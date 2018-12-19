<?php

namespace Drupal\digital_store_payment\Entity;

use Drupal\digital_store\Price;

/**
 * Defines the interface for payments.
 */
interface PaymentInterface {

  /**
   * Gets the payment type.
   *
   * @return string
   *   The payment type.
   */
  public function getType();

  /**
   * Gets the payment gateway mode.
   *
   * A payment gateway in "live" mode can't manipulate payments created while
   * it was in "test" mode, and vice-versa.
   *
   * @return string
   *   The payment gateway mode.
   */
  public function getPaymentGatewayMode();

  /**
   * Gets the payment method.
   *
   * @return \Drupal\digital_store_payment\Entity\PaymentMethodInterface|null
   *   The payment method entity, or null if unknown.
   */
  public function getPaymentMethod();

  /**
   * Gets the payment method ID.
   *
   * @return int|null
   *   The payment method ID, or null if unknown.
   */
  public function getPaymentMethodId();

  /**
   * Gets the parent order.
   *
   * @return \Drupal\digital_store_order\Entity\OrderInterface|null
   *   The order entity, or null.
   */
  public function getOrder();

  /**
   * Gets the parent order ID.
   *
   * @return int|null
   *   The order ID, or null.
   */
  public function getOrderId();

  /**
   * Gets the payment remote ID.
   *
   * @return string
   *   The payment remote ID.
   */
  public function getRemoteId();

  /**
   * Sets the payment remote ID.
   *
   * @param string $remote_ID
   *   The payment remote ID.
   *
   * @return $this
   */
  public function setRemoteId($remote_ID);

  /**
   * Gets the payment remote state.
   *
   * @return string
   *   The payment remote state.
   */
  public function getRemoteState();

  /**
   * Sets the payment remote state.
   *
   * @param string $remote_state
   *   The payment remote state.
   *
   * @return $this
   */
  public function setRemoteState($remote_state);

  /**
   * Gets the payment balance.
   *
   * The balance represents the payment amount minus the refunded amount.
   *
   * @return \Drupal\digital_store\Price|null
   *   The payment balance, or NULL if the payment does not have an amount yet.
   */
  public function getBalance();

  /**
   * Gets the payment amount.
   *
   * @return \Drupal\digital_store\Price|null
   *   The payment amount, or NULL.
   */
  public function getAmount();

  /**
   * Sets the payment amount.
   *
   * @param \Drupal\digital_store\Price $amount
   *   The payment amount.
   *
   * @return $this
   */
  public function setAmount(Price $amount);

  /**
   * Gets the refunded payment amount.
   *
   * @return \Drupal\digital_store\Price|null
   *   The refunded payment amount, or NULL.
   */
  public function getRefundedAmount();

  /**
   * Sets the refunded payment amount.
   *
   * @param \Drupal\digital_store\Price $refunded_amount
   *   The refunded payment amount.
   *
   * @return $this
   */
  public function setRefundedAmount(Price $refunded_amount);

  /**
   * Gets the payment state.
   *
   * @return string
   *   The payment state.
   */
  public function getState();

  /**
   * Sets the payment state.
   *
   * @param string $state_id
   *   The new state ID.
   *
   * @return $this
   */
  public function setState($state_id);

  /**
   * Gets the payment authorization timestamp.
   *
   * @return int
   *   The payment authorization timestamp.
   */
  public function getAuthorizedTime();

  /**
   * Sets the payment authorization timestamp.
   *
   * @param int $timestamp
   *   The payment authorization timestamp.
   *
   * @return $this
   */
  public function setAuthorizedTime($timestamp);

  /**
   * Gets whether the payment has expired.
   *
   * @return bool
   *   TRUE if the payment has expired, FALSE otherwise.
   */
  public function isExpired();

  /**
   * Gets the payment expiration timestamp.
   *
   * Marks the time after which the payment can no longer be completed.
   *
   * @return int
   *   The payment expiration timestamp.
   */
  public function getExpiresTime();

  /**
   * Sets the payment expiration timestamp.
   *
   * @param int $timestamp
   *   The payment expiration timestamp.
   *
   * @return $this
   */
  public function setExpiresTime($timestamp);

  /**
   * Gets whether the payment has been completed.
   *
   * @return bool
   *   TRUE if the payment has been completed, FALSE otherwise.
   */
  public function isCompleted();

  /**
   * Gets the payment completed timestamp.
   *
   * @return int
   *   The payment completed timestamp.
   */
  public function getCompletedTime();

  /**
   * Sets the payment completed timestamp.
   *
   * @param int $timestamp
   *   The payment completed timestamp.
   *
   * @return $this
   */
  public function setCompletedTime($timestamp);

}
