<?php

namespace Drupal\digital_store_cart;

use Drupal\node\NodeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\digital_store_order\Entity\Order;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\digital_store_order\OrderStatusInterface;
use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\digital_store_cart\Exception\DuplicateCartException;

/**
 * Default implementation of the cart provider.
 */
class CartProvider implements CartProviderInterface {

  /**
   * The order storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $orderStorage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The session.
   *
   * @var \Drupal\digital_store_cart\CartSessionInterface
   */
  protected $cartSession;

  /**
   * The loaded cart data, grouped by uid, then keyed by cart order ID.
   *
   * @var array
   */
  protected $cartData = [];

  /**
   * Constructs a new CartProvider object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\digital_store_cart\CartSessionInterface $cart_session
   *   The cart session.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, CartSessionInterface $cart_session) {
    $this->orderStorage = $entity_type_manager->getStorage('node');
    $this->currentUser = $current_user;
    $this->cartSession = $cart_session;
  }

  /**
   * {@inheritdoc}
   */
  public function createCart(AccountInterface $account = NULL) {
    $account = $account ?: $this->currentUser;
    $uid = $account->id();
    if ($this->getCartId($account)) {
      // Don't allow multiple cart orders matching the same criteria.
      throw new DuplicateCartException("A cart order for the account '$uid' already exists.");
    }
    $type = 'order';
    // Create the new cart order.
    $entity = $this->orderStorage->create([
      'type' => $type,
      'title' => 'Shopping Cart Order',
      'customer' => $uid,
      'state' => OrderStatusInterface::DRAFT,
      'cart' => TRUE,
    ]);
    $entity->save();
    // Store the new cart order id in the anonymous user's session so that it
    // can be retrieved on the next page load.
    if ($account->isAnonymous()) {
      $this->cartSession->addCartId($entity->id());
    }
    // Cart data has already been loaded, add the new cart order to the list.
    if (isset($this->cartData[$uid])) {
      $this->cartData[$uid][$entity->id()] = [
        'type' => $type,
      ];
    }
    return $this->getOrderFromEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getCart(AccountInterface $account = NULL) {
    $cart_id = $this->getCartId($account);
    if (empty($cart_id)) {
      return NULL;
    }
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $this->orderStorage->load($cart_id);
    if (!$entity) {
      return NULL;
    }
    $cart = $this->getOrderFromEntity($entity);
    return $cart;
  }

  /**
   * {@inheritdoc}
   */
  public function getCartId(AccountInterface $account = NULL) {
    $cart_id = NULL;
    $cart_data = $this->loadCartData($account);
    if ($cart_data) {
      $search = [
        'type' => 'order',
      ];
      $cart_id = array_search($search, $cart_data);
    }
    return $cart_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getCarts(AccountInterface $account = NULL) {
    $carts = [];
    $cart_ids = $this->getCartIds($account);
    if ($cart_ids) {
      $entities = $this->orderStorage->loadMultiple($cart_ids);
      $carts = $this->getOrdersFromEntities($entities);
    }
    return $carts;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllOrders($cart = FALSE) {
    $query = $this->orderStorage->getQuery()
      ->condition('cart', $cart)
      ->sort('nid', 'DESC')
      ->accessCheck(FALSE);
    $cart_ids = $query->execute();
    $entities = $this->orderStorage->loadMultiple($cart_ids);
    $carts = $this->getOrdersFromEntities($entities);
    return $carts;
  }

  /**
   * Get Order from node entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node entity
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface
   *   The order entity.
   */
  protected function getOrderFromEntity(NodeInterface $entity = NULL) {
    return new Order($entity);
  }

  /**
   * Get Order from node entity.
   *
   * @param array $entities
   *   The array of node entities.
   *
   * @return array
   *   The order entity.
   */
  protected function getOrdersFromEntities(array $entities = []) {
    if (empty($entities)) {
      return [];
    }
    $carts = [];
    foreach ($entities as $delta => $entity) {
      $carts[] = $this->getOrderFromEntity($entity);
    }
    return $carts;
  }

  /**
   * {@inheritdoc}
   */
  public function finalizeCart(OrderInterface $cart, $save_cart = TRUE) {
    if ($save_cart) {
      $cart->save();
    }
    // The cart is anonymous, move it to the 'completed' session.
    if (!$cart->getCustomerId()) {
      $this->cartSession->deleteCartId($cart->id(), CartSession::ACTIVE);
      $this->cartSession->addCartId($cart->id(), CartSession::COMPLETED);
    }
    // Remove the cart order from the internal cache, if present.
    unset($this->cartData[$cart->getCustomerId()][$cart->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCartIds(AccountInterface $account = NULL) {
    $cart_data = $this->loadCartData($account);
    return array_keys($cart_data);
  }

  /**
   * {@inheritdoc}
   */
  public function clearCaches() {
    $this->cartData = [];
  }

  /**
   * Loads the cart data for the given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user. If empty, the current user is assumed.
   *
   * @return array
   *   The cart data.
   */
  protected function loadCartData(AccountInterface $account = NULL) {
    $account = $account ?: $this->currentUser;
    $uid = $account->id();
    if (isset($this->cartData[$uid])) {
      return $this->cartData[$uid];
    }
    if ($account->isAuthenticated()) {
      $query = $this->orderStorage->getQuery()
        ->condition('cart', TRUE)
        ->condition('customer', $account->id())
        ->sort('nid', 'DESC')
        ->accessCheck(FALSE);
      $cart_ids = $query->execute();
    }
    else {
      $cart_ids = $this->cartSession->getCartIds();
    }

    $this->cartData[$uid] = [];
    if (!$cart_ids) {
      return [];
    }
    // Getting the cart data and validating the cart IDs received from the
    // session requires loading the entities. This is a performance hit, but
    // it's assumed that these entities would be loaded at one point anyway.
    /** @var \Drupal\digital_store_order\Entity\OrderInterface[] $carts */
    $carts = $this->orderStorage->loadMultiple($cart_ids);
    $non_eligible_cart_ids = [];
    foreach ($carts as $entity) {
      $cart = $this->getOrderFromEntity($entity);
      if ($cart->isLocked()) {
        // Skip locked carts, the customer is probably off-site for payment.
        continue;
      }
      if (($cart->getCustomerId() != $uid) || !$cart->orderIsOnCart() || ($cart->getState() != OrderStatusInterface::DRAFT)) {
        // Skip carts that are no longer eligible.
        $non_eligible_cart_ids[] = $cart->id();
        continue;
      }

      $this->cartData[$uid][$cart->id()] = [
        'type' => $cart->bundle(),
      ];
    }
    // Avoid loading non-eligible carts on the next page load.
    if (!$account->isAuthenticated()) {
      foreach ($non_eligible_cart_ids as $cart_id) {
        $this->cartSession->deleteCartId($cart_id);
      }
    }
    return $this->cartData[$uid];
  }

}