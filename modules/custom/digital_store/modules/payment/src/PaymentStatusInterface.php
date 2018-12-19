<?php

namespace Drupal\digital_store_payment;

/**
 * Digital Store Payment Status - Payment workflow states.
 */
interface PaymentStatusInterface {

  /**
   * Payment Status - New.
   */
  const NEW = 'new';

  /**
   * Payment Status - Authorization.
   */
  const AUTHORIZATION = 'authorization';

  /**
   * Payment Status - Partially Refunded.
   */
  const PARTIALLY_REFUNDED = 'partially_refunded';

  /**
   * Payment Status - Refunded.
   */
  const REFUNDED = 'refunded';

  /**
   * Payment Status - Completed.
   */
  const COMPLETED = 'completed';

  /**
   * Payment Status - Authorization Voided.
   */
  const AUTHORIZATION_VOIDED = 'authorization_voided';

}
