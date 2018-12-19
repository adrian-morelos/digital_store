<?php

namespace Drupal\digital_store\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'order_item_table' formatter.
 *
 * @FieldFormatter(
 *   id = "order_item_table",
 *   label = @Translation("Order item table"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class OrderItemTable extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\digital_store_order\Entity\OrderInterface $order */
    $order = $items->getEntity();
    if (!$order) {
      return [];
    }
    $items = $order->getItems();
    if (empty($items)) {
      return [];
    }
    $elements = [];
    // Order Items.
    $elements['order_items'] = [
      '#type' => 'table',
      '#empty' => t('Your cart is currently empty.'),
      '#header' => [
        t('Product'),
        t('Total'),
      ],
      '#attributes' => [
        'class' => ['order-items'],
      ],
    ];
    foreach ($items as $delta => $order_item) {
      $order_item_id = $order_item->id();
      $elements['order_items'][$order_item_id] = [
        'product' => [
          '#markup' => $order_item->label() . '<strong> x ' . $order_item->getQuantity() . '</strong> ',
          '#title' => t('Name'),
          '#title_display' => 'invisible',
        ],
        'total' => [
          '#markup' => $order_item->getTotalPrice(),
          '#title' => t('Total'),
          '#title_display' => 'invisible',
        ],
      ];
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return (($entity_type == 'node') && ($field_name == 'order_items'));
  }

}
