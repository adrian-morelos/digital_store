<?php

namespace Drupal\digital_store\Entity;

/**
 * Defines the interface for purchasable entities.
 */
interface PurchasableEntityInterface {

  /**
   * Gets the purchasable entity's order item type ID.
   *
   * Used for finding/creating the appropriate order item when purchasing a
   * product (adding it to an order).
   *
   * @return string
   *   The order item type ID.
   */
  public function getOrderItemTypeId();

  /**
   * Gets the purchasable entity's order item title.
   *
   * Saved in the $order_item->title field to protect the order items of
   * completed orders against changes in the referenced purchased entity.
   *
   * @return string
   *   The order item title.
   */
  public function getOrderItemTitle();

  /**
   * Gets the product entity's link.
   *
   * @return \Drupal\Core\Link
   *   A Link to the entity.
   */
  public function getProductLink();

  /**
   * Gets the purchasable entity's price.
   *
   * @return string|null
   *   The price, or NULL.
   */
  public function getPrice();

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id();

}
