<?php

namespace Drupal\digital_store_cart;

use Drupal\digital_store\Calculator;
use Drupal\digital_store_order\Entity\OrderItem;
use Drupal\digital_store_order\OrderStatusInterface;
use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\digital_store_order\Entity\OrderItemInterface;
use Drupal\digital_store_order\OrderItemMatcherInterface;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Default implementation of the cart manager.
 */
class CartManager implements CartManagerInterface {

  /**
   * The order item matcher.
   *
   * @var \Drupal\digital_store_order\OrderItemMatcherInterface
   */
  protected $orderItemMatcher;

  /**
   * Constructs a new CartManager object.
   *
   * @param \Drupal\digital_store_order\OrderItemMatcherInterface $order_item_matcher
   *   The order item matcher.
   */
  public function __construct(OrderItemMatcherInterface $order_item_matcher) {
    $this->orderItemMatcher = $order_item_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public function emptyCart(OrderInterface $cart, $save_cart = TRUE) {
    $order_items = $cart->getItems();
    foreach ($order_items as $order_item) {
      $order_item->delete();
    }
    $cart->setItems([]);
    $this->resetCheckoutStep($cart);
    if ($save_cart) {
      $cart->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addEntity(OrderInterface $cart, PurchasableEntityInterface $entity, $quantity = 1, $combine = TRUE, $save_cart = TRUE) {
    $order_item = $this->createOrderItem($entity, $quantity);
    return $this->addOrderItem($cart, $order_item, $combine, $save_cart);
  }

  /**
   * {@inheritdoc}
   */
  public function createOrderItem(PurchasableEntityInterface $entity, $quantity = 1) {
    $order_item = OrderItem::createFromPurchasableEntity($entity, [
      'order_item_quantity' => $quantity,
    ]);
    return $order_item;
  }

  /**
   * {@inheritdoc}
   */
  public function addOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $combine = TRUE, $save_cart = TRUE) {
    $quantity = $order_item->getQuantity();
    $matching_order_item = NULL;
    if ($combine) {
      $matching_order_item = $this->orderItemMatcher->match($order_item, $cart->getItems());
    }
    if ($matching_order_item) {
      $new_quantity = Calculator::add($matching_order_item->getQuantity(), $quantity);
      $matching_order_item->setQuantity($new_quantity);
      $matching_order_item->save();
      $saved_order_item = $matching_order_item;
    }
    else {
      $order_item->set('order', $cart->id());
      $order_item->save();
      $cart->addItem($order_item);
      $saved_order_item = $order_item;
    }
    $this->resetCheckoutStep($cart);
    if ($save_cart) {
      $cart->save();
    }

    return $saved_order_item;
  }

  /**
   * {@inheritdoc}
   */
  public function updateOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $save_cart = TRUE) {
    $order_item->save();
    $this->resetCheckoutStep($cart);
    if ($save_cart) {
      $cart->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeOrderItem(OrderInterface $cart, OrderItemInterface $order_item, $save_cart = TRUE) {
    $order_item->delete();
    $cart->removeItem($order_item);
    $this->resetCheckoutStep($cart);
    if ($save_cart) {
      $cart->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeOrderItemById($order_item_id = NULL) {
    if (empty($order_item_id)) {
      return FALSE;
    }
    $order_item = OrderItem::load($order_item_id);
    if (!$order_item) {
      return FALSE;
    }
    $this->removeOrderItem($order_item->getOrder(), $order_item);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function updateOrderItemQuantity($order_item_id = NULL, $quantity = 1) {
    if (empty($order_item_id)) {
      return FALSE;
    }
    if (!(intval($quantity) > 0)) {
      $quantity = 1;
    }
    $order_item = OrderItem::load($order_item_id);
    if (!$order_item) {
      return FALSE;
    }
    $order_item->setQuantity($quantity);
    $this->updateOrderItem($order_item->getOrder(), $order_item);
    return TRUE;
  }

  /**
   * Resets the checkout step.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   */
  protected function resetCheckoutStep(OrderInterface $cart) {
    if ($cart->hasField('state')) {
      $cart->set('state', OrderStatusInterface::DRAFT);
    }
    if ($cart->hasField('checkout_flow_step')) {
      $cart->set('checkout_flow_step', 'shopping.cart');
    }
  }

}
