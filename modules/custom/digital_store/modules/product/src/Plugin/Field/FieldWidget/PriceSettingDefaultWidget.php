<?php

namespace Drupal\digital_store_product\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'product_pricing_config_default' widget.
 *
 * @FieldWidget(
 *   id = "product_pricing_config_default",
 *   label = @Translation("Product's Pricing Config - Widget"),
 *   field_types = {
 *     "product_pricing_config"
 *   }
 * )
 */
class PriceSettingDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Default values.
    /* @var \Drupal\digital_store_product\Plugin\Field\FieldType\PriceSettingItem $setting */
    $setting = $items[$delta];
    $field_name = $setting->getFieldDefinition()->getName();
    $element['use_global_config'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use global product configuration?'),
      '#default_value' => $setting->useGlobalConfig(),
    ];
    $selector = ':input[name="' . $field_name . '[' . $delta . '][use_global_config]"]';
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Price Options title.'),
      '#required' => FALSE,
      '#default_value' => $setting->getTitle(),
      '#states' => [
        // Only show this field when the 'toggle_me' checkbox is not enabled.
        'visible' => [
          $selector => [
            'checked' => FALSE,
          ],
        ],
        'required' => [
          $selector => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];
    $element['quick_buy'] = [
      '#type' => 'radios',
      '#title' => $this->t('Do you want to activate Quick Buy in the product detail?'),
      '#options' => [
        TRUE => $this->t('Yes'),
        FALSE => $this->t('No'),
      ],
      '#default_value' => (int) $setting->activeQuickBuy(),
      '#states' => [
        // Only show this field when the 'toggle_me' checkbox is not enabled.
        'visible' => [
          $selector => [
            'checked' => FALSE,
          ],
        ],
        'required' => [
          $selector => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];
    $element['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Price Options description.'),
      '#default_value' => $setting->getDescription(),
      '#rows' => 4,
      '#states' => [
        // Only show this field when the 'toggle_me' checkbox is not enabled.
        'visible' => [
          $selector => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];

    // If the advanced settings tabs-set is available (normally rendered in the
    // second column on wide-resolutions), place the field as a details element
    // in this tab-set.
    if (isset($form['advanced'])) {
      $element += [
        '#type' => 'details',
        '#title' => t("Product's Pricing Config"),
        '#open' => !$setting->useGlobalConfig(),
        '#group' => 'advanced',
      ];
      $element['#weight'] = 29;
    }
    return $element;
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    ksm($element);
  }

}
