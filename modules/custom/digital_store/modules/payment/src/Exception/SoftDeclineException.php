<?php

namespace Drupal\digital_store_payment\Exception;

/**
 * Thrown for declined transactions that can be retried.
 */
class SoftDeclineException extends DeclineException {}
