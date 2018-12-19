<?php

namespace Drupal\digital_store_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Search form' block.
 *
 * @Block(
 *   id = "digital_store_search_form_block",
 *   admin_label = @Translation("Search block form"),
 *   category = @Translation("Forms")
 * )
 */
class SearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()
      ->getForm('Drupal\digital_store_search\Form\SearchBlockForm');
  }

}
