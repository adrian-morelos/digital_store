<?php

namespace Drupal\digital_store_product\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'product_pricing_config_default' formatter.
 *
 * @FieldFormatter(
 *   id = "product_pricing_config_default",
 *   label = @Translation("Product's Pricing Config - Formatter"),
 *   field_types = {
 *     "product_pricing_config"
 *   }
 * )
 */
class PriceSettingDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return [];
  }

}
