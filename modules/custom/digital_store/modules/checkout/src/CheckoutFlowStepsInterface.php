<?php

namespace Drupal\digital_store_checkout;

/**
 * Digital Store - Checkout Flow Steps.
 */
interface CheckoutFlowStepsInterface {

  /**
   * Order in Shopping Cart.
   *
   *  The order is in the Draft state when a customer is adding products
   *  to the shopping cart or when the order is reset.
   */
  const SHOPPING_CART = 'shopping.cart';

  /**
   * Order in Payment Information.
   *
   *  Payment Information – the order is Payment Information step.
   */
  const PAYMENT_INFORMATION = 'payment';

  /**
   * Order received.
   *
   *  Completed – the order was placed.
   */
  const COMPLETED = 'order-received';

}
