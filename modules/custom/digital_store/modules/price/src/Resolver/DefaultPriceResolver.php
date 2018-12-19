<?php

namespace Drupal\digital_store_price\Resolver;

use Drupal\digital_store\Context;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Provides the default price, taking it directly from the purchasable entity.
 */
class DefaultPriceResolver implements PriceResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {
    $field_name = $context->getData('field_name', 'price');
    if ($field_name == 'price') {
      // Use the price getter to allow custom purchasable entity types to have
      // computed prices that are not backed by a field called "price".
      return $entity->getPrice();
    }
    elseif ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
      return $entity->get($field_name)->first()->toPrice();
    }
  }

}
