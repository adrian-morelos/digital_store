<?php

namespace Drupal\digital_store_analytics;

/**
 * Provides the interface for the Analytics Client.
 */
interface AnalyticsClientInterface {
  /**
   * Execute a POST request against the API to send a Hit.
   *
   * @param array $parameters
   *   The parameters.
   *
   * @return bool
   *   True if the operation was completed, Otherwise FALSE.
   */
  public function sendHit(array $parameters = []);
}
