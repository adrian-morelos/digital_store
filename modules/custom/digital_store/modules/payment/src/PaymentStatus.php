<?php

namespace Drupal\digital_store_payment;

/**
 * Provides logic for handle Payment Status.
 */
final class PaymentStatus {

  /**
   * The instantiated Payment States.
   *
   * @var array
   */
  public static $states = [];

  /**
   * Gets all available Payment States.
   *
   * @return array
   *   The Payment States.
   */
  public static function getStates() {
    $definitions = [
      PaymentStatusInterface::NEW => t('New'),
      PaymentStatusInterface::AUTHORIZATION => t('Authorization'),
      PaymentStatusInterface::PARTIALLY_REFUNDED => t('Partially Refunded'),
      PaymentStatusInterface::REFUNDED => t('Refunded'),
      PaymentStatusInterface::COMPLETED => t('Completed'),
      PaymentStatusInterface::AUTHORIZATION_VOIDED => t('Authorization Voided'),
    ];
    foreach ($definitions as $id => $definition) {
      self::$states[$id] = $definition;
    }
    return self::$states;
  }

  /**
   * Gets the labels of all available Payment States.
   *
   * @return array
   *   The labels, keyed by ID.
   */
  public static function getStateLabels() {
    return self::getStates();
  }

}
