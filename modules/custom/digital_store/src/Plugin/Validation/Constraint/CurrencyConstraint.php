<?php

namespace Drupal\digital_store\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Currency constraint.
 *
 * @Constraint(
 *   id = "Currency",
 *   label = @Translation("Currency", context = "Validation"),
 *   type = { "price" }
 * )
 */
class CurrencyConstraint extends Constraint {

  public $availableCurrencies = [];
  public $invalidMessage = 'The currency %value is not valid.';
  public $notAvailableMessage = 'The currency %value is not available.';

}
