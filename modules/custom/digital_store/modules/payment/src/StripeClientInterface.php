<?php

namespace Drupal\digital_store_payment;

/**
 * Provides the interface for the Stripe payment gateway.
 */
interface StripeClientInterface {

  /**
   * Get the Stripe API Publishable key set for the payment gateway.
   *
   * @return string
   *   The Stripe API publishable key.
   */
  public function getPublishableKey();

  /**
   * Check if the payment should be captured.
   *
   * @return bool
   *   Returns TRUE if Authorize and capture, otherwise FALSE.
   */
  public function isCapture();

  /**
   * Get the Stripe secret key set for the payment gateway.
   *
   * @return string
   *   The Stripe API secret key.
   */
  public function getSecretKey();

  /**
   * Create User on Stripe.
   *
   * @param array $fields
   *   The customer field.
   *
   * @return \stdClass|bool
   *   The Customer object representation, otherwise FALSE.
   */
  public function createCustomer(array $fields = []);

  /**
   * Create Charge on Stripe.
   *
   * @param array $fields
   *   The customer field.
   *
   * @return \stdClass|bool
   *   Returns the charge object if the charge succeeded. otherwise FALSE.
   */
  public function createCharge(array $fields = []);

  /**
   * Capture Charge on Stripe.
   *
   * @param string $remote_id
   *   The identifier of the charge to be retrieved.
   * @param array $fields
   *   The customer field.
   *
   * @return \stdClass|bool
   *   Returns the charge object, with an updated captured property
   *   (set to true), otherwise FALSE.
   */
  public function captureCharge($remote_id = NULL, array $fields = []);

  /**
   * Retrieve a charge from Stripe.
   *
   * @param string $remote_id
   *   The identifier of the charge to be retrieved.
   *
   * @return \stdClass|NULL
   *   The Charge object representation, otherwise NULL.
   */
  public function retrieveCharge($remote_id = NULL);

  /**
   * Retrieve User from Stripe.
   *
   * @param string $customer_id
   *   The customer ID.
   *
   * @return \stdClass|NULL
   *   The Customer object representation, otherwise NULL.
   */
  public function retrieveCustomer($customer_id = NULL);

  /**
   * Get Customers Cards.
   *
   * @param \stdClass $customer
   *   The customer object representation.
   *
   * @return \stdClass[]
   *   The array the Customer's Cards object representation, otherwise NULL.
   */
  public function getCustomersCards(\stdClass $customer = NULL);

  /**
   * Retrieve a Stripe token.
   *
   * @param string $token_id
   *   The ID of the desired token.
   *
   * @return \stdClass|NULL
   *   The token object representation, otherwise NULL.
   */
  public function retrieveToken($token_id = NULL);

  /**
   * Create New Card.
   *
   * @param string $customer_id
   *   The customer ID.
   * @param string $source
   *   Token returned by Stripe.js.
   *
   * @return \stdClass
   *   The the Customer's Card object representation, otherwise NULL.
   */
  public function createNewCard($customer_id = NULL, $source = NULL);

  /**
   * Create a refund on Stripe.
   *
   * @param array $fields
   *   The refund fields.
   *
   * @return \stdClass|bool
   *   Returns the Refund object if the refund succeeded, otherwise FALSE.
   */
  public function createRefund(array $fields = []);

  /**
   * Detaches a Stripe Source object from a Customer.
   *
   * @param string $customer_id
   *   The customer ID.
   * @param string $remote_id
   *   The identifier of the source to be detached.
   *
   * @return \stdClass
   *   Returns the detached Source object, otherwise NULL.
   */
  public function detachSource($customer_id = NULL, $remote_id = NULL);

}
