<?php

namespace Drupal\digital_store\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Plugin implementation of the 'Subscription Options' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_subscription_options",
 *   label = @Translation("Subscription Options"),
 *   description = @Translation("Display the Subscription Options of the
 *   referenced entities."), field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SubscriptionOptionsFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $options = $this->getEntitiesToView($items, $langcode);
    if (empty($options)) {
      return [];
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\digital_store\Form\SubscriptionOptionsForm', $options);
    return $form;
  }

}
