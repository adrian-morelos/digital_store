<?php

namespace Drupal\digital_store_frontend;

/**
 * Default definition of the frontend Helper class.
 */
interface FrontendHelperInterface {

  /**
   * Check if the device is mobile.
   *
   * @return bool
   *   Returns TRUE if any type of mobile device detected, including
   *   special ones.
   */
  public function isMobile();

  /**
   * Check if the device is a tablet.
   *
   * @return bool
   *   Returns TRUE if any type of tablet device is detected.
   */
  public function isTablet();

  /**
   * Check if the device is a desktop.
   *
   * @return bool
   *   Returns TRUE if any type of desktop device is detected.
   */
  public function isDesktop();

  /**
   * Get the device Ids contexts.
   *
   * @return array
   *   Returns a list with the devices detected.
   */
  public function getDeviceIds();

  /**
   * Get the Mobile Detect class instance.
   *
   * @return \Mobile_Detect|null
   *   Returns the mobile detect class, otherwise FALSE.
   */
  public function getMobileDetect();

}
