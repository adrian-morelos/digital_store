<?php

namespace Drupal\digital_store_cart\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a cart menu block.
 *
 * @Block(
 *   id = "digital_store_cart",
 *   admin_label = @Translation("Cart Menu"),
 *   category = @Translation("Digital Store")
 * )
 */
class CartMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new CartBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartProviderInterface $cart_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('digital_store_cart.cart_provider')
    );
  }

  /**
   * Builds the cart block.
   *
   * @return array
   *   A render array.
   */
  public function build() {
    $cachable_metadata = new CacheableMetadata();
    $cachable_metadata->addCacheContexts(['user', 'session']);

    /** @var \Drupal\digital_store_order\Entity\OrderInterface[] $carts */
    $carts = $this->cartProvider->getCarts();
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\digital_store_order\Entity\OrderInterface $cart */
      // There is a chance the cart may have converted from a draft order, but
      // is still in session. Such as just completing check out. So we verify
      // that the order is still a cart.
      return ($cart->hasItems() && $cart->orderIsOnCart());
    });

    $count = 0;
    if (!empty($carts)) {
      foreach ($carts as $cart_id => $cart) {
        foreach ($cart->getItems() as $order_item) {
          $count += (int) $order_item->getQuantity();
        }
        $cachable_metadata->addCacheableDependency($cart);
      }
    }

    $links = [];
    $links[] = [
      '#type' => 'link',
      '#title' => $this->t('Cart'),
      '#url' => Url::fromRoute('digital_store_cart.cart_page'),
    ];

    return [
      '#theme' => 'digital_store_cart_block',
      '#icon' => [
        '#theme' => 'image',
        '#uri' => drupal_get_path('module', 'digital_store_cart') . '/icons/cart.png',
        '#alt' => $this->t('Shopping cart'),
      ],
      '#count' => $count,
      '#count_text' => $this->formatPlural($count, '@count Item', '@count Items'),
      '#url' => Url::fromRoute('digital_store_cart.cart_page')->toString(),
      '#links' => $links,
      '#cache' => [
        'contexts' => ['cart'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['cart']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cart_cache_tags = [];

    /** @var \Drupal\digital_store_order\Entity\OrderInterface[] $carts */
    $carts = $this->cartProvider->getCarts();
    foreach ($carts as $cart) {
      // Add tags for all carts regardless items or cart flag.
      $cache_tags = $cart->getEntity()->getCacheTags();
      $cart_cache_tags = Cache::mergeTags($cart_cache_tags, $cache_tags);
    }
    return Cache::mergeTags($cache_tags, $cart_cache_tags);
  }

}
