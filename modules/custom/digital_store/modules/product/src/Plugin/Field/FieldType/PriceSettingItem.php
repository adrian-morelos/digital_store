<?php

namespace Drupal\digital_store_product\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'product_pricing_config' field type.
 *
 * @FieldType(
 *   id = "product_pricing_config",
 *   label = @Translation("Product's pricing config"),
 *   description = @Translation("Stores the product's pricing config"),
 *   category = @Translation("Digital Store"),
 *   default_widget = "product_pricing_config_default",
 *   default_formatter = "product_pricing_config_default",
 * )
 */
class PriceSettingItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(FALSE);

    $properties['description'] = DataDefinition::create('string')
      ->setLabel(t('Price Options description.'))
      ->setRequired(FALSE);

    $properties['quick_buy'] = DataDefinition::create('integer')
      ->setLabel(t('Do you want to activate Quick Buy?.'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'title' => [
          'description' => 'Title',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
        'description' => [
          'description' => 'Price Options description',
          'type' => 'text',
          'size' => 'small',
          'not null' => FALSE,
        ],
        'quick_buy' => [
          'description' => 'Flag activate Quick Buy',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $quick_buy = $this->quick_buy ?? NULL;
    $title = $this->title ?? NULL;
    return (($quick_buy === NULL) && empty($title));
  }

  /**
   * Get if the field is using Global Config.
   *
   * @return bool
   *   The description.
   */
  public function useGlobalConfig() {
    return $this->isEmpty();
  }

  /**
   * Gets the Setting: Title.
   *
   * @return string
   *   The title.
   */
  public function getTitle() {
    return $this->title ?? NULL;
  }

  /**
   * Gets the Setting: Description.
   *
   * @return string
   *   The description.
   */
  public function getDescription() {
    return $this->description ?? NULL;
  }

  /**
   * Gets the Setting: Active Quick Buy.
   *
   * @return bool
   *   The description.
   */
  public function activeQuickBuy() {
    $quick_buy = $this->quick_buy ?? FALSE;
    return boolval($quick_buy);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (!isset($values)) {
      return;
    }
    if (isset($values['use_global_config']) && ($values['use_global_config'] == 1)) {
      $values['title'] = NULL;
      $values['quick_buy'] = NULL;
      $values['description'] = NULL;
    }
    parent::setValue($values, $notify);
  }

}
