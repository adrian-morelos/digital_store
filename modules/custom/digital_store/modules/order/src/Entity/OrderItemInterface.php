<?php

namespace Drupal\digital_store_order\Entity;

use Drupal\digital_store\Price;
use Drupal\digital_store_order\EntityAdjustableInterface;

/**
 * Defines the interface for order items.
 */
interface OrderItemInterface extends EntityAdjustableInterface {

  /**
   * Gets the parent order.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The order, or NULL.
   */
  public function getOrder();

  /**
   * Gets the order item title.
   *
   * @return array
   *   The render-able array representing the title.
   */
  public function getOrderItemTitle();

  /**
   * Gets the parent order ID.
   *
   * @return int|null
   *   The order ID, or NULL.
   */
  public function getOrderId();

  /**
   * Set the parent order ID.
   *
   * @param string $id
   *   The order ID.
   *
   * @return int|null
   *   The order ID, or NULL.
   */
  public function setOrderId($id);

  /**
   * Gets whether the order item has a purchased entity.
   *
   * @return bool
   *   TRUE if the order item has a purchased entity, FALSE otherwise.
   */
  public function hasPurchasedEntity();

  /**
   * Gets the purchased entity.
   *
   * @return \Drupal\digital_store\Entity\PurchasableEntityInterface|null
   *   The purchased entity, or NULL.
   */
  public function getPurchasedEntity();

  /**
   * Gets the purchased entity ID.
   *
   * @return int
   *   The purchased entity ID.
   */
  public function getPurchasedEntityId();

  /**
   * Gets the order item quantity.
   *
   * @return string
   *   The order item quantity
   */
  public function getQuantity();

  /**
   * Sets the order item quantity.
   *
   * @param string $quantity
   *   The order item quantity.
   *
   * @return $this
   */
  public function setQuantity($quantity);

  /**
   * Gets the order item unit price.
   *
   * @return \Drupal\digital_store\Price|null
   *   The order item unit price, or NULL.
   */
  public function getUnitPrice();

  /**
   * Sets the order item unit price.
   *
   * @param \Drupal\digital_store\Price $unit_price
   *   The order item unit price.
   * @param bool $override
   *   Whether the unit price should be overridden.
   *
   * @return $this
   */
  public function setUnitPrice(Price $unit_price, $override = FALSE);

  /**
   * Gets whether the order item unit price is overridden.
   *
   * Overridden unit prices are not updated when the order is refreshed.
   *
   * @return bool
   *   TRUE if the unit price is overridden, FALSE otherwise.
   */
  public function isUnitPriceOverridden();

  /**
   * Gets the order item total price.
   *
   * @return \Drupal\digital_store\Price|null
   *   The order item total price, or NULL.
   */
  public function getTotalPrice();

  /**
   * Get Featured Image.
   *
   * @return string|null
   *   The order item featured image, or NULL.
   */
  public function getFeaturedImage();

  /**
   * Gets the adjusted order item total price.
   *
   * The adjusted total price is calculated by applying the order item's
   * adjustments to the total price. This can include promotions, taxes, etc.
   *
   * @param string[] $adjustment_types
   *   The adjustment types to include in the adjusted price.
   *   Examples: fee, promotion, tax. Defaults to all adjustment types.
   *
   * @return \Drupal\digital_store\Price|null
   *   The adjusted order item total price, or NULL.
   */
  public function getAdjustedTotalPrice(array $adjustment_types = []);

  /**
   * Gets the adjusted order item unit price.
   *
   * Calculated by dividing the adjusted total price by quantity.
   *
   * Useful for refunds and other purposes where there's a need to know
   * how much a single unit contributed to the order total.
   *
   * @param string[] $adjustment_types
   *   The adjustment types to include in the adjusted price.
   *   Examples: fee, promotion, tax. Defaults to all adjustment types.
   *
   * @return \Drupal\digital_store\Price|null
   *   The adjusted order item unit price, or NULL.
   */
  public function getAdjustedUnitPrice(array $adjustment_types = []);

}
