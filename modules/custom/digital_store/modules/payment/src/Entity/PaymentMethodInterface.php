<?php

namespace Drupal\digital_store_payment\Entity;

use Drupal\user\EntityOwnerInterface;

/**
 * Defines the interface for payment methods.
 */
interface PaymentMethodInterface extends EntityOwnerInterface {

  /**
   * Gets the payment method type.
   *
   * @return string
   *   The payment method type.
   */
  public function getType();

  /**
   * Gets the payment gateway mode.
   *
   * A payment gateway in "live" mode can't manipulate payment methods created
   * while it was in "test" mode, and vice-versa.
   *
   * @return string
   *   The payment gateway mode.
   */
  public function getPaymentGatewayMode();

  /**
   * Gets the payment method remote ID.
   *
   * @return string
   *   The payment method remote ID.
   */
  public function getRemoteId();

  /**
   * Sets the payment method remote ID.
   *
   * @param string $remote_id
   *   The payment method remote ID.
   *
   * @return $this
   */
  public function setRemoteId($remote_id);

  /**
   * Gets the billing profile.
   *
   * @return array
   *   The billing profile entity.
   */
  public function getBillingProfile();

  /**
   * Sets the billing profile.
   *
   * @param array
   *   The billing profile entity.
   *
   * @return $this
   */
  public function setBillingProfile(array $profile = []);

  /**
   * Gets whether the payment method is reusable.
   *
   * @return bool
   *   TRUE if the payment method is reusable, FALSE otherwise.
   */
  public function isReusable();

  /**
   * Sets whether the payment method is reusable.
   *
   * @param bool $reusable
   *   Whether the payment method is reusable.
   *
   * @return $this
   */
  public function setReusable($reusable);

  /**
   * Gets whether this is the user's default payment method.
   *
   * @return bool
   *   TRUE if this is the user's default payment method, FALSE otherwise.
   */
  public function isDefault();

  /**
   * Sets whether this is the user's default payment method.
   *
   * @param bool $default
   *   Whether this is the user's default payment method.
   *
   * @return $this
   */
  public function setDefault($default);

  /**
   * Gets whether the payment method has expired.
   *
   * @return bool
   *   TRUE if the payment method has expired, FALSE otherwise.
   */
  public function isExpired();

  /**
   * Gets the payment method expiration timestamp.
   *
   * @return int
   *   The payment method expiration timestamp.
   */
  public function getExpiresTime();

  /**
   * Sets the payment method expiration timestamp.
   *
   * @param int $timestamp
   *   The payment method expiration timestamp.
   *
   * @return $this
   */
  public function setExpiresTime($timestamp);

  /**
   * Gets the payment method creation timestamp.
   *
   * @return int
   *   Creation timestamp of the payment.
   */
  public function getCreatedTime();

  /**
   * Sets the payment method creation timestamp.
   *
   * @param int $timestamp
   *   The payment method creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the payment method card type.
   *
   * @return string
   *   card type of the payment.
   */
  public function getCardType();

  /**
   * Sets the payment method card type.
   *
   * @param string $card_type
   *   The payment method card type.
   *
   * @return $this
   */
  public function setCardType($card_type);

  /**
   * Gets the payment method card number.
   *
   * @return string
   *   card number of the payment.
   */
  public function getCardNumber();

  /**
   * Sets the payment method card number.
   *
   * @param string $card_number
   *   The payment method card number.
   *
   * @return $this
   */
  public function setCardNumber($card_number);

  /**
   * Gets the payment method card expiration month.
   *
   * @return string
   *   Card expiration month of the payment.
   */
  public function getCardExpirationMonth();

  /**
   * Sets the payment method card expiration month.
   *
   * @param string $card_exp_month
   *   The payment method card expiration month.
   *
   * @return $this
   */
  public function setCardExpirationMonth($card_exp_month);

  /**
   * Gets the payment method card expiration year.
   *
   * @return string
   *   Card expiration year of the payment.
   */
  public function getCardExpirationYear();

  /**
   * Sets the payment method card expiration year.
   *
   * @param string $card_exp_year
   *   The payment method card expiration year.
   *
   * @return $this
   */
  public function setCardExpirationYear($card_exp_year);

}
