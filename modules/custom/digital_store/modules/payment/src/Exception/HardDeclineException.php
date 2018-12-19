<?php

namespace Drupal\digital_store_payment\Exception;

/**
 * Thrown for declined transactions that can't be retried.
 */
class HardDeclineException extends DeclineException {}
