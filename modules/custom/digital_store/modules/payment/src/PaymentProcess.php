<?php

namespace Drupal\digital_store_payment;

use Drupal\digital_store_payment\Exception\HardDeclineException;
use Drupal\digital_store_payment\Entity\PaymentMethodInterface;
use Drupal\digital_store_payment\Entity\PaymentInterface;
use Drupal\digital_store_payment\Entity\PaymentMethod;
use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\digital_store_payment\Entity\Payment;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\digital_store\Calculator;
use Drupal\digital_store\Price;
use Drupal\user\UserInterface;

/**
 * Provides the Payment Process service definition.
 */
class PaymentProcess {

  /**
   * The Stripe payment gateway ID.
   *
   * @var string
   */
  const STRIPE_PAYMENT_GATEWAY_ID = '10';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The configuration.
   *
   * @var \Drupal\digital_store_payment\StripeClientInterface $stripe
   */
  protected $stripeClient = NULL;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new Payment Process object.
   *
   * @param \Drupal\digital_store_payment\StripeClientInterface $stripe
   *   The stripe instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(StripeClientInterface $stripe, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->stripeClient = $stripe;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * Place Order.
   *
   * @param string $stripe_token
   *   The stripe token.
   * @param \Drupal\digital_store_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return bool
   *   TRUE if the order was placed successfully, otherwise false.
   */
  public function placeOrder($stripe_token = NULL, OrderInterface $order = NULL) {
    if (!$order) {
      return FALSE;
    }
    if (empty($stripe_token)) {
      return FALSE;
    }
    // Try Place order.
    try {
      // Create the payment method based on the Stripe Token.
      $fields = [
        'title' => 'Payment Method',
        'payment_method_billing_profile' => $order->getBillingDetail(),
        'payment_method_owner' => $order->getCustomerId(),
        'payment_method_gateway' => self::STRIPE_PAYMENT_GATEWAY_ID,
      ];
      $payment_method_id = $this->createPaymentMethod($fields, ['stripe_token' => $stripe_token]);
      if (!$payment_method_id) {
        throw new \Exception();
      }
      // Create the Payment.
      $fields = [
        'title' => 'Payment',
        'payment_state' => 'new',
        'payment_amount' => $order->getBalance(),
        'payment_gateway' => self::STRIPE_PAYMENT_GATEWAY_ID,
        'payment_order_id' => $order->id(),
        'payment_method' => $payment_method_id,
      ];
      $payment = Payment::create($fields);
      $success = $this->createPayment($payment, $this->stripeClient->isCapture());
      if (!$success) {
        throw new \Exception();
      }
    } catch (\Exception $e) {
      $success = FALSE;
    }
    if (!$success) {
      $message = t('We encountered an error processing your payment. Please verify your details and try again.');
      $this->messenger->addError($message);
    }
    return $success;
  }

  /**
   * Creates a payment.
   *
   * @param \Drupal\digital_store_payment\Entity\PaymentInterface $payment
   *   The payment.
   * @param bool $capture
   *   Whether the created payment should be captured (VS authorized only).
   *   Allowed to be FALSE only if the plugin supports authorizations.
   *
   * @throws \InvalidArgumentException
   *   If $capture is FALSE but the plugin does not support authorizations.
   * @throws \Drupal\digital_store_payment\Exception\PaymentGatewayException
   *   Thrown when the transaction fails for any reason.
   *
   * @return bool
   *   TRUE if the order was placed successfully, otherwise false.
   */
  public function createPayment(PaymentInterface $payment = NULL, $capture = TRUE) {
    if (!$payment) {
      return FALSE;
    }
    $payment_state = $payment->getState();
    if ($payment_state != 'new') {
      return FALSE;
    }
    $payment_method = $payment->getPaymentMethod();
    if (!$payment_method) {
      return FALSE;
    }
    $amount = $payment->getAmount();
    $transaction_data = [
      'currency' => $amount->getCurrencyCode(),
      'amount' => $this->toMinorUnits($amount),
      'source' => $payment_method->getRemoteId(),
      'capture' => boolval($capture) ? 'true' : 'false',
    ];
    $owner = $payment_method->getOwner();
    if ($owner && $owner->isAuthenticated()) {
      $transaction_data['customer'] = $this->getRemoteCustomerId($owner);
    }
    try {
      $result = $this->stripeClient->createCharge($transaction_data);
    } catch (\Exception $e) {
      $result = NULL;
    }
    if ($result && ($result->status == 'succeeded')) {
      $next_state = $capture ? 'completed' : 'authorization';
      $payment->setState($next_state);
      $payment->setRemoteId($result->id);
      $payment->save();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function capturePayment(PaymentInterface $payment, Price $amount = NULL) {
    if (!$payment) {
      return NULL;
    }
    $payment_state = $payment->getState();
    if ($payment_state != 'authorization') {
      return NULL;
    }
    // If not specified, capture the entire amount.
    $amount = $amount ?: $payment->getAmount();
    try {
      $remote_id = $payment->getRemoteId();
      $charge = $this->stripeClient->retrieveCharge($remote_id);
      if ($charge) {
        $charge->amount = $this->toMinorUnits($amount);
        $transaction_data = [
          'amount' => $charge->amount,
        ];
        $this->stripeClient->captureCharge($remote_id, $transaction_data);
      }
    } catch (\Exception $e) {
      // @todo handle user-facing Exception messages.
    }
    $payment->setState('completed');
    $payment->setAmount($amount);
    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function voidPayment(PaymentInterface $payment) {
    if (!$payment) {
      return NULL;
    }
    $payment_state = $payment->getState();
    if ($payment_state != 'authorization') {
      return NULL;
    }
    // Void Stripe payment - release un-captured payment.
    try {
      $remote_id = $payment->getRemoteId();
      $amount = $payment->getAmount();
      $data = [
        'charge' => $remote_id,
        'amount' => $this->toMinorUnits($amount),
      ];
      $release_refund = $this->stripeClient->createRefund($data);
      // @todo handle user-facing Exception messages.
    } catch (\Exception $e) {
      // @todo handle user-facing Exception messages.
    }
    $payment->setState('authorization_voided');
    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    $payment_state = $payment->getState();
    if (!in_array($payment_state, ['completed', 'partially_refunded'])) {
      return NULL;
    }
    // If not specified, refund the entire amount.
    $amount = $amount ?: $payment->getAmount();
    $balance = $payment->getBalance();
    if ($amount->greaterThan($balance)) {
      // Can't refund more than the balance.
      return NULL;
    }
    try {
      $remote_id = $payment->getRemoteId();
      $data = [
        'charge' => $remote_id,
        'amount' => $this->toMinorUnits($amount),
      ];
      $refund = $this->stripeClient->createRefund($data);
      // @todo handle user-facing Exception messages.
    } catch (\Exception $e) {
      // @todo handle user-facing Exception messages.
    }
    $old_refunded_amount = $payment->getRefundedAmount();
    $new_refunded_amount = $old_refunded_amount->add($amount);
    if ($new_refunded_amount->lessThan($payment->getAmount())) {
      $payment->setState('partially_refunded');
    }
    else {
      $payment->setState('refunded');
    }
    $payment->setRefundedAmount($new_refunded_amount);
    $payment->save();
  }

  /**
   * Creates a payment.
   *
   * @param \Drupal\digital_store_payment\Entity\PaymentInterface $payment
   *   The payment.
   * @param bool $capture
   *   Whether the created payment should be captured (VS authorized only).
   *   Allowed to be FALSE only if the plugin supports authorizations.
   *
   * @throws \InvalidArgumentException
   *   If $capture is FALSE but the plugin does not support authorizations.
   * @throws \Drupal\digital_store_payment\Exception\PaymentGatewayException
   *   Thrown when the transaction fails for any reason.
   *
   * @return string|bool
   *   The Payment Method ID if was created successfully, otherwise false.
   */
  public function createPaymentMethod(array $fields = [], array $payment_details) {
    $required_keys = [
      'stripe_token'
    ];
    foreach ($required_keys as $required_key) {
      if (empty($payment_details[$required_key])) {
        throw new \InvalidArgumentException(sprintf('$payment_details must contain the %s key.', $required_key));
      }
    }
    $payment_method = PaymentMethod::create($fields);
    $card = $this->doCreatePaymentMethod($payment_method, $payment_details);
    if (empty($card)) {
      return FALSE;
    }
    $payment_method->setCardType($this->mapCreditCardType($card->brand));
    $payment_method->setCardNumber($card->last4);
    $payment_method->setCardExpirationMonth($card->exp_month);
    $payment_method->setCardExpirationYear($card->exp_year);
    $remote_id = $card->id;
    $expires = CreditCard::calculateExpirationTimestamp($card->exp_month, $card->exp_year);
    $payment_method->setRemoteId($remote_id);
    $payment_method->setExpiresTime($expires);
    $payment_method->save();
    return $payment_method->id();
  }

  /**
   * Maps the Stripe credit card type to a Commerce credit card type.
   *
   * @param string $card_type
   *   The Stripe credit card type.
   *
   * @return string
   *   The Commerce credit card type.
   */
  protected function mapCreditCardType($card_type) {
    // https://support.stripe.com/questions/which-cards-and-payment-types-can-i-accept-with-stripe.
    $map = [
      'American Express' => 'amex',
      'Diners Club' => 'dinersclub',
      'Discover' => 'discover',
      'JCB' => 'jcb',
      'MasterCard' => 'mastercard',
      'Visa' => 'visa',
    ];
    if (!isset($map[$card_type])) {
      throw new HardDeclineException(sprintf('Unsupported credit card type "%s".', $card_type));
    }
    return $map[$card_type];
  }

  /**
   * Creates the payment method on the gateway.
   *
   * @param \Drupal\digital_store_payment\Entity\PaymentMethodInterface $payment_method
   *   The payment method.
   * @param array $payment_details
   *   The gateway-specific payment details.
   *
   * @return \stdClass|null
   *   The payment method information returned by the gateway. Notable keys:
   *   - id: The remote ID.
   *   Credit card specific keys:
   *   - brand: The card type.
   *   - last4: The last 4 digits of the credit card number.
   *   - exp_month: The expiration month.
   *   - exp_year: The expiration year.
   */
  protected function doCreatePaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    $owner = $payment_method->getOwner();
    $stripe_token = $payment_details['stripe_token'];
    $customer_id = NULL;
    $customer_data = [];
    $card = NULL;
    if ($owner && $owner->isAuthenticated()) {
      $customer_id = $this->getRemoteCustomerId($owner);
      $customer_data['email'] = $owner->getEmail();
    }
    if ($customer_id) {
      // If the customer id already exists, use the Stripe form token to create the new card.
      try {
        // Create a payment method for an existing customer.
        $card = $this->stripeClient->createNewCard($customer_id, $stripe_token);
      } catch (\Exception $e) {
        // @todo handle user-facing Exception messages.
      }
    }
    elseif ($owner && $owner->isAuthenticated()) {
      // Create both the customer and the payment method.
      try {
        $customer = $this->stripeClient->createCustomer([
          'email' => $owner->getEmail(),
          'description' => t('Customer for :mail', [':mail' => $owner->getEmail()]),
          'source' => $stripe_token,
        ]);
        $this->setRemoteCustomerId($owner, $customer->id);
        $cards = $this->stripeClient->getCustomersCards($customer);
        $card = !empty($cards) ? current($cards) : $card;
      } catch (\Exception $e) {
        // @todo handle user-facing Exception messages.
      }
    }
    else {
      $card_token = $this->stripeClient->retrieveToken($stripe_token);
      if ($card_token) {
        // We need to use token for Anonymous customers.
        $card_token->card->id = $stripe_token;
        $card = $card_token->card;
      }
    }
    return $card;
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // Delete the remote record.
    try {
      $owner = $payment_method->getOwner();
      if ($owner) {
        $customer_id = $this->getRemoteCustomerId($owner);
        $remote_id = $payment_method->getRemoteId();
        $detached_source = $this->stripeClient->detachSource($customer_id, $remote_id);
      }
    } catch (\Exception $e) {
      // @todo handle user-facing Exception messages.
    }
    // Delete the local entity.
    $payment_method->delete();
  }

  /**
   * Gets the remote customer ID for the given user.
   *
   * The remote customer ID is specific to a payment gateway instance
   * in the configured mode. This allows the gateway to skip test customers
   * after the gateway has been switched to live mode.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account.
   *
   * @return string
   *   The remote customer ID, or NULL if none found.
   */
  protected function getRemoteCustomerId(UserInterface $account) {
    if (!$account->isAuthenticated()) {
      return NULL;
    }
    $remote_id = $account->get('remote_id')->getString();
    if (empty($remote_id)) {
      return NULL;
    }
    return $remote_id;
  }

  /**
   * Sets the remote customer ID for the given user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account.
   * @param string $remote_id
   *   The remote customer ID.
   */
  protected function setRemoteCustomerId(UserInterface $account, $remote_id) {
    if ($account->isAuthenticated()) {
      $account->set('remote_id', $remote_id);
      $account->save();
    }
  }

  /**
   * Converts the given amount to its minor units.
   *
   * For example, 9.99 USD becomes 999.
   *
   * @param \Drupal\digital_store\Price $amount
   *   The amount.
   *
   * @return int
   *   The amount in minor units, as an integer.
   */
  protected function toMinorUnits(Price $amount) {
    $currency_storage = $this->entityTypeManager->getStorage('currency');
    /** @var \Drupal\digital_store\Entity\CurrencyInterface $currency */
    $currency = $currency_storage->load($amount->getCurrencyCode());
    $fraction_digits = $currency->getFractionDigits();
    $number = $amount->getNumber();
    if ($fraction_digits > 0) {
      $number = Calculator::multiply($number, pow(10, $fraction_digits));
    }
    return round($number, 0);
  }

}
