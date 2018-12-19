<?php

namespace Drupal\digital_store;

/**
 * Rounds prices.
 */
interface RounderInterface {

  /**
   * Rounds the given price to its currency precision.
   *
   * For example, USD prices will be rounded to 2 decimals.
   *
   * @param \Drupal\digital_store\Price $price
   *   The price.
   * @param int $mode
   *   The rounding mode. One of the following constants: PHP_ROUND_HALF_UP,
   *   PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD.
   *
   * @return \Drupal\digital_store\Price
   *   The rounded price.
   *
   * @throws \InvalidArgumentException
   *   When given a price with an unknown currency.
   */
  public function round(Price $price, $mode = PHP_ROUND_HALF_UP);

}
