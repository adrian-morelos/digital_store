<?php

namespace Drupal\digital_store_order\Controller;

/**
 * Provides the Orders listing page.
 */
class OrdersController extends OrderListBuilder {

  /**
   * Outputs a order listing admin page.
   *
   * @return array
   *   A render array.
   */
  public function orderListingPage() {
    return $this->listingPage(FALSE, 'Orders', 'There are no new Orders.');
  }

}
