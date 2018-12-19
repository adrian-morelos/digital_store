<?php

namespace Drupal\digital_store_price\Resolver;

use Drupal\digital_store\Context;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Defines the interface for price resolvers.
 */
interface PriceResolverInterface {

  /**
   * Resolves a price for the given purchasable entity.
   *
   * Use $context->getData('field_name', 'price') to get the name of the field
   * for which the price is being resolved (e.g "list_price", "price").
   *
   * @param \Drupal\digital_store\Entity\PurchasableEntityInterface $entity
   *   The purchasable entity.
   * @param int $quantity
   *   The quantity.
   * @param \Drupal\digital_store\Context $context
   *   The context.
   *
   * @return \Drupal\digital_store\Price|null
   *   A price value object, if resolved. Otherwise NULL, indicating that the
   *   next resolver in the chain should be called.
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context);

}
