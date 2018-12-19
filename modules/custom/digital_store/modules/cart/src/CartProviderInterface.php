<?php

namespace Drupal\digital_store_cart;

use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Creates and loads carts for anonymous and authenticated users.
 *
 * @see \Drupal\digital_store_cart\CartSessionInterface
 */
interface CartProviderInterface {

  /**
   * Creates a cart order for the given store and user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface
   *   The created cart order.
   *
   * @throws \Drupal\digital_store_cart\Exception\DuplicateCartException
   *   When a cart with the given criteria already exists.
   */
  public function createCart(AccountInterface $account = NULL);

  /**
   * Finalizes the given cart order.
   *
   * Removes the cart flag from the order and saves it.
   * If the user is anonymous, moves the cart ID from the
   * active to the completed cart session.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $cart
   *   The cart order.
   * @param bool $save_cart
   *   Whether to immediately save the cart or not.
   */
  public function finalizeCart(OrderInterface $cart, $save_cart = TRUE);

  /**
   * Gets the cart order for the given store and user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The cart order, or NULL if none found.
   */
  public function getCart(AccountInterface $account = NULL);

  /**
   * Gets the cart order ID for the given store and user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return int|null
   *   The cart order ID, or NULL if none found.
   */
  public function getCartId(AccountInterface $account = NULL);

  /**
   * Gets all cart orders for the given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface[]
   *   A list of cart orders.
   */
  public function getCarts(AccountInterface $account = NULL);

  /**
   * Gets all cart order ids for the given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return int[]
   *   A list of cart orders ids.
   */
  public function getCartIds(AccountInterface $account = NULL);

  /**
   * Gets all orders.
   *
   * @param bool $cart
   *   The fag to include only cart orders or not.
   *
   * @return int[]
   *   A list of cart orders ids.
   */
  public function getAllOrders($cart = FALSE);

  /**
   * Clears the static caches.
   */
  public function clearCaches();

}
