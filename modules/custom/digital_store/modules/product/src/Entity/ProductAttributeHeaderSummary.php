<?php

namespace Drupal\digital_store_product\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines a product attribute entity class.
 */
class ProductAttributeHeaderSummary extends DigitalStoreEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PRODUCT_ATTRIBUTE_HEADER_SUMMARY_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Sub-header.
    $fields['attribute_subheader'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subheader'))
      ->setDescription(t('The Subheader.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 128)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
        'settings' => [
          'size' => 60,
          'placeholder' => 'your awesome subheader',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Summary.
    $fields['attribute_summary'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Summary'))
      ->setDescription(t('Item Summary.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 128)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 3,
        'settings' => [
          'rows' => 5,
          'placeholder' => 'Your awesome Item Summary goes here!.',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}