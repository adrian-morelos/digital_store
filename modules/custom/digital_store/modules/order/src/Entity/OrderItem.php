<?php

namespace Drupal\digital_store_order\Entity;

use Drupal\digital_store\Price;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store_product\Entity\ProductVariation;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Defines the order item entity class.
 */
class OrderItem extends DigitalStoreEntityBase implements OrderItemInterface {

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTitle() {
    // Get variation type.
    $variation_type = NULL;
    $variation = $this->getPurchasedEntity();
    if ($variation) {
      $variation_type = $variation->label();
    }
    // Get variation product label an product url.
    $product_url = NULL;
    $product_label = NULL;
    $product = $variation->getProduct();
    if ($product) {
      $product_label = $product->label();
      $product_url = $product->url();
    }
    return [
      '#theme' => 'digital_store_order_item_label',
      '#product_url' => $product_url,
      '#product_label' => $product_label,
      '#variation_type' => $variation_type,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    $item = $this->get('order');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);

    return $referenced_entity ? new Order($referenced_entity) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderId() {
    $item = $this->get('order');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderId($id) {
    $this->set('order', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPurchasedEntity() {
    $item = $this->get('purchased_entity');
    if (empty($item)) {
      return FALSE;
    }
    return !$item->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getPurchasedEntity() {
    $item = $this->get('purchased_entity');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);
    return $referenced_entity ? new ProductVariation($referenced_entity) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getProduct() {
    $variation = $this->getPurchasedEntity();
    if (!$variation) {
      return NULL;
    }
    return $variation->getProduct();
  }

  /**
   * {@inheritdoc}
   */
  public function getPurchasedEntityId() {
    $item = $this->get('purchased_entity');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuantity() {
    return (string) $this->get('order_item_quantity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantity($quantity) {
    $this->set('order_item_quantity', (string) $quantity);
    $this->recalculateTotalPrice();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitPrice() {
    $item = $this->get('unit_price');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
    $price_item = $item->first();
    return $price_item->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function getFeaturedImage() {
    $product = $this->getProduct();
    if (!$product) {
      return NULL;
    }
    return $product->getFeaturedImage();
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitPrice(Price $unit_price, $override = FALSE) {
    $this->set('unit_price', $unit_price);
    $this->set('overridden_unit_price', $override);
    $this->recalculateTotalPrice();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isUnitPriceOverridden() {
    return $this->get('overridden_unit_price')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalPrice() {
    $item = $this->get('total_price');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
    $price_item = $item->first();
    return $price_item->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function getAdjustedTotalPrice(array $adjustment_types = []) {
    $total_price = $this->getTotalPrice();
    if (!$total_price) {
      return NULL;
    }
    $adjusted_total_price = $this->applyAdjustments($total_price, $adjustment_types);
    $rounder = \Drupal::service('digital_store.rounder');
    $adjusted_total_price = $rounder->round($adjusted_total_price);
    return $adjusted_total_price;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdjustedUnitPrice(array $adjustment_types = []) {
    $unit_price = $this->getUnitPrice();
    if (!$unit_price) {
      return NULL;
    }
    $adjusted_total_price = $this->getAdjustedTotalPrice($adjustment_types);
    $adjusted_unit_price = $adjusted_total_price->divide($this->getQuantity());
    $rounder = \Drupal::service('digital_store.rounder');
    $adjusted_unit_price = $rounder->round($adjusted_unit_price);
    return $adjusted_unit_price;
  }

  /**
   * Applies adjustments to the given price.
   *
   * @param \Drupal\digital_store\Price $price
   *   The price.
   * @param array $adjustment_types
   *   The adjustment types to include in the adjusted price.
   *   Examples: fee, promotion, tax. Defaults to all adjustment types.
   *
   * @return \Drupal\digital_store\Price
   *   The adjusted price.
   */
  protected function applyAdjustments(Price $price, array $adjustment_types = []) {
    $adjusted_price = $price;
    return $adjusted_price;
  }

  /**
   * Recalculates the order item total price.
   */
  protected function recalculateTotalPrice() {
    if ($unit_price = $this->getUnitPrice()) {
      $rounder = \Drupal::service('digital_store.rounder');
      $total_price = $unit_price->multiply($this->getQuantity());
      $total_price_rounder = $rounder->round($total_price);
      $this->set('total_price', $total_price_rounder);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromPurchasableEntity(PurchasableEntityInterface $entity, array $values = []) {
    $values += [
      'type' => 'order_item',
      'title' => $entity->getOrderItemTitle(),
      'purchased_entity' => $entity->id(),
      'unit_price' => $entity->getPrice(),
    ];
    return self::create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // Maintain Order item total price up-to-date.
    $this->recalculateTotalPrice();
    return $this->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_ORDER_ITEM_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Order.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'order' => 'order',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['order'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setLabel(t('The back-reference parent order.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 0,
        'settings' => [
          'link' => TRUE,
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Overridden unit price.
    $fields['overridden_unit_price'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Overridden unit price'))
      ->setDescription(t('Whether the unit price is overridden.'))
      ->setSettings([
        'on_label' => t('On'),
        'off_label' => t('Off'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'format' => 'default',
          'format_custom_false' => '',
          'format_custom_true' => '',
        ],
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 0,
        'settings' => [
          'link' => TRUE,
        ],
      ])
      ->setDefaultValue(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Purchased entity.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'price_options' => 'price_options',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['purchased_entity'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Purchased entity'))
      ->setLabel(t('The purchased entity.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 0,
        'settings' => [
          'link' => TRUE,
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Quantity.
    $fields['order_item_quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Quantity'))
      ->setDescription(t('The number of purchased units.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 0)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 0,
        'settings' => [
          'thousand_separator' => '',
          'prefix_suffix' => TRUE,
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Unit price.
    $available_currencies = [
      'USD' => 'USD',
    ];
    $fields['unit_price'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Unit price'))
      ->setDescription(t('The price of a single unit.'))
      ->setSetting('available_currencies', $available_currencies)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'price_default',
        'weight' => 0,
        'settings' => [
          'available_currencies ' => $available_currencies
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Total price.
    $available_currencies = [
      'USD' => 'USD',
    ];
    $fields['total_price'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Total price'))
      ->setDescription(t('The total price of the order item.'))
      ->setReadOnly(TRUE)
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'price_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}