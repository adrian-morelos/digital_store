<?php

namespace Drupal\digital_store\Form;

use Drupal\node\NodeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\digital_store_order\Entity\OrderItem;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\digital_store_cart\CartManagerInterface;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\digital_store\Entity\ProductVariation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the Subscription Option form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class SubscriptionOptionsForm extends FormBase {

  /**
   * The cart manager.
   *
   * @var \Drupal\digital_store_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The product variation storage.
   *
   * @var \Drupal\Node\NodeInterface
   */
  protected $productVariationStorage;

  /**
   * Constructs a new PricePlainFormatter object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\digital_store_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider) {
    $this->currencyStorage = $entity_type_manager->getStorage('currency');
    $this->productVariationStorage = $entity_type_manager->getStorage('node');
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('digital_store_cart.cart_manager'),
      $container->get('digital_store_cart.cart_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'subscription_options_form';
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '50%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form = [], FormStateInterface $form_state, array $options = []) {
    if (empty($options)) {
      return [];
    }
    // Add the core AJAX library.
    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('<h4>Subscription options</h4>'),
    ];
    $messages = [
      $this->t('<p>A subscription entitles you to <a target="_blank" href="/support-policy/"><strong>1 year of updates and support</strong></a> from the date of purchase.</p>'),
      $this->t('<p>Each installation of the plugin will require a license key.</p>'),
    ];
    $form['description'] = [
      '#type' => 'item',
      '#markup' => implode('', $messages),
    ];
    $price_options = [];
    foreach ($options as $delta => $entity) {
      $price_options[$entity->id()] = $this->getOptionDescription($entity);
    }
    $form['price_options'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#options' => $price_options,
      '#default_value' => key($price_options),
    ];
    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Buy Now'),
      '#attributes' => ['class' => ['btn btn-primary buy_now']],
      '#ajax' => [
        'callback' => '::ajaxSubmitBuyNow',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];
    $form['actions']['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Cart'),
      '#attributes' => ['class' => ['btn btn-secondary add_to_cart']],
    ];
    return $form;
  }

  /**
   * Get price option description markup.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The price option entity.
   *
   * @return string|null
   *   The formatted option description.
   */
  public function getOptionDescription(NodeInterface $entity = NULL) {
    if (!$entity) {
      return NULL;
    }
    $label = $entity->get('field_label')->getString();
    $price_item = $entity->get('field_price');
    $data_price = $price_item->isEmpty() ? NULL : $price_item->first()->getValue();
    $price = $this->formatPrice($data_price);
    return "<div class='name'>{$label}</div><div class='price'>{$price}</div>";
  }

  /**
   * Format price option.
   *
   * @param array $data_price
   *   The array with data price.
   *
   * @return string|null
   *   The formatted price markup.
   */
  public function formatPrice(array $data_price = []) {
    if (empty($data_price)) {
      return NULL;
    }
    $number = $data_price['number'] ?? NULL;
    $currency_code = $data_price['currency_code'] ?? NULL;
    if (empty($number) || empty($currency_code)) {
      return NULL;
    }
    $price = number_format($number, 2, '.', ',');
    /* @var \Drupal\digital_store\Entity\Currency $currency */
    $currency = $this->currencyStorage->load($currency_code);
    $symbol = $currency ? $currency->getSymbol() : NULL;
    return "<span class='currency-symbol'>{$symbol}</span>{$price}";
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form = [], FormStateInterface $form_state) {
  }

  /**
   * Implements the submit handler for the modal dialog AJAX call.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array of AJAX commands to execute on submit of the modal form.
   */
  public function ajaxSubmitBuyNow(array &$form = [], FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('price_options');
    $order_item = $this->getOrderItemFromSelectedVariation($variation_id);
    if ($order_item) {
      $cart = $this->cartProvider->getCart();
      if (!$cart) {
        $cart = $this->cartProvider->createCart();
      }
      $this->cartManager->addOrderItem($cart, $order_item, TRUE);
    }
    else {
      // Show a error.
    }

    // We begin building a new ajax response.
    $response = new AjaxResponse();

    // If the user submitted the form and there are errors, show them the
    // input dialog again with error messages. Since the title element is
    // required, the empty string wont't validate and there will be an error.
    if ($form_state->getErrors()) {
      // If there are errors, we can show the form again with the errors in
      // the status_messages section.
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new OpenModalDialogCommand($this->t('Errors'), $form, static::getDataDialogOptions()));
    }
    // If there are no errors, show the output dialog.
    else {
      // We don't want any messages that were added by submitForm().
      $this->messenger()->deleteAll();
      $selector = 'section.b';
      $content = [
        '#type' => 'modal_box',
        '#open' => TRUE,
        '#content' => [
          '#markup' => '<pre> Selected Variation: ' . json_encode($variation_id) . '</pre>',
        ]
      ];
      // Add the Checkout Dialog to the response. This will cause Drupal
      // AJAX to show the Checkout dialog. The user can click the little X to close
      // the dialog.
      $response->addCommand(new ReplaceCommand($selector, $content));
    }
    // Finally return our response.
    return $response;
  }

  /**
   * Get order item from selected variation ID.
   *
   * @param string $variation_id
   *   The selected variation.
   *
   * @return \Drupal\digital_store_order\Entity\OrderItemInterface|null
   *   The order item, otherwise NULL.
   */
  public function getOrderItemFromSelectedVariation($variation_id = NULL) {
    if (empty($variation_id)) {
      return NULL;
    }
    $entity = $this->productVariationStorage->load($variation_id);
    if (!$entity) {
      return NULL;
    }
    $variation = new ProductVariation($entity);
    return OrderItem::createFromPurchasableEntity($variation);
  }

}
