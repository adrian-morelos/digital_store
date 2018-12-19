<?php

namespace Drupal\digital_store_product\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines a product attribute entity class.
 */
class ProductAttributeWebFlow extends DigitalStoreEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PRODUCT_ATTRIBUTE_WEB_FLOW_BUNDLE;
  }

  /**
   * Gets base field definitions.
   *
   * @return array
   *   The field definitions.
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Featured Images.
    $settings = [
      'file_directory' => '[date:custom:Y]-[date:custom:m]',
      'file_extensions' => 'png gif jpg jpeg svg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
      'title_field' => TRUE,
      'title_field_required' => FALSE,
      'default_image' => [
        'uuid' => '',
        'alt' => '',
        'title' => '',
        'width' => NULL,
        'height' => NULL,
      ],
      'handler' => 'default:file',
      'handler_settings' => []
    ];
    $fields['attribute_featured_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Featured Image'))
      ->setDescription(t('Please enter your featured image along with a description.'))
      ->setDefaultValue('')
      ->setSettings($settings)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 1,
        'settings' => [
          'image_style' => '',
          'image_link' => '',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 1,
        'settings' => [
          'progress_indicator' => 'throbber',
          'preview_image_style' => 'thumbnail',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}