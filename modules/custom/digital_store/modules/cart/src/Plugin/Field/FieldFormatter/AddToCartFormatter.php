<?php

namespace Drupal\digital_store_cart\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'digital_store_add_to_cart' formatter.
 *
 * @FieldFormatter(
 *   id = "digital_store_add_to_cart",
 *   label = @Translation("Add to Cart form"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class AddToCartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'combine' => TRUE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['combine'] = [
      '#type' => 'checkbox',
      '#title' => t('Combine order items containing the same product variation.'),
      '#description' => t('The order item, referenced product variation, and data from fields exposed on the Add to Cart form must all match to combine.'),
      '#default_value' => $this->getSetting('combine'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('combine')) {
      $summary[] = $this->t('Combine order items containing the same product variation.');
    }
    else {
      $summary[] = $this->t('Do not combine order items containing the same product variation.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $elements[0]['add_to_cart_form'] = [
      '#lazy_builder' => [
        'digital_store_cart.lazy_builders:addToCartForm',
        [
          $items->getEntity()->id(),
          $this->viewMode,
          $this->getSetting('combine'),
          $langcode,
        ],
      ],
      '#create_placeholder' => TRUE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return ($entity_type == 'node') && ($field_name == 'variations');
  }

}
