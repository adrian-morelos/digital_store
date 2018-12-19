<?php

namespace Drupal\digital_store_order\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\digital_store_cart\CartProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Order listing build.
 */
class OrderListBuilder extends ControllerBase {

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(CartProviderInterface $cart_provider) {
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('digital_store_cart.cart_provider')
    );
  }

  /**
   * Outputs a cart view for each non-empty cart belonging to the current user.
   *
   * @return array
   *   A render array.
   */
  public function listingPage($cart_flag = FALSE, $title = '', $empty_message = '') {
    $build = [];
    // The table title.
    $build['#title'] = $title;
    // Build de filters.
    $build['form'] = [
      '#type' => 'form',
    ];
    $build['form']['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filters'),
      '#open' => TRUE,
    ];
    $build['form']['filters']['text'] = [
      '#title' => 'text',
      '#type' => 'search'
    ];
    $build['form']['filters']['actions'] = [
      '#type' => 'actions'
    ];
    $build['form']['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter')
    ];
    // Build the table.s
    $carts = $this->cartProvider->getAllOrders($cart_flag);
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\digital_store_order\Entity\OrderInterface $cart */
      return $cart->hasItems();
    });
    $header = [
      ['data' => $this->t('#'), 'field' => 'order_number', 'sort' => 'asc'],
      ['data' => $this->t('Added'), 'field' => 'added'],
      ['data' => $this->t('Updated'), 'field' => 'updated'],
      ['data' => $this->t('Customer'), 'field' => 'customer'],
      ['data' => $this->t('State'), 'field' => 'state'],
      ['data' => $this->t('Total'), 'field' => 'total'],
      ['data' => $this->t('Operations'), 'field' => 'operations'],
    ];
    // Populate the rows.
    $rows = [];
    /** @var \Drupal\digital_store_order\Entity\OrderInterface $cart */
    foreach ($carts as $cart_id => $cart) {
      $item = [
        'order_number' => $cart->id(),
        'added' => $cart->getCreatedTime($formatted = TRUE),
        'updated' => $cart->getChangedTime($formatted = TRUE),
        'customer' => $cart->getEmail(),
        'state' => $cart->getState(),
        'total' => $cart->getTotalPrice(),
        'operations' => '',
      ];
      $rows[] = ['data' => $item];
    }
    // Generate the table.
    $build['carts'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $empty_message,
    ];
    // Add the pager.
    $build['pager'] = [
      '#type' => 'pager'
    ];
    return $build;
  }

}
