<?php

namespace Drupal\digital_store_product\Entity;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\digital_store\Price;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines the product variation entity class.
 */
class ProductVariation extends DigitalStoreEntityBase implements ProductVariationInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    $item = $this->get('variation_label');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getProduct() {
    $item = $this->get('product_id');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);
    return $referenced_entity ? new Product($referenced_entity) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductLink() {
    $product = $this->getProduct();
    if (!$product) {
      return NULL;
    }
    $entity = $product->getEntity();
    if (!$entity) {
      return NULL;
    }
    try {
      return $entity->toLink($product->label(), 'canonical');
    }
    catch (EntityMalformedException $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProductId() {
    $item = $this->get('product_id');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setProductId($id) {
    $this->set('product_id', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSku() {
    $item = $this->get('sku');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSku($sku) {
    $this->set('sku', $sku);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('price', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    if (!$this->entity) {
      return FALSE;
    }
    return $this->entity->isPublished();
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', (bool) $active);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    $item = $this->get('price');
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
  public function getOrderItemTypeId() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTitle() {
    $names = [];
    $product = $this->getProduct();
    if ($product) {
      $names[] = $product->label();
    }
    $names[] = $this->label();
    return implode(' - ', $names);
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PRODUCT_VARIATION_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Label.
    $fields['variation_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The price Label.'))
      ->setRequired(TRUE)
      ->setSetting('display_description', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
        'settings' => [
          'size' => 60,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: The product back-reference, populated by Product::postSave().
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'product' => 'product',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['product_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product'))
      ->setDescription(t('The parent product.'))
      ->setSetting('target_type', 'commerce_product')
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setDisplayConfigurable('view', TRUE);
    // Field: SKU.
    $fields['sku'] = BaseFieldDefinition::create('string')
      ->setLabel(t('SKU'))
      ->setDescription(t('The unique, machine-readable identifier for a variation.'))
      ->setRequired(TRUE)
      ->addConstraint('ProductVariationSku')
      ->setSetting('display_description', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Price.
    $available_currencies = [
      'USD' => 'USD',
    ];
    $fields['price'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Price'))
      ->setDescription(t('The price'))
      ->setRequired(TRUE)
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'price_default',
        'weight' => 3,
        'settings' => [
          'available_currencies ' => $available_currencies
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Bundle.
    $fields['variation_bundle'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Bundle'))
      ->setDescription(t('Product quantity.'))
      ->setRequired(TRUE)
      ->setSetting('min', 1)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 3,
        'settings' => [
          'placeholder ' => ''
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);
    // Return The Fields.
    return $fields;
  }

}
