<?php

namespace Drupal\digital_store_cart;

use Drupal\digital_store\Entity\PurchasableEntityInterface;
use Drupal\digital_store_order\Entity\OrderItemInterface;
use Drupal\digital_store_order\Entity\OrderInterface;

/**
 * Manages the cart order and its order items.
 */
interface CartManagerInterface {

  /**
   * Empties the given cart order.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param bool $save_cart
   *   Whether the cart should be saved after the operation.
   */
  public function emptyCart(OrderInterface $cart, $save_cart = TRUE);

  /**
   * Adds the given purchasable entity to the given cart order.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param \Drupal\digital_store\Entity\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *   The quantity.
   * @param bool $combine
   *   Whether the order item should be combined with an existing matching one.
   * @param bool $save_cart
   *   Whether the cart should be saved after the operation.
   *
   * @return \Drupal\digital_store_order\Entity\OrderItemInterface
   *   The saved order item.
   */
  public function addEntity(OrderInterface $cart, PurchasableEntityInterface $entity, $quantity = 1, $combine = TRUE, $save_cart = TRUE);

  /**
   * Creates an order item for the given purchasable entity.
   *
   * @param \Drupal\digital_store\Entity\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *   The quantity.
   *
   * @return \Drupal\digital_store_order\Entity\OrderItemInterface
   *   The created order item. Unsaved.
   */
  public function createOrderItem(PurchasableEntityInterface $entity, $quantity = 1);

  /**
   * Adds the given order item to the given cart order.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param bool $combine
   *   Whether the order item should be combined with an existing matching one.
   * @param bool $save_cart
   *   Whether the cart should be saved after the operation.
   *
   * @return \Drupal\digital_store_order\Entity\OrderItemInterface
   *   The saved order item.
   */
  public function addOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $combine = TRUE, $save_cart = TRUE);

  /**
   * Updates the given order item.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param bool $save_cart
   *   Whether the cart should be saved after the operation.
   */
  public function updateOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $save_cart = TRUE);

  /**
   * Updates the given order item.
   *
   * @param string $order_item_id
   *   The order item ID.
   * @param int $quantity
   *   The quantity.
   *
   * @return bool
   */
  public function updateOrderItemQuantity($order_item_id = NULL, $quantity = 1);

  /**
   * Removes the given order item from the cart order.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param bool $save_cart
   *   Whether the cart should be saved after the operation.
   */
  public function removeOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $save_cart = TRUE);

  /**
   * Removes the given order item from the cart order.
   *
   * @param string $order_item_id
   *   The cart order.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|null
   */
  public function removeOrderItemById($order_item_id = NULL);

}
