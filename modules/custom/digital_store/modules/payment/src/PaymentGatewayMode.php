<?php

namespace Drupal\digital_store_payment;

/**
 * Provides logic for handle Payment Gateway Modes.
 */
final class PaymentGatewayMode {

  /**
   * Payment Gateway Mode - Production.
   */
  const PRODUCTION = 'production';

  /**
   * Payment Gateway Mode - Development.
   */
  const DEVELOPMENT = 'Development';

  /**
   * The instantiated Payment Gateway modes.
   *
   * @var array
   */
  public static $modes = [];

  /**
   * Gets all available Payment Gateway modes.
   *
   * @return array
   *   The Payment Gateway modes.
   */
  public static function getModes() {
    $definitions = [
      self::DEVELOPMENT => t('Development'),
      self::PRODUCTION => t('Production'),
    ];
    foreach ($definitions as $id => $definition) {
      self::$modes[$id] = $definition;
    }
    return self::$modes;
  }

  /**
   * Gets the labels of all available Payment Gateway modes.
   *
   * @return array
   *   The labels, keyed by ID.
   */
  public static function getModeLabels() {
    return self::getModes();
  }

}
