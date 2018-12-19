<?php

namespace Drupal\digital_store_product\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store_product\ProductType;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines the product entity class.
 */
class Product extends DigitalStoreEntityBase implements ProductInterface {
  
  /**
   * {@inheritdoc}
   */
  public function getVariationIds() {
    if (!$this->hasField('variations')) {
      return [];
    }
    $variation_ids = [];
    foreach ($this->get('variations') as $field_item) {
      $variation_ids[] = $field_item->target_id;
    }
    return $variation_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariations() {
    $item = $this->get('variations');
    if (empty($item)) {
      return [];
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return [];
    }
    $items = [];
    foreach ($referenced_entities as $key => $entity) {
      $items[] = new ProductVariation($entity);
    }
    return $items;
  }

  /**
   * Gets the product summary.
   *
   * @return \Drupal\node\NodeInterface
   *   The variations.
   */
  public function getProductSummary() {
    $item = $this->get('field_product_attributes');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);
    return $referenced_entity ? $referenced_entity : NULL;
  }

  /**
   * Gets the product's pricing setting.
   *
   * @return \Drupal\digital_store_product\Plugin\Field\FieldType\PriceSettingItem|null
   *   The product's pricing setting.
   */
  public function getProductSetting() {
    $item = $this->get('field_product_pricing_config');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    $price_setting = $item->first();
    /* @var \Drupal\digital_store_product\Plugin\Field\FieldType\PriceSettingItem $price_setting */
    return $price_setting;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariations(array $variations) {
    $this->set('variations', $variations);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariations() {
    $item = $this->get('variations');
    if (empty($item)) {
      return FALSE;
    }
    return !$item->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addVariation(ProductVariationInterface $variation) {
    if (!$this->hasVariation($variation)) {
      $this->get('variations')->appendItem($variation);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariation(ProductVariationInterface $variation) {
    $index = $this->getVariationIndex($variation);
    if ($index !== FALSE) {
      $this->get('variations')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariation(ProductVariationInterface $variation) {
    return in_array($variation->id(), $this->getVariationIds());
  }

  /**
   * Gets the index of the given variation.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return int|bool
   *   The index of the given variation, or FALSE if not found.
   */
  protected function getVariationIndex(ProductVariationInterface $variation) {
    return array_search($variation->id(), $this->getVariationIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultVariation() {
    foreach ($this->getVariations() as $variation) {
      // Return the first active variation.
      if ($variation->isActive()) {
        return $variation;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFeaturedImage() {
    $item = $this->get('field_featured_image');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    /* @var \Drupal\file\Entity\File $file */
    $file = reset($referenced_entities);
    return [
      '#theme' => 'image_style',
      '#width' => 75,
      '#height' => 100,
      '#style_name' => 'product_thumbnail',
      '#uri' => $file->getFileUri(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postSave() {
    // Ensure there's a back-reference on each product variation.
    foreach ($this->getVariations() as $variation) {
      if (empty($variation->getProductId())) {
        $variation->setProductId($this->id());
        $variation->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete() {
    // Delete the order items of a deleted order.
    $variations = [];
    foreach ($this->getVariations() as $variation) {
      $variations[$variation->id()] = $variation->getEntity();
    }
    /** @var \Drupal\node\NodeStorageInterface $order_item_storage */
    $order_item_storage = \Drupal::service('entity_type.manager')
      ->getStorage('node');
    $order_item_storage->delete($variations);
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PRODUCT_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Variations.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'product_variation' => 'product_variation',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['variations'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Variations')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => 'variation',
          'label_plural' => 'variations',
          'allow_new' => TRUE,
          'match_operator' => 'CONTAINS',
          'allow_existing' => 'false',
          'form_mode' => 'default',
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'digital_store_add_to_cart',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Product type.
    $fields['product_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setDescription(t('The Product Type.'))
      ->setSetting('allowed_values', ProductType::getTypeLabels())
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 3,
        'settings' => [
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Product Attributes.
    $handler_settings = [
      'product_attribute' => [
        'is_product_attribute' => 1,
      ],
      'target_bundles' => [
        'attribute_web_flow' => 'attribute_web_flow',
        'attribute_header_summary' => 'attribute_header_summary',
      ],
    ];
    $fields['field_product_attributes'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Product Attributes')
      ->setRequired(FALSE)
      ->setSetting('handler', 'product_attribute_selection')
      ->setSetting('handler_settings', $handler_settings)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 2,
        'settings' => [
          'form_mode' => 'default',
          'override_labels' => TRUE,
          'label_singular' => 'attribute',
          'label_plural' => 'attributes',
          'collapsible' => TRUE,
          'collapsed' => TRUE,
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
          'match_operator' => 'CONTAINS',
          'allow_duplicate' => FALSE,
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_entity_view',
        'weight' => 1,
        'label' => 'hidden',
        'settings' => [
          'view_mode' => 'default',
          'link' => FALSE,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}