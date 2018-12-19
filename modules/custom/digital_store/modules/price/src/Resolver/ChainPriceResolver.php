<?php

namespace Drupal\digital_store_price\Resolver;

use Drupal\digital_store\Context;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Default implementation of the chain base price resolver.
 */
class ChainPriceResolver implements ChainPriceResolverInterface {

  /**
   * The resolvers.
   *
   * @var \Drupal\digital_store_price\Resolver\PriceResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Constructs a new ChainBasePriceResolver object.
   *
   * @param \Drupal\digital_store_price\Resolver\PriceResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    $this->resolvers = $resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function addResolver(PriceResolverInterface $resolver) {
    $this->resolvers[] = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolvers() {
    return $this->resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {
    foreach ($this->resolvers as $resolver) {
      $result = $resolver->resolve($entity, $quantity, $context);
      if ($result) {
        return $result;
      }
    }
  }

}
