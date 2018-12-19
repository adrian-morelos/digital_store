<?php

namespace Drupal\digital_store_checkout;

/**
 * Provides logic for handle Checkout Flow Steps.
 */
final class CheckoutFlow {

  /**
   * The instantiated Checkout Flow steps.
   *
   * @var array
   */
  public static $steps = [];

  /**
   * Gets all available Checkout Flow steps.
   *
   * @return array
   *   The Checkout Flow steps.
   */
  public static function getSteps() {
    $definitions = [
      CheckoutFlowStepsInterface::SHOPPING_CART => t('Shopping Cart'),
      CheckoutFlowStepsInterface::PAYMENT_INFORMATION => t('Payment Information'),
      CheckoutFlowStepsInterface::COMPLETED => t('Completed'),
    ];
    foreach ($definitions as $id => $definition) {
      self::$steps[$id] = $definition;
    }
    return self::$steps;
  }

  /**
   * Gets the labels of all available CheckoutFlow steps.
   *
   * @return array
   *   The labels, keyed by ID.
   */
  public static function getStepLabels() {
    return self::getSteps();
  }

}
