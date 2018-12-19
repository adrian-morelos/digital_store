<?php

namespace Drupal\digital_store_checkout\Form;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\digital_store_order\Entity\Order;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\digital_store_cart\CartManagerInterface;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\digital_store_checkout\CheckoutFlowStepsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Checkout Payment Form.
 */
class CheckoutPaymentForm extends FormBase {

  /**
   * The Current Cart.
   *
   * @var \Drupal\digital_store_order\Entity\OrderInterface
   */
  protected $cart = NULL;

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface;
   */
  protected $cartProvider;

  /**
   * The cart manager.
   *
   * @var \Drupal\digital_store_cart\CartManagerInterface;
   */
  protected $cartManager;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\digital_store_cart\CartProviderInterface; $cart_provider
   *   The cart provider.
   * @param \Drupal\digital_store_cart\CartManagerInterface; $cart_manager
   *   The cart manager.
   */
  public function __construct(CartProviderInterface $cart_provider, CartManagerInterface $cart_manager) {
    $this->cartProvider = $cart_provider;
    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('digital_store_cart.cart_provider'),
      $container->get('digital_store_cart.cart_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checkout-payment-form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $order_id = NULL) {
    // @todo add redirect logic and access checker.
    $order = new Order($order_id);
    $form_state->set('order', $order);
    if (!$order) {
      // Nothing to do stop here.
      return [];
    }
    // We want to deal with hierarchical form values.
    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div class="c"><div class="r"><div class="col"><div class="b"><div class="c-f-s">';
    $form['#suffix'] = '</div></div></div></div></div>';
    $form['#attributes'] = [
      'itemscope' => '',
      'itemtype' => 'http://schema.org/Order',
    ];

    // Stripe Config.
    $config = \Drupal::config('digital_store.settings.stripe');
    $publishable_key = $config->get('publishable_key');
    $form['#tree'] = TRUE;
    $form['#attached']['drupalSettings']['digitalStoreStripe'] = [
      'publishableKey' => $publishable_key,
    ];
    // Attach the payment library.
    $form['#attached']['library'][] = 'digital_store_checkout/payment_form';
    // p-i-s: Payment Information Step.
    $form['#attributes']['class'][] = 'p-i-s';
    $form['#attributes']['class'][] = 'stripe-form';
    $form['#attributes']['class'][] = 'r';
    $form['#title'] = t('Payment Information');
    $form['header'] = [
      '#type' => 'item',
      '#markup' => '<h3 class="t">' . t('Payment Information') . '</h3>',
    ];
    // Print cart Summary.
    $this->cartSummary($order, $form);

    // Payment Information Container.
    $form['payment_information'] = [
      '#type' => 'container',
      '#prefix' => '<div class="col-md-12 col-sm-12">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['r', 'payment-information'],
      ],
    ];
    // Print Billing info.
    $this->billingInformationSummary($order, $form);
    $payment_information = &$form['payment_information'];

    // Credit Card Container.
    $payment_information['credit_card'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-md-6', 'col-sm-12', 'credit-card'],
        'itemscope' => '',
        'itemtype' => 'http://schema.org/CreditCard',
      ],
    ];

    $credit_card = &$payment_information['credit_card'];

    $credit_card['header'] = [
      '#type' => 'item',
      '#markup' => '<h5 class="s">' . t('Payment Information') . '</h5>',
    ];
    // Payment Information - Card details Container.
    $credit_card['card_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['card-details'],
      ],
    ];
    // Populated by the JS library.
    $credit_card['card_details']['stripe_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'stripe_token',
      ],
    ];
    // To display validation errors.
    $credit_card['card_details']['payment_errors'] = [
      '#type' => 'markup',
      '#markup' => '<div id="payment-errors" class="payment-errors"></div>',
    ];
    $credit_card['card_details']['card_number'] = [
      '#type' => 'item',
      '#title' => t('Card number'),
      '#required' => TRUE,
      '#validated' => TRUE,
      '#markup' => '<div id="card-number-element" class="form-text"></div>',
      '#wrapper_attributes' => [
        'class' => ['form-group'],
      ],
    ];

    $credit_card['card_details']['expiration'] = [
      '#type' => 'item',
      '#title' => t('Expiration date'),
      '#required' => TRUE,
      '#validated' => TRUE,
      '#prefix' => '<div class="r"><div class="col-sm-6">',
      '#suffix' => '</div>',
      '#markup' => '<div id="expiration-element" class="form-text"></div>',
    ];

    $credit_card['card_details']['security_code'] = [
      '#type' => 'item',
      '#title' => t('CVC'),
      '#required' => TRUE,
      '#validated' => TRUE,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div></div>',
      '#markup' => '<div id="security-code-element" class="form-text"></div>',
    ];

    // Actions.
    $credit_card['actions'] = [
      '#type' => 'actions',
      '#attributes' => [
        'class' => ['r', 'j-c-c'],
      ],
    ];
    // Return to Cart link.
    $credit_card['actions']['back_to_cart'] = [
      '#type' => 'link',
      '#title' => t('&#8592; Back to shopping cart'),
      '#attributes' => ['class' => ['btn-link', 'back-to-cart', 'pull-left']],
      '#prefix' => '<div class="col">',
      '#suffix' => '</div>',
      '#url' => Url::fromRoute('digital_store_cart.cart_page'),
    ];
    // Add Complete Order info.
    $credit_card['actions']['complete_order'] = [
      '#type' => 'submit',
      '#value' => t('Complete Order'),
      '#prefix' => '<div class="col">',
      '#suffix' => '</div>',
      '#action' => 'complete_order',
      '#attributes' => ['class' => ['btn', 'b-cta', 'pull-right']],
    ];
    // Return Form.
    return $form;
  }

  /**
   * Add Cart Summary to the given element.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The order entity.
   * @param array $form
   *   The initial price_number form element.
   */
  public function cartSummary(OrderInterface $cart = NULL, &$form = NULL) {
    if (!$cart) {
      return;
    }
    // Cart Summary Container.
    $form['cart_summary'] = [
      '#type' => 'container',
      '#prefix' => '<div class="col-md-12 col-sm-12">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['c-s'],
      ],
    ];
    $form['cart_summary']['header'] = [
      '#type' => 'item',
      '#markup' => '<h5 class="s">Your Order</h5>',
    ];
    $form['cart_summary']['content'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['r'],
      ],
    ];
    // Order Items.
    $form['cart_summary']['content']['order_items'] = [
      '#type' => 'table',
      '#prefix' => '<div class="col-md-8 col-sm-8">',
      '#suffix' => '</div>',
      '#empty' => t('Your cart is currently empty.'),
      '#header' => [
        ['data' => t('Product'), 'class' => 't-c'],
        ['data' => t('Qty'), 'class' => 't-c'],
        ['data' => t('Price'), 'class' => 't-r'],
        ['data' => t('Total'), 'class' => 't-r'],
      ],
      '#attributes' => [
        'class' => ['c-i'],
      ],
    ];
    $items = $cart->getItems();
    foreach ($items as $delta => $order_item) {
      $order_item_id = $order_item->id();
      $form['cart_summary']['content']['order_items'][$order_item_id] = [
        'product' => $order_item->getOrderItemTitle(),
        'quantity' => [
          '#markup' => $order_item->getQuantity(),
          '#title' => t('Qty'),
          '#title_display' => 'invisible',
          '#wrapper_attributes' => [
            'class' => ['t-c'],
          ],
        ],
        'unit_price' => [
          '#markup' => "<div class='item'>{$order_item->getUnitPrice()}</div>",
          '#title' => t('Unit Price'),
          '#title_display' => 'invisible',
          '#wrapper_attributes' => [
            'class' => ['t-r'],
          ],
        ],
        'total' => [
          '#markup' => $order_item->getTotalPrice(),
          '#title' => t('Total'),
          '#title_display' => 'invisible',
          '#wrapper_attributes' => [
            'class' => ['t-r'],
          ],
        ],
      ];
    }
    // Add Table for totals.
    $form['cart_summary']['content']['cart_total'] = [
      '#type' => 'container',
      '#prefix' => '<div class="col-md-4 col-sm-4">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['cart-total'],
      ],
    ];
    $form['cart_summary']['content']['cart_total']['summary'] = [
      '#type' => 'table',
      '#header' => [
      ],
      '#attributes' => [
        'class' => ['c-t'],
      ],
      'top' => [
        ['#markup' => '<div class="sub-total-label">' . t('SUBTOTAL') . '</div>'],
        ['#markup' => '<div class="sub-total-price">' . (string) $cart->getTotalPrice() . '</div>'],
      ],
      'bottom' => [
        ['#markup' => '<div class="total-label">' . t('TOTAL') . '</div>'],
        ['#markup' => '<div class="total-price">' . (string) $cart->getTotalPrice() . '</div>'],
      ],
    ];
  }

  /**
   * Add billing information Summary to the given element.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The order entity.
   * @param array $form
   *   The initial price_number form element.
   */
  public function billingInformationSummary(OrderInterface $cart = NULL, &$form = NULL) {
    if (!$cart) {
      return;
    }
    // Billing Information.
    $address = $cart->getBillingDetail();
    $names = [];
    if (isset($address['given_name'])) {
      $names[] = $address['given_name'];
    }
    if (isset($address['additional_name'])) {
      $names[] = $address['additional_name'];
    }
    if (isset($address['family_name'])) {
      $names[] = $address['family_name'];
    }
    $options = ['absolute' => TRUE, 'attributes' => ['class' => 'l-a']];
    $form['payment_information']['billing_address'] = [
      '#theme' => 'billing_information',
      '#address' => $address,
      '#name' => implode(' ', $names),
      '#organization' => $address['organization'] ?? NULL,
      '#email' => $cart->getEmail(),
      '#country_code' => $address['country_code'] ?? NULL,
      '#address_line1' => $address['address_line1'] ?? NULL,
      '#address_line2' => $address['address_line2'] ?? NULL,
      '#locality' => $address['locality'] ?? NULL,
      '#administrative_area' => $address['administrative_area'] ?? NULL,
      '#postal_code' => $address['postal_code'] ?? NULL,
      '#action_link' => Link::createFromRoute(t('Change'), 'digital_store_cart.cart_page', [], $options),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$this->isTriggeringAction('complete_order', $form_state)) {
      // Nothing to do, Stop here.
      return;
    }
    $parent = 'payment_information';
    $card_details = $form_state->getValue([
      $parent,
      'credit_card',
      'card_details',
    ]);
    // 1. Get the stripe token associated with the credit card.
    $stripe_token = $card_details['stripe_token'] ?? NULL;
    if (empty($stripe_token)) {
      $credit_card_element = &$form[$parent]['credit_card']['card_details']['card_number'];
      $form_state->setError($credit_card_element['card_number'], t('You have entered an invalid credit card number.'));
      return;
    }
    // 2. Try to charge the credit card.
    $order = $form_state->get('order');
    $payment_process = \Drupal::service('digital_store_payment.payment_process');
    $charged = $payment_process->placeOrder($stripe_token, $order);
    if (!$charged) {
      $form_state->setRebuild(TRUE);
      return;
    }
    // 4. Complete the order.
    // Move the order to the next step.
    $order->set('checkout_flow_step', CheckoutFlowStepsInterface::COMPLETED);
    $order->setState('completed');
    $order->setIsOnCart(FALSE);
    // Save the changes.
    try {
      $order->save();
    }
    catch (EntityStorageException $e) {
      // @todo Show an friendly message.
    }
    // 5. Finalize Cart.
    \Drupal::service('digital_store_cart.cart_provider')->finalizeCart($order, $save_cart = FALSE);
    // 6. Go to next Step.
    $step_id = CheckoutFlowStepsInterface::COMPLETED;
    $route_name = "digital_store_checkout.{$step_id}";
    $form_state->setRedirect($route_name, [
      'order' => $order->id(),
    ]);
  }

  /**
   * Validates the trigger is an associated to a given action.
   *
   * @param string $action
   *   The action name.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   *
   * @return bool
   *   TRUE if the trigger is an associated to a given action, otherwise FALSE.
   */
  public function isTriggeringAction($action = '', FormStateInterface $form_state = NULL) {
    if (empty($action)) {
      return FALSE;
    }
    $triggering_element = $form_state->getTriggeringElement();
    if (empty($triggering_element)) {
      return FALSE;
    }
    $operation = $triggering_element['#action'] ?? NULL;
    if (empty($operation)) {
      return FALSE;
    }
    return ($operation == $action);
  }

}
