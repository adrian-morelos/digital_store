<?php

namespace Drupal\digital_store_product\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ProductVariationSku constraint.
 */
class ProductVariationSkuConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }

    $sku = $item->value;
    if (isset($sku) && $sku !== '') {
      $sku_exists = (bool) \Drupal::entityQuery('node')
        ->condition('sku', $sku)
        ->condition('nid', (int) $items->getEntity()->id(), '<>')
        ->range(0, 1)
        ->count()
        ->execute();

      if ($sku_exists) {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%sku', $this->formatValue($sku))
          ->addViolation();
      }
    }
  }

}
