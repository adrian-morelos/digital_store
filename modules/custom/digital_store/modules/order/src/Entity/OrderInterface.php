<?php

namespace Drupal\digital_store_order\Entity;

use Drupal\user\UserInterface;
use Drupal\digital_store\Price;
use Drupal\digital_store_order\EntityAdjustableInterface;

/**
 * Defines the interface for orders.
 */
interface OrderInterface extends EntityAdjustableInterface {

  /**
   * Gets the order number.
   *
   * @return string
   *   The order number.
   */
  public function getOrderNumber();

  /**
   * Gets the customer user.
   *
   * @return \Drupal\user\UserInterface
   *   The customer user entity. If the order is anonymous (customer
   *   unspecified or deleted), an anonymous user will be returned. Use
   *   $customer->isAnonymous() to check.
   */
  public function getCustomer();

  /**
   * Sets the customer user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The customer user entity.
   *
   * @return $this
   */
  public function setCustomer(UserInterface $account);

  /**
   * Gets the customer user ID.
   *
   * @return int
   *   The customer user ID ('0' if anonymous).
   */
  public function getCustomerId();

  /**
   * Sets the customer user ID.
   *
   * @param int $uid
   *   The customer user ID.
   *
   * @return $this
   */
  public function setCustomerId($uid);

  /**
   * Gets the order email.
   *
   * @return string
   *   The order email.
   */
  public function getEmail();

  /**
   * Sets the order email.
   *
   * @param string $mail
   *   The order email.
   *
   * @return $this
   */
  public function setEmail($mail);

  /**
   * Gets the order IP address.
   *
   * @return string
   *   The IP address.
   */
  public function getIpAddress();

  /**
   * Sets the order IP address.
   *
   * @param string $ip_address
   *   The IP address.
   *
   * @return $this
   */
  public function setIpAddress($ip_address);

  /**
   * Gets the order items.
   *
   * @return \Drupal\digital_store_order\Entity\OrderItemInterface[]
   *   The order items.
   */
  public function getItems();

  /**
   * Sets the order items.
   *
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface[] $order_items
   *   The order items.
   *
   * @return $this
   */
  public function setItems(array $order_items);

  /**
   * Gets whether the order has order items.
   *
   * @return bool
   *   TRUE if the order has order items, FALSE otherwise.
   */
  public function hasItems();

  /**
   * Adds an order item.
   *
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return $this
   */
  public function addItem(OrderItemInterface $order_item);

  /**
   * Removes an order item.
   *
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return $this
   */
  public function removeItem(OrderItemInterface $order_item);

  /**
   * Checks whether the order has a given order item.
   *
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return bool
   *   TRUE if the order item was found, FALSE otherwise.
   */
  public function hasItem(OrderItemInterface $order_item);

  /**
   * Gets the order subtotal price.
   *
   * Represents a sum of all order item totals.
   *
   * @return \Drupal\digital_store\Price|null
   *   The order subtotal price, or NULL.
   */
  public function getSubtotalPrice();

  /**
   * Recalculates the order total price.
   *
   * @return $this
   */
  public function recalculateTotalPrice();

  /**
   * Gets the order total price.
   *
   * Represents a sum of all order item totals along with adjustments.
   *
   * @return \Drupal\digital_store\Price|null
   *   The order total price, or NULL.
   */
  public function getTotalPrice();

  /**
   * Gets the total paid price.
   *
   * @return \Drupal\digital_store\Price|null
   *   The total paid price, or NULL.
   */
  public function getTotalPaid();

  /**
   * Sets the total paid price.
   *
   * @param \Drupal\digital_store\Price $total_paid
   *   The total paid price.
   */
  public function setTotalPaid(Price $total_paid);

  /**
   * Gets the order balance.
   *
   * Calculated by subtracting the total paid price from the total price.
   * Can be negative in case the order was overpaid.
   *
   * @return \Drupal\digital_store\Price|null
   *   The order balance, or NULL.
   */
  public function getBalance();

  /**
   * Gets whether the order has been fully paid.
   *
   * The order has been fully paid if its balance is zero or negative.
   *
   * @return bool
   *   TRUE if the order has been fully paid, FALSE otherwise.
   */
  public function isPaid();

  /**
   * Gets whether the order is locked.
   *
   * @return bool
   *   TRUE if the order is locked, FALSE otherwise.
   */
  public function isLocked();

  /**
   * Gets whether the order is on the cart.
   *
   * @return bool
   *   TRUE if the order is on the cart, FALSE otherwise.
   */
  public function orderIsOnCart();

  /**
   * Sets is order in the cart.
   *
   * @param bool $value
   *   The cart flag value.
   *
   * @return $this
   */
  public function setIsOnCart($value = FALSE);

  /**
   * Locks the order.
   *
   * @return $this
   */
  public function lock();

  /**
   * Unlocks the order.
   *
   * @return $this
   */
  public function unlock();

  /**
   * Gets the order placed timestamp.
   *
   * @return int
   *   The order placed timestamp.
   */
  public function getPlacedTime();

  /**
   * Sets the order placed timestamp.
   *
   * @param int $timestamp
   *   The order placed timestamp.
   *
   * @return $this
   */
  public function setPlacedTime($timestamp);

  /**
   * Gets the order completed timestamp.
   *
   * @return int
   *   The order completed timestamp.
   */
  public function getCompletedTime();

  /**
   * Sets the order completed timestamp.
   *
   * @param int $timestamp
   *   The order completed timestamp.
   *
   * @return $this
   */
  public function setCompletedTime($timestamp);

  /**
   * Sets the order state.
   *
   * @param string $value
   *   The order state value.
   *
   * @return $this
   */
  public function setState($value);

  /**
   * Gets the order state.
   *
   * @return string
   *   The order state.
   */
  public function getState();

  /**
   * Gets the Billing Details.
   *
   * @return array
   *   The order billing detail.
   */
  public function getBillingDetail();

  /**
   * Sets the order billing detail.
   *
   * @param array $address
   *   The address array.
   *
   * @return $this
   */
  public function setBillingDetail(array $address = []);

}
