<?php

namespace Drupal\digital_store_payment\Exception;

/**
 * Base exception for declined transactions.
 *
 * A transaction can be declined due to an invalid payment method, fraud check,
 * expired authorization, etc.
 *
 * Good explanation for Braintree:
 * https://articles.braintreepayments.com/control-panel/transactions/declines
 *
 * @see \Drupal\digital_store_payment\Exception\HardDeclineException
 * @see \Drupal\digital_store_payment\Exception\SoftDeclineException
 */
class DeclineException extends PaymentGatewayException {}
