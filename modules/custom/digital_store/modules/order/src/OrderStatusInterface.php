<?php

namespace Drupal\digital_store_order;

/**
 * Digital Store - Order Status.
 */
interface OrderStatusInterface {

  /**
   * Order in the cart.
   *
   *  The order is in the Draft state when a customer is adding products
   *  to the shopping cart or when the order is reset.
   */
  const DRAFT = 'draft';

  /**
   * Order status Pending payment.
   *
   *  Pending payment – Order received (unpaid)
   */
  const PENDING_PAYMENT = 'pending_payment';

  /**
   * Order status Failed.
   *
   *  Payment failed or was declined (unpaid). Note that this status may not
   *  show immediately and instead show as Pending until
   *  verified (i.e., PayPal).
   */
  const FAILED = 'failed';

  /**
   * Order status Processing.
   *
   *  Payment received – the order is awaiting fulfillment.
   */
  const PROCESSING = 'processing';

  /**
   * Order status Completed.
   *
   *  Order fulfilled and complete – requires no further action.
   */
  const COMPLETED = 'completed';

  /**
   * Order status On Hold.
   *
   *  Awaiting payment – stock is reduced, but you need to confirm payment
   */
  const ON_HOLD = 'on_hold';

  /**
   * Order status Cancelled.
   *
   *  Cancelled by an admin or the customer – stock is increased,
   *  no further action required
   */
  const CANCELLED = 'cancelled';

  /**
   * Order status Refunded.
   *
   *  Refunded by an admin – no further action required
   */
  const REFUNDED = 'refunded';

}
