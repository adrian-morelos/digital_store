<?php

namespace Drupal\digital_store_analytics;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides Analytics Client - Communicates with GA via Measurement Protocol.
 */
class AnalyticsClient implements AnalyticsClientInterface {

  /**
   * The GA Host.
   *
   * @var string
   */
  const GA_HOST = 'https://www.google-analytics.com/collect';

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
  protected $configName = 'digital_store_analytics.settings.google_analytics';

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
  public function getProtocolVersion() {
    if (!$this->configuration) {
      return NULL;
    }
    return $this->configuration->get('protocol_version');
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackingId() {
    if (!$this->configuration) {
      return NULL;
    }
    return $this->configuration->get('tid');
  }

  /**
   * Gets Google Analytics Base Url.
   *
   * @return string
   *   The Google Analytics Base Url.
   */
  public function getGoogleAnalyticsBaseUrl() {
    return self::GA_HOST;
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
    $endpoint = $this->getGoogleAnalyticsBaseUrl() . '/' . $requestUri;
    try {
      $res = $this->getHttpClient()->request('GET', $endpoint, $options);
    } catch (GuzzleException $e) {
      $res = $e->getMessage();
    } catch (\Exception $e) {
      $res = $e->getMessage();
    }
    return $res;
  }

  /**
   * Execute a POST request against the API.
   *
   * @param array $options
   *   The options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   A guzzle response.
   */
  public function post(array $options = []) {
    $endpoint = $this->getGoogleAnalyticsBaseUrl();
    try {
      $res = $this->getHttpClient()->request('POST', $endpoint, $options);
    }
    catch (GuzzleException $e) {
      $res = NULL;
    }
    return $res;
  }

  /**
   * Generates the user agent we use to pass to API request so
   * Stripe can identify our application.
   *
   * @since 4.0.0
   * @version 4.0.0
   */
  public function getUserAgent() {
    $app_info = [
      'name' => 'Digital Store Google Analytics',
      'version' => '1.0.0',
      'url' => 'https://wpgle.com',
    ];
    return [
      'lang' => 'php',
      'lang_version' => phpversion(),
      'publisher' => 'wpgle',
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
      'Authorization' => 'Basic ' . base64_encode($this->getTrackingId() . ':'),
      'Protocol-Version' => $this->getProtocolVersion(),
      'User-Agent' => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
      'X-Analytics-Client-User-Agent' => json_encode($user_agent),
      'Content-Type' => 'application/x-www-form-urlencoded',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function addRequiredFields(array &$fields = []) {
    $fields['v'] = $this->getProtocolVersion();
    $fields['tid'] = $this->getTrackingId();
  }

  /**
   * {@inheritdoc}
   */
  public function sendHit(array $fields = []) {
    $this->addRequiredFields($fields);
    $options = [
      'headers' => $this->getHeaders(),
      'form_params' => $fields,
    ];
    try {
      $response = $this->post($options);
      $result = ($response->getStatusCode() == 200);
    }
    catch (ClientException $e) {
      $result = FALSE;
    }
    return $result;
  }

}
