<?php

namespace Drupal\digital_store_checkout\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Entity\EntityStorageException;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Drupal\digital_store_checkout\CheckoutFlowStepsInterface;

/**
 * Provides a billing details step.
 *
 * Usage example:
 * @code
 * $element['billing_details_step'] = [
 *   '#type' => 'billing_details_step',
 *   '#required' => TRUE,
 * ];
 * @endcode
 *
 * @FormElement("billing_details_step")
 */
class BillingDetailsStep extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#order' => NULL,
      '#default_value' => NULL,
      '#element_validate' => [
        [$class, 'validateBillingInformationStep'],
      ],
      '#process' => [
        [$class, 'processElement'],
      ],
      '#input' => TRUE,
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Builds the price_number form element.
   *
   * @param array $element
   *   The initial price_number form element.
   *
   * @return array
   *   The built price_number form element.
   */
  public static function processElement(array $element = []) {
    $cart = self::getCart();
    $customer = self::getCurrentUser();
    if (!$cart || !$cart->hasItems()) {
      // Nothing to do, stop here.
      return $element;
    }
    $customer_email = $customer->getEmail();
    $is_anonymous = $customer->isAnonymous();
    // Billing details Container.
    $element['#attributes']['class'][] = 'billing-details';
    $element['#prefix'] = '<div class="col-md-12 col-sm-12">';
    $element['#suffix'] = '</div>';
    $element['header'] = [
      '#type' => 'item',
      '#markup' => '<h5>Complete your billing details below</h5>',
    ];
    $address = $cart->getBillingDetail();
    $element['first_name'] = [
      '#type' => 'textfield',
      '#title' => t('First Name'),
      '#default_value' => ($address['given_name'] ?? NULL),
      '#maxlength' => 128,
      '#required' => FALSE,
      '#size' => 20,
      '#prefix' => '<div class="form-row"><div class="form-group col-md-6">',
      '#suffix' => '</div>',
      '#attributes' => [
        'placeholder' => t('Enter Your First Name'),
        'class' => ['form-control'],
      ],
    ];
    $element['last_name'] = [
      '#type' => 'textfield',
      '#title' => t('Last Name'),
      '#default_value' => ($address['family_name'] ?? NULL),
      '#maxlength' => 128,
      '#required' => FALSE,
      '#size' => 20,
      '#prefix' => '<div class="form-group col-md-6">',
      '#suffix' => '</div></div>',
      '#attributes' => [
        'placeholder' => t('Enter Your Last Name'),
        'class' => ['form-control'],
      ],
    ];
    $element_prefix = '<div class="form-group">';
    $element_suffix = '</div>';
    if (!$is_anonymous) {
      $element_prefix = '<div class="form-row"><div class="form-group col-md-6">';
      $element_suffix = '</div>';
    }
    $element['company_name'] = [
      '#type' => 'textfield',
      '#title' => t('Company name'),
      '#default_value' => ($address[AddressField::ORGANIZATION] ?? NULL),
      '#maxlength' => 128,
      '#required' => FALSE,
      '#size' => 20,
      '#prefix' => $element_prefix,
      '#suffix' => $element_suffix,
      '#attributes' => [
        'placeholder' => t('Company name'),
        'class' => ['form-control'],
      ],
    ];
    if (!$is_anonymous) {
      $element['customer_email'] = [
        '#type' => 'textfield',
        '#title' => t('Email address'),
        '#default_value' => $customer_email,
        '#required' => FALSE,
        '#prefix' => '<div class="form-group col-md-6">',
        '#suffix' => '</div></div>',
        '#attributes' => [
          'placeholder' => $customer_email,
          'class' => ['form-control'],
          'readonly' => 'readonly',
        ],
      ];
    }
    $address = !empty($address) ? $address : ['country_code' => 'US'];
    $element['address'] = [
      '#type' => 'address',
      '#default_value' => $address,
      '#required' => FALSE,
      '#used_fields' => [
        AddressField::ADDRESS_LINE1,
        AddressField::ADDRESS_LINE2,
        AddressField::ADMINISTRATIVE_AREA,
        AddressField::LOCALITY,
        AddressField::POSTAL_CODE,
      ],
      '#available_countries' => ['US', 'CA'],
    ];
    $element['check_out'] = array(
      '#type' => 'submit',
      '#value' => t('Proceed to Checkout &#8594;'),
      '#attributes' => ['class' => ['btn', 'btn-check-out']],
      '#action' => 'check_out',
      '#name' => 'check_out',
    );
    return $element;
  }

  /**
   * Validates the billing details form.
   *
   * @param array $element
   *   The billing details form element.
   * @param \Drupal\Core\Form\FormStateInterface $element_state
   *   The current state of the complete form.
   */
  public static function validateBillingInformationStep(array $element = [], FormStateInterface &$element_state = NULL) {
    $triggering_element = $element_state->getTriggeringElement();
    if (empty($triggering_element)) {
      return;
    }
    $operation = $triggering_element['#action'] ?? NULL;
    if (empty($operation)) {
      return;
    }
    if ($operation != 'check_out') {
      return;
    }
    $billing_details = $element_state->getValue(['billing_details_step']);
    if (empty($billing_details)) {
      return;
    }
    // 1. Validate.
    $valid = self::validateBillingInformation($billing_details, $element, $element_state);
    if (!$valid) {
      return;
    }
    // 2. Save billing info.
    $saved = self::saveBillingInformation($billing_details);
    if (!$saved) {
      return;
    }
    // 3. Go to next Step.
    self::goToNextStep($element_state);
  }

  /**
   * Validates the billing information.
   *
   * @param array $billing_details
   *   The billing details.
   * @param array $element
   *   The billing details form element.
   * @param \Drupal\Core\Form\FormStateInterface $element_state
   *   The current state of the complete form.
   *
   * @return bool
   *   True if the billing info is valid, otherwise FALSE.
   */
  public static function validateBillingInformation(array $billing_details = [], array $element = [], FormStateInterface &$element_state = NULL) {
    // @todo.
    return TRUE;
  }

  /**
   * Saves the billing information.
   *
   * @param array $billing_details
   *   The billing details.
   *
   * @return bool
   *   True if the billing info was successfully updated, otherwise FALSE.
   */
  public static function saveBillingInformation(array $billing_details = []) {
    if (empty($billing_details)) {
      return FALSE;
    }
    $cart = self::getCart();
    if (!$cart) {
      return FALSE;
    }
    // Update Billing Address.
    $address = $billing_details['address'] ?? NULL;
    $first_name = $billing_details['first_name'] ?? NULL;
    $address['given_name'] = $first_name;
    $last_name = $billing_details['last_name'] ?? NULL;
    $address['family_name'] = $last_name;
    $company_name = $billing_details['company_name'] ?? NULL;
    $address[AddressField::ORGANIZATION] = $company_name;
    $cart->setBillingDetail($address);
    // Move the order to the next step.
    $cart->setAttribute('checkout_flow_step', CheckoutFlowStepsInterface::PAYMENT_INFORMATION);
    // Save the changes.
    try {
      $saved = $cart->save();
    }
    catch (EntityStorageException $e) {
      $saved = FALSE;
    }
    return ($saved == SAVED_UPDATED);
  }

  /**
   * Go to next step.
   *
   * @param \Drupal\Core\Form\FormStateInterface $element_state
   *   The current state of the complete form passed by reference.
   */
  public static function goToNextStep(FormStateInterface &$element_state = NULL) {
    $cart = self::getCart();
    if (!$cart) {
      return;
    }
    $order_id = $cart->id();
    $step_id = CheckoutFlowStepsInterface::PAYMENT_INFORMATION;
    $route_name = "digital_store_checkout.{$step_id}";
    $element_state->setRedirect($route_name, [
      'order' => $order_id,
    ]);
  }

  /**
   * Get the current user.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface
   *   The current user.
   */
  public static function getCurrentUser() {
    return \Drupal::currentUser();
  }

  /**
   * Get Cart.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The current user cart.
   */
  public static function getCart() {
    return \Drupal::service('digital_store_cart.cart_provider')->getCart();
  }

}
