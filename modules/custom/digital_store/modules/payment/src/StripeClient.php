<?php

namespace Drupal\digital_store_payment;

use Drupal\digital_store\Exception\InvalidRequestException;
use Drupal\digital_store\Exception\HardDeclineException;
use Drupal\Core\Config\ConfigFactoryInterface;
use \GuzzleHttp\Exception\ClientException;
use \Psr\Http\Message\ResponseInterface;

/**
 * Provides the Stripe Client payment gateway - Communicates with Stripe API.
 */
class StripeClient implements StripeClientInterface {

  /**
   * The Stripe API version.
   *
   * @var string
   */
  const STRIPE_API_VERSION = '2018-09-24';

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  private $httpClient = NULL;

  /**
   * The config name.
   *
   * @var string
   */
  protected $configName = 'digital_store.settings.stripe';

  /**
   * The configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Constructs a new Stripe object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The order item matcher.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configuration = $config_factory->get($this->configName);
  }

  /**
   * {@inheritdoc}
   */
  public function getPublishableKey() {
    if (!$this->configuration) {
      return NULL;
    }
    return $this->configuration->get('publishable_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getSecretKey() {
    if (!$this->configuration) {
      return NULL;
    }
    return $this->configuration->get('secret_key');
  }

  /**
   * {@inheritdoc}
   */
  public function isCapture() {
    if (!$this->configuration) {
      return FALSE;
    }
    $capture = $this->configuration->get('capture');
    return boolval($capture);
  }

  /**
   * Get Stripe API Endpoint.
   *
   * @return string
   *   The Stripe API Endpoint.
   */
  public function getStripeApiEndpoint() {
    if (!$this->configuration) {
      return NULL;
    }
    return $this->configuration->get('stripe_api_endpoint');
  }

  /**
   * Returns the default http client.
   *
   * @return \GuzzleHttp\Client
   *   A guzzle http client instance.
   */
  public function getHttpClient() {
    if (is_null($this->httpClient)) {
      $this->httpClient = \Drupal::httpClient();
    }
    return $this->httpClient;
  }

  /**
   * Execute a GET request against the API.
   *
   * @param string $requestUri
   *   The request uri.
   * @param array $options
   *   The request options.
   *
   * @return \Psr\Http\Message\ResponseInterface|string
   *   A guzzle response.
   */
  public function get($requestUri, array $options = []) {
    $endpoint = $this->getStripeApiEndpoint() . '/' . $requestUri;
    try {
      $res = $this->getHttpClient()->request('GET', $endpoint, $options);
    } catch (\Exception $ex) {
      $res = $ex->getMessage();
    }
    return $res;
  }

  /**
   * Execute a PUT request against the API.
   *
   * @param string $requestUri
   *   The request uri.
   * @param array $options
   *   The options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   A guzzle response.
   */
  public function put($requestUri, array $options = []) {
    $endpoint = $this->getStripeApiEndpoint() . '/' . $requestUri;
    return $res = $this->getHttpClient()->request('PUT', $endpoint, $options);
  }

  /**
   * Execute a POST request against the API.
   *
   * @param string $requestUri
   *   The request uri.
   * @param array $options
   *   The options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   A guzzle response.
   */
  public function post($requestUri, array $options = []) {
    $endpoint = $this->getStripeApiEndpoint() . '/' . $requestUri;
    return $res = $this->getHttpClient()->request('POST', $endpoint, $options);
  }

  /**
   * Execute a DELETE request against the API.
   *
   * @param string $requestUri
   *   The request uri.
   * @param array $options
   *   The options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   A guzzle response.
   */
  public function delete($requestUri, array $options = []) {
    $endpoint = $this->getStripeApiEndpoint() . '/' . $requestUri;
    return $res = $this->getHttpClient()
      ->request('DELETE', $endpoint, $options);
  }

  /**
   * Generates the user agent we use to pass to API request so
   * Stripe can identify our application.
   *
   * @since 4.0.0
   * @version 4.0.0
   */
  public function getUserAgent() {
    $app_info = array(
      'name' => 'Digital Store Stripe Gateway',
      'version' => '1.0.0',
      'url' => 'https://wpuseful.com/',
    );
    return [
      'lang' => 'php',
      'lang_version' => phpversion(),
      'publisher' => 'wpuseful',
      'uname' => php_uname(),
      'application' => $app_info,
    ];
  }

  /**
   * Generates the headers to pass to API request.
   *
   * @since 4.0.0
   * @version 4.0.0
   */
  public function getHeaders() {
    $user_agent = $this->getUserAgent();
    $app_info = $user_agent['application'];
    return [
      'Authorization' => 'Basic ' . base64_encode($this->getSecretKey() . ':'),
      'Stripe-Version' => self::STRIPE_API_VERSION,
      'User-Agent' => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
      'X-Stripe-Client-User-Agent' => json_encode($user_agent),
      'Content-Type' => 'application/x-www-form-urlencoded',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createCustomer(array $fields = []) {
    $endpoint = 'customers';
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => $fields,
    ];
    try {
      $response = $this->post($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createCharge(array $fields = []) {
    $endpoint = 'charges';
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => $fields,
    ];
    try {
      $response = $this->post($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createRefund(array $fields = []) {
    $endpoint = 'refunds';
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => $fields,
    ];
    try {
      $response = $this->post($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function captureCharge($remote_id = NULL, array $fields = []) {
    if (empty($remote_id)) {
      return NULL;
    }
    if (empty($fields)) {
      return NULL;
    }
    $endpoint = "charges/{$remote_id}/capture";
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => $fields,
    ];
    try {
      $response = $this->post($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveCharge($remote_id = NULL) {
    if (empty($remote_id)) {
      return NULL;
    }
    $endpoint = "charges/{$remote_id}";
    $options = [
      'headers' => $this->getHeaders(),
    ];
    try {
      $response = $this->get($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createNewCard($customer_id = NULL, $source = NULL) {
    if (empty($customer_id) || empty($source)) {
      return NULL;
    }
    $endpoint = "customers/{$customer_id}/sources";
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => [
        'source' => $source,
      ],
    ];
    try {
      $response = $this->post($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function detachSource($customer_id = NULL, $remote_id = NULL) {
    if (empty($customer_id) || empty($remote_id)) {
      return NULL;
    }
    $endpoint = "customers/{$customer_id}/sources/{$remote_id}";
    $options = [
      'headers' => $this->getHeaders(),
    ];
    try {
      $response = $this->delete($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }


  /**
   * {@inheritdoc}
   */
  public function retrieveCustomer($customer_id = NULL) {
    if (empty($customer_id)) {
      return NULL;
    }
    $endpoint = "customers/{$customer_id}";
    $options = [
      'headers' => $this->getHeaders(),
    ];
    try {
      $response = $this->get($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveToken($token_id = NULL) {
    if (empty($token_id)) {
      return NULL;
    }
    $endpoint = "tokens/{$token_id}";
    $options = [
      'headers' => $this->getHeaders(),
    ];
    try {
      $response = $this->get($endpoint, $options);
      return $this->decodeResponse($response);
    } catch (ClientException $e) {
      $this->handleException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomersCards(\stdClass $customer = NULL) {
    if (!$customer) {
      return [];
    }
    if (!isset($customer->sources->data)) {
      return [];
    }
    $data = $customer->sources->data;
    $cards = [];
    foreach ($data as $delta => $item) {
      $is_card = isset($item->object) && ($item->object == 'card');
      if ($is_card) {
        $cards[] = $item;
      }
    }
    return $cards;
  }

  /**
   * Decode Response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The request object.
   *
   * @return @return \stdClass|null
   *   the Stripe Object, otherwise NULL.
   */
  public function decodeResponse(ResponseInterface $response = NULL) {
    if (!$response) {
      return NULL;
    }
    $response_content = (string) $response->getBody();
    return json_decode($response_content);
  }

  /**
   * Translates Stripe exceptions into Commerce exceptions.
   *
   * @param \\GuzzleHttp\Exception\ClientException $exception
   *   The Stripe exception.
   *
   * @throws \Drupal\digital_store\Exception\PaymentGatewayException
   *   The Commerce exception.
   */
  public static function handleException(ClientException $exception = NULL) {
    if (empty($exception)) {
      return NULL;
    }
    $response = $exception->getResponse();
    $response_content = (string) $response->getBody();
    $info = json_decode($response_content, TRUE);
    $error = $info['error'] ?? NULL;
    $message = $error['message'] ?? NULL;
    $code = $error['code'] ?? NULL;
    $log = "$code - $message";
    \Drupal::logger('digital_store')->warning($log);
    // throw new PaymentGatewayException($message, $code);
  }

  /**
   * Translates Stripe errors into Digital Store exceptions.
   *
   * @todo
   *   Make sure if this is really needed or handleException cover all
   *   possible errors.
   *
   * @param object $result
   *   The Stripe result object.
   *
   * @throws \Drupal\digital_store\Exception\PaymentGatewayException
   *   The Commerce exception.
   */
  public static function handleErrors($result) {
    $result_data = $result->__toArray();
    if ($result_data['status'] == 'succeeded') {
      return;
    }
    // @todo: Better handling for possible Stripe errors.
    if (!empty($result_data['failure_code'])) {
      $failure_code = $result_data['failure_code'];
      // https://stripe.com/docs/api?lang=php#errors
      // Validation errors can be due to a module error (mapped to
      // InvalidRequestException) or due to a user input error (mapped to
      // a HardDeclineException).
      $hard_decline_codes = ['processing_error', 'missing', 'card_declined'];
      if (in_array($failure_code, $hard_decline_codes)) {
        throw new HardDeclineException($result_data['failure_message'], $failure_code);
      }
      else {
        throw new InvalidRequestException($result_data['failure_message'], $failure_code);
      }
    }
  }

}
