<?php

namespace Drupal\digital_store_cart\Form;

use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\digital_store_cart\CartManagerInterface;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\digital_store_order\Entity\OrderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Cart Form.
 *
 * @package digital_store_cart
 */
class CartForm extends FormBase {

  /**
   * The Current Cart.
   *
   * @var \Drupal\digital_store_order\Entity\OrderInterface
   */
  protected $cart = NULL;

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The cart manager.
   *
   * @var \Drupal\digital_store_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\digital_store_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   */
  public function __construct(CartProviderInterface $cart_provider, CartManagerInterface $cart_manager) {
    $this->cartProvider = $cart_provider;
    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('digital_store_cart.cart_provider'),
      $container->get('digital_store_cart.cart_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cart-form';
  }

  /**
   * Get operation data.
   *
   * @param array $triggering_element
   *   An associative array containing the structure of the triggering element.
   *
   * @return array|null
   */
  public function getOperationData(array $triggering_element = []) {
    if (empty($triggering_element)) {
      return NULL;
    }
    $operation = $triggering_element['#operation'] ?? NULL;
    if (empty($operation)) {
      return NULL;
    }
    $data = $triggering_element['#data'] ?? NULL;
    return [
      '#operation' => $operation,
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cart = $this->getCart();
    // We want to deal with hierarchical form values.
    $form['#tree'] = TRUE;
    $form['#title'] = 'Your Shopping Cart';
    $form['#prefix'] = '<div class="c"><div class="r"><div class="col shopping-cart-wrapper" id="shopping-cart-form-wrapper">';
    $form['#suffix'] = '</div></div></div>';
    $form['#attributes'] = [
      'class' => ['shopping-cart-form', 'b'],
      'itemscope' => '',
      'itemtype' => 'http://schema.org/Order',
    ];
    $form['header'] = [
      '#type' => 'item',
      '#markup' => '<h3 class="t">' . t('Your Shopping Cart') . '</h3>',
    ];
    // Shopping Cart form elements.
    if ($this->isCartEmpty($cart)) {
      // Show Empty cart message,
      $form['message'] = [
        '#type' => 'item',
        '#markup' => '<h3>' . t("You don't have any items in your cart yet.") . '</h3><br>',
      ];
      $form['continue_shopping'] = [
        '#title' => t('&#8592; Continue shopping.'),
        '#type' => 'link',
        '#url' => Url::fromUri('internal:/'),
        '#attributes' => ['class' => ['continue-shopping', 'clearfix']],
      ];
      // Stop Here.
      return $form;
    }
    // Cart Details Container.
    $form['cart_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['cart-details'],
      ],
      '#prefix' => '<div class="col-md-12 col-sm-12">',
      '#suffix' => '</div>',
    ];
    // Order Items.
    $form['cart_details']['order_items'] = [
      '#type' => 'table',
      '#header' => [
        NULL,
        t('Item'),
        t('Qty'),
        t('Unit Price'),
        t('Subtotal'),
      ],
      '#attributes' => [
        'class' => ['order-items'],
      ],
    ];
    $cache_tags = $cart->getEntity()->getCacheTags();
    $items = $cart->getItems();
    /* @var \Drupal\digital_store_order\Entity\OrderItemInterface $order_item */
    foreach ($items as $delta => $order_item) {
      $order_item_id = $order_item->id();
      $order_item_tags = $order_item->getEntity()->getCacheTags();
      $cache_tags = Cache::mergeTags($cache_tags, $order_item_tags);
      $form['cart_details']['order_items'][$order_item_id] = [
        'featured_image' => $order_item->getFeaturedImage(),
        'product' => $order_item->getOrderItemTitle(),
        'quantity' => [
          '#type' => 'number',
          '#default_value' => $order_item->getQuantity(),
          '#title' => t('Qty'),
          '#title_display' => 'invisible',
          '#min' => 1,
          '#attributes' => ['class' => ['form-control', 'quantity']],
        ],
        'unit_price' => [
          '#markup' => "<div class='item'>{$order_item->getUnitPrice()}</div>",
          '#title' => t('Unit Price'),
          '#title_display' => 'invisible',
        ],
        'total' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['item-action'],
          ],
          'total' => [
            '#markup' => "<div class='item-total'>{$order_item->getTotalPrice()}</div>",
          ],
          'remove' => [
            '#type' => 'submit',
            '#value' => $this->t('Remove Order Item @order_item_id', ['@order_item_id' => $order_item_id]),
            '#data' => ['order_item_id' => $order_item_id],
            '#operation' => 'remove_order_item',
            '#attributes' => ['class' => ['btn', 'remove-order-item']],
          ],
        ],
      ];
    }
    $form['cart_details']['bottom'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['r', 'bottom'],
      ],
    ];
    $form['cart_details']['bottom']['continue_shopping'] = [
      '#title' => t('&#8592; Continue Shopping'),
      '#type' => 'link',
      '#url' => Url::fromUri('internal:/'),
      '#attributes' => ['class' => ['continue-shopping']],
      '#prefix' => '<div class="col">',
      '#suffix' => '',
    ];
    $form['cart_details']['bottom']['update_cart'] = [
      '#type' => 'submit',
      '#value' => t('Update cart'),
      '#operation' => 'update_cart_quantities',
      '#skip_validation' => TRUE,
      '#attributes' => [
        'class' => [
          'btn',
          'btn-secondary',
          'update-cart-quantities-item'
        ]
      ],
      '#prefix' => '',
      '#suffix' => '</div>',
    ];
    $form['cart_details']['bottom']['order_total'] = [
      '#type' => 'item',
      '#markup' => '<div class="col"><div class="order-total pull-right">Total:' . $cart->getTotalPrice() . '</div></div>',
    ];
    $customer = $this->currentUser();
    $is_anonymous = $customer->isAnonymous();
    if ($is_anonymous) {
      $form['cart_details']['account'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['col-md-12', 'col-sm-12', 'account-details'],
        ],
      ];
      $form['cart_details']['account']['checkout_options'] = [
        '#type' => 'radios',
        '#title' => t('Checkout Options'),
        '#default_value' => 0,
        '#options' => [
          0 => t('Guest Checkout'),
          1 => t('Create An Account?'),
        ],
        '#description' => t("By creating an account you will be able to shop faster, be up to date on an order's status, and keep track of the orders you have previously made."),
        '#attributes' => [
          'class' => ['checkout-options'],
        ],
      ];
      $form['cart_details']['account']['email'] = [
        '#type' => 'textfield',
        '#title' => t('Email address'),
        '#default_value' => '',
        '#required' => TRUE,
        '#prefix' => '<div class="form-row account-credentials"><div class="form-group col">',
        '#suffix' => '</div>',
        '#attributes' => [
          'placeholder' => t('Enter Your Email address'),
          'class' => ['form-control'],
        ],
      ];
      $form['cart_details']['account']['pass'] = [
        '#type' => 'password',
        '#title' => t('Create account password'),
        '#prefix' => '<div class="form-group col">',
        '#suffix' => '</div></div>',
        '#attributes' => [
          'placeholder' => t('Enter Password'),
          'class' => ['form-control'],
        ],
        '#states' => [
          'visible' => [
            ':input[name="checkout_options"]' => ['value' => '1'],
          ],
        ],
      ];
    }
    // Set cache.
    $form['#cache'] = [
      'keys' => ['entity_view', 'node', $cart->id()],
      'tags' => $cache_tags,
    ];
    // Return Form.
    return $form;
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $data = $this->getOperationData($form_state->getTriggeringElement());
    if (empty($data)) {
      return;
    }
    $form_state->setValue('operation_data', $data);
    $this->processOperation($form, $form_state);
  }

  /**
   * Handles Cart actions.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form passed by reference.
   */
  public function processOperation(array $form = [], FormStateInterface &$form_state = NULL) {
    $data = $form_state->getValue('operation_data');
    if (empty($data)) {
      return NULL;
    }

    $operation = $data['#operation'] ?? NULL;
    switch ($operation) {

      case 'remove_order_item':
        $order_item_id = $data['#data']['order_item_id'] ?? NULL;
        $this->cartManager->removeOrderItemById($order_item_id);
        $this->messenger()
          ->addStatus($this->t('The product has been removed from your cart.'));
        $this->resetCart();
        break;

      case 'update_cart_quantities':
        $order_items = $form_state->getValue(['cart_details', 'order_items']);
        $success = $this->updateCartQuantities($order_items);
        if ($success) {
          $this->messenger()
            ->addStatus($this->t('Your cart was updated successfully.'));
        }
        else {
          $this->messenger()
            ->addWarning($this->t('A problem occurred while updating the cart, please try again.'));
        }
        $this->resetCart();
        break;

    }
    $form_state->setRebuild(TRUE);
    return $form;
  }

  /**
   * Reset the Cart.
   */
  public function resetCart() {
    $this->cart = NULL;
  }

  /**
   * Get Cart.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The current user cart.
   */
  public function setCart(OrderInterface $cart = NULL) {
    return $this->cart = $cart;
  }

  /**
   * Get Cart.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The current user cart.
   */
  public function getCart() {
    if (!$this->cart) {
      $this->cart = $this->cartProvider->getCart();
    }
    return $this->cart;
  }

  /**
   * Checks if the cart is empty.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return bool
   *   TRUE if the #order value is valid, FALSE otherwise.
   */
  public function isCartEmpty(OrderInterface $order = NULL) {
    if (!$order) {
      return TRUE;
    }
    if (empty($order->getItems())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Update Cart Quantities.
   *
   * @param array $order_items
   *   The order items array.
   *
   * @return bool
   *   TRUE if the cart quantities were updated, FALSE otherwise.
   */
  public function updateCartQuantities(array $order_items = []) {
    if (empty($order_items)) {
      return NULL;
    }
    $success = TRUE;
    foreach ($order_items as $order_item_id => $item) {
      $quantity = $item['quantity'] ?? 1;
      $result = $this->cartManager->updateOrderItemQuantity($order_item_id, $quantity);
      if (!$result) {
        $success = FALSE;
      }
    }
    return $success;
  }

}
