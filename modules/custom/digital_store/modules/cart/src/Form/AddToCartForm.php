<?php

namespace Drupal\digital_store_cart\Form;

use Drupal\Core\Link;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\digital_store_cart\CartManagerInterface;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\digital_store_product\Entity\ProductVariation;
use Drupal\digital_store_product\Entity\ProductInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\digital_store_product\Entity\ProductVariationInterface;
use Drupal\digital_store_price\Resolver\ChainPriceResolverInterface;

/**
 * Provides the order item add to cart form.
 */
class AddToCartForm extends FormBase {

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
   * The chain base price resolver.
   *
   * @var \Drupal\digital_store_price\Resolver\ChainPriceResolverInterface
   */
  protected $chainPriceResolver;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The product variation storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $productVariationStorage;

  /**
   * Constructs a new AddToCartForm object.
   *
   * @param \Drupal\digital_store_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\digital_store_price\Resolver\ChainPriceResolverInterface $chain_price_resolver
   *   The chain base price resolver.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, ChainPriceResolverInterface $chain_price_resolver, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->chainPriceResolver = $chain_price_resolver;
    $this->productVariationStorage = $entity_type_manager->getStorage('node');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('digital_store_cart.cart_manager'),
      $container->get('digital_store_cart.cart_provider'),
      $container->get('digital_store_price.chain_price_resolver'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_to_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form = [], FormStateInterface $form_state) {
    /* @var \Drupal\digital_store_product\Entity\ProductInterface $product */
    $product = $form_state->get('product');
    if (!$product) {
      return [];
    }
    // The widgets are allowed to signal that the form should be hidden
    // (because there's no purchasable entity to select, for example).
    if ($form_state->get('hide_form')) {
      $form['#access'] = FALSE;
    }
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['c'],
      ],
    ];
    $form['container']['left'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-md-6'],
      ],
      '#prefix' => '<div class="r">',
    ];
    $form['container']['left']['header_summary'] = $this->getProductSummary($product);
    $form['container']['right'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-md-6'],
      ],
      '#suffix' => '</div>',
    ];
    $form['container']['right']['subscription_options'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['s-o'],
      ],
    ];
    $setting = $this->getProductSetting($product);
    $title = $setting['title'] ?? NULL;
    $description = $setting['description'] ?? NULL;
    $quick_buy = $setting['quick_buy'] ?? FALSE;
    if (!empty($title)) {
      $form['container']['right']['subscription_options']['title'] = [
        '#type' => 'item',
        '#markup' => '<h4>' . $title . '</h4>',
      ];
    }
    if (!empty($description)) {
      $form['container']['right']['subscription_options']['description'] = [
        '#type' => 'item',
        '#markup' => $description,
      ];
    }
    $default_variation = $product->getDefaultVariation();
    $price_options = $this->getVariationOptions($product);
    // Display Price Options.
    $form['container']['right']['subscription_options']['variation'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#options' => $price_options,
      '#default_value' => $default_variation->id(),
    ];
    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form.
    $form['container']['right']['subscription_options']['actions'] = [
      '#type' => 'actions',
    ];
    if (!empty($quick_buy)) {
      $form['container']['right']['subscription_options']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Buy Now'),
        '#attributes' => ['class' => ['btn btn-success buy_now']],
      ];
    }
    $form['container']['right']['subscription_options']['actions']['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to Cart'),
      '#attributes' => ['class' => ['btn btn-light add_to_cart']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('variation');
    $order_item = $this->getOrderItemFromSelectedVariation($variation_id);
    if (!$order_item) {
      $message = t('Something went wrong! Please, try now!');
      \Drupal::messenger()->addMessage($message, 'warning');
      return NULL;
    }
    // Get the current active cart.
    $cart = $this->cartProvider->getCart();
    if (!$cart) {
      // Create the Cart.
      $cart = $this->cartProvider->createCart();
    }
    // Add order item in the cart.
    $this->cartManager->addOrderItem($cart, $order_item, TRUE);
    /* @var \Drupal\node\NodeInterface $entity */
    $variation = $order_item->getPurchasedEntity();
    $product_link = $variation ? $variation->getProductLink() : NULL;
    if ($product_link) {
      $product_label = $product_link->getText();
      $product_url = $product_link->getUrl()->setAbsolute(TRUE)->toString();
      $message = t('Added <a href="@product_url">@product_label</a> to your cart - Excellent!', ['@product_label' => $product_label, '@product_url' => $product_url]);
      \Drupal::messenger()->addMessage($message);
    }
    // Other submit handlers might need the cart ID.
    $form_state->set('cart_id', $cart->id());
    $form_state->setRedirect('digital_store_cart.cart_page');
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
    /* @var \Drupal\node\NodeInterface $entity */
    $entity = $this->productVariationStorage->load($variation_id);
    if (!$entity) {
      return NULL;
    }
    $variation = new ProductVariation($entity);
    return $this->cartManager->createOrderItem($variation);
  }

  /**
   * Gets the variation options.
   *
   * @param \Drupal\digital_store_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return array
   *   The variations array in pairs ID/Label.
   */
  public function getVariationOptions(ProductInterface $product = NULL) {
    if (!$product) {
      return [];
    }
    $variations = $product->getVariations();
    $price_options = [];
    foreach ($variations as $delta => $entity) {
      $price_options[$entity->id()] = $this->getVariationDescription($entity);
    }
    return $price_options;
  }

  /**
   * Gets the product setting.
   *
   * @param \Drupal\digital_store_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return array
   *   The array with the product's price config.
   */
  public function getProductSetting(ProductInterface $product = NULL) {
    $default = [
      'title' => NULL,
      'description' => NULL,
      'quick_buy' => FALSE,
    ];
    if (!$product) {
      return $default;
    }
    /* @var \Drupal\digital_store_product\Plugin\Field\FieldType\PriceSettingItem $setting */
    $setting = $product->getProductSetting();
    $use_global = !$setting || ($setting && $setting->useGlobalConfig());
    if ($use_global) {
      // User Global Config.
      $config = \Drupal::config('digital_store_product.settings.prices');
      $default['title'] = $config->get('title');
      $default['description'] = $config->get('description');
      $default['quick_buy'] = $config->get('quick_buy');
    }
    else {
      $default['title'] = $setting->getTitle();
      $default['description'] = $setting->getDescription();
      $default['quick_buy'] = $setting->activeQuickBuy();
    }
    return $default;
  }

  /**
   * Gets the product summary.
   *
   * @param \Drupal\digital_store_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return array
   *   The Render array of the product attribute.
   */
  public function getProductSummary(ProductInterface $product = NULL) {
    if (!$product) {
      return [];
    }
    $summary = $product->getProductSummary();
    if (!$summary) {
      return [];
    }
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    return $view_builder->view($summary);
  }

  /**
   * Get price option description markup.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface $entity
   *   The price option entity.
   *
   * @return string|null
   *   The formatted option description.
   */
  public function getVariationDescription(ProductVariationInterface $entity = NULL) {
    if (!$entity) {
      return NULL;
    }
    return "<div class='name'>{$entity->label()}</div>{$entity->getPrice()}";
  }

}
