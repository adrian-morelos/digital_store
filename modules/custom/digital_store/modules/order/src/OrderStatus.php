<?php

namespace Drupal\digital_store_order;

/**
 * Provides logic for handle Order Status.
 */
final class OrderStatus {

  /**
   * The instantiated Checkout Flow status.
   *
   * @var array
   */
  public static $status = [];

  /**
   * Gets all available Order workflow status.
   *
   * @return array
   *   The Checkout Flow status.
   */
  public static function getOrderStatus() {
    $definitions = [
      OrderStatusInterface::DRAFT => t('Shopping Cart'),
      OrderStatusInterface::PENDING_PAYMENT => t('Pending Payment'),
      OrderStatusInterface::FAILED => t('Failed'),
      OrderStatusInterface::PROCESSING => t('Processing'),
      OrderStatusInterface::COMPLETED => t('Completed'),
      OrderStatusInterface::ON_HOLD => t('On-Hold'),
      OrderStatusInterface::CANCELLED => t('Cancelled'),
      OrderStatusInterface::REFUNDED => t('Refunded'),
    ];
    foreach ($definitions as $id => $definition) {
      self::$status[$id] = $definition;
    }
    return self::$status;
  }

  /**
   * Gets the labels of all available Order status.
   *
   * @return array
   *   The labels, keyed by ID.
   */
  public static function getOrderStatusLabels() {
    return self::getOrderStatus();
  }

}
