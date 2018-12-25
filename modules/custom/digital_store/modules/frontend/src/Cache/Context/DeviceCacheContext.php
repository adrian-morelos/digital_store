<?php

namespace Drupal\digital_store_frontend\Cache\Context;

use Drupal\digital_store_frontend\FrontendHelperInterface;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines the DeviceCacheContext service, for "per device" caching.
 *
 * Cache context ID: 'device'.
 */
class DeviceCacheContext implements CacheContextInterface {

  /**
   * The Frontend Helper service.
   *
   * @var \Drupal\digital_store_frontend\FrontendHelperInterface
   */
  protected $frontendHelper;

  /**
   * Constructs a new CartCacheContext object.
   *
   * @param \Drupal\digital_store_frontend\FrontendHelperInterface $frontend_helper
   *   The frontend helper.
   */
  public function __construct(FrontendHelperInterface $frontend_helper) {
    $this->frontendHelper = $frontend_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Current Device IDs');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return implode(':', $this->frontendHelper->getDeviceIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
