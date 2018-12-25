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
    if (!$this->mobileDetect) {
      return FALSE;
    }
    return $this->mobileDetect->isMobile();
  }

  /**
   * {@inheritdoc}
   */
  public function isTablet() {
    if (!$this->mobileDetect) {
      return FALSE;
    }
    return $this->mobileDetect->isTablet();
  }

  /**
   * {@inheritdoc}
   */
  public function isDesktop() {
    if (!$this->mobileDetect) {
      return TRUE;
    }
    return (!$this->mobileDetect->isMobile()) && (!$this->mobileDetect->isTablet());
  }

}
