<?php

namespace Drupal\digital_store_price\Resolver;

/**
 * Runs the added resolvers one by one until one of them returns the price.
 *
 * Each resolver in the chain can be another chain, which is why this interface
 * extends the base price resolver one.
 */
interface ChainPriceResolverInterface extends PriceResolverInterface {

  /**
   * Adds a resolver.
   *
   * @param \Drupal\digital_store_price\Resolver\PriceResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(PriceResolverInterface $resolver);

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\digital_store_price\Resolver\PriceResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers();

}
