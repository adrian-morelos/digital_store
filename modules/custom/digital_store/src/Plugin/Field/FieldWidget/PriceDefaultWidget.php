<?php

namespace Drupal\digital_store\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'price_default' widget.
 *
 * @FieldWidget(
 *   id = "price_default",
 *   label = @Translation("Price"),
 *   field_types = {
 *     "price"
 *   }
 * )
 */
class PriceDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'price';
    if (!$items[$delta]->isEmpty()) {
      $element['#default_value'] = $items[$delta]->toPrice()->toArray();
    }
    $element['#available_currencies'] = array_filter($this->getFieldSetting('available_currencies'));

    return $element;
  }

}
