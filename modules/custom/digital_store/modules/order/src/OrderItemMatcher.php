<?php

namespace Drupal\digital_store_order;

use Drupal\digital_store_order\Entity\OrderItemInterface;

/**
 * Default implementation of the order item matcher.
 */
class OrderItemMatcher implements OrderItemMatcherInterface {

  /**
   * {@inheritdoc}
   */
  public function match(OrderItemInterface $order_item, array $order_items = []) {
    $order_items = $this->matchAll($order_item, $order_items);
    return count($order_items) ? $order_items[0] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function matchAll(OrderItemInterface $order_item, array $order_items) {
    $purchased_entity = $order_item->getPurchasedEntity();
    if (empty($purchased_entity)) {
      // Don't support combining order items without a purchased entity.
      return [];
    }
    $comparison_fields = ['purchased_entity'];
    $matched_order_items = [];
    /** @var \Drupal\digital_store_order\Entity\OrderItemInterface $existing_order_item */
    foreach ($order_items as $existing_order_item) {
      foreach ($comparison_fields as $field_name) {
        if (!$existing_order_item->hasField($field_name) || !$order_item->hasField($field_name)) {
          // The field is missing on one of the order items.
          continue 2;
        }
        $existing_order_item_field = $existing_order_item->get($field_name);
        $order_item_field = $order_item->get($field_name);
        // Two empty fields should be considered identical, but an empty item
        // can affect the comparison and cause a false mismatch.
        $existing_order_item_field = $existing_order_item_field->filterEmptyItems();
        $order_item_field = $order_item_field->filterEmptyItems();
        if (!$existing_order_item_field->equals($order_item_field)) {
          // Order item doesn't match.
          continue 2;
        }
      }
      $matched_order_items[] = $existing_order_item;
    }

    return $matched_order_items;
  }

}
