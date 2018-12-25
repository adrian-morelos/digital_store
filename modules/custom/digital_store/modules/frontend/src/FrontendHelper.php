<?php

namespace Drupal\digital_store_frontend;

/**
 * Default implementation of the frontend Helper class.
 */
class FrontendHelper implements FrontendHelperInterface {

  /**
   * The Mobile Detect service.
   *
   * @var \Mobile_Detect
   */
  protected $mobileDetect;

  /**
   * {@inheritdoc}
   */
  public function getMobileDetect() {
    if (!$this->mobileDetect) {
      $this->mobileDetect = new \Mobile_Detect;
    }
    return $this->mobileDetect;
  }

  /**
   * {@inheritdoc}
   */
  public function isMobile() {
    $mobile_detect = $this->getMobileDetect();
    if (!$mobile_detect) {
      return FALSE;
    }
    return $mobile_detect->isMobile() && (!$mobile_detect->isTablet());
  }

  /**
   * {@inheritdoc}
   */
  public function isTablet() {
    $mobile_detect = $this->getMobileDetect();
    if (!$mobile_detect) {
      return FALSE;
    }
    return $mobile_detect->isTablet();
  }

  /**
   * {@inheritdoc}
   */
  public function isDesktop() {
    $mobile_detect = $this->getMobileDetect();
    if (!$mobile_detect) {
      return TRUE;
    }
    return (!$mobile_detect->isMobile()) && (!$mobile_detect->isTablet());
  }

  /**
   * {@inheritdoc}
   */
  public function getDeviceIds() {
    $is_mobile = $this->isMobile() ? '1' : '0';
    $is_tablet = $this->isTablet() ? '1' : '0';
    $is_desktop = $this->isDesktop() ? '1' : '0';
    return [
      'is_mobile' . $is_mobile,
      'is_tablet' . $is_tablet,
      'is_desktop' . $is_desktop,
    ];
  }

}
