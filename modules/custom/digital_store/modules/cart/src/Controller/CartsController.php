<?php

namespace Drupal\digital_store_cart\Controller;

use Drupal\digital_store_order\Controller\OrderListBuilder;

/**
 * Provides the Carts listing page.
 */
class CartsController extends OrderListBuilder {

  /**
   * Outputs a cart listing admin page.
   *
   * @return array
   *   A render array.
   */
  public function cartListingPage() {
    return $this->listingPage(TRUE, 'Carts', 'There are no new orders in the cart.');
  }

}
