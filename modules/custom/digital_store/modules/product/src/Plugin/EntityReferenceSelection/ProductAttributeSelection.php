<?php

namespace Drupal\digital_store_product\Plugin\EntityReferenceSelection;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Plugin implementation of the 'Product Attribute Selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "product_attribute_selection",
 *   label = @Translation("Product Attribute selection"),
 *   entity_types = {"node"},
 *   group = "product_attribute_selection",
 *   weight = 2
 * )
 */
class ProductAttributeSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'product_attribute' => [
          'is_product_attribute' => FALSE,
        ],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $configuration = $this->getConfiguration();
    $is_product_attribute = $configuration['product_attribute']['is_product_attribute'] ?? FALSE;
    // Add Product Attributes details.
    $form['product_attribute'] = [
      '#type' => 'container',
      '#title' => t('Product Attribute'),
    ];
    // Add Field: Is Product Attribute?.
    $form['product_attribute']['is_product_attribute'] = [
      '#type' => 'checkbox',
      '#title' => t('This Bundle represents a product attribute?'),
      '#description' => t('Check this box if you would like to use this node-type as a product attribute.'),
      '#default_value' => $is_product_attribute,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    // Set Target Bundles.
    $form_state->setValue(['settings', 'handler_settings', 'target_bundles'], $this->getTargetBundles());
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    // Filter by custom bundles according to the Product Attribute config.
    $target_bundles = $this->getTargetBundles();
    if (!empty($target_bundles)) {
      $query->condition('type', $target_bundles, 'IN');
    }
    return $query;
  }

  /**
   * Get Target Bundles.
   *
   * @return array
   *   Return array of target bundles user for the filter..
   */
  protected function getTargetBundles() {
    $target_bundles = [];
    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];
    $bundles_info = $this->entityManager->getBundleInfo($target_type);
    $bundles = array_keys($bundles_info);
    if (empty($bundles)) {
      return [];
    }
    $entity_types = NodeType::loadMultiple($bundles);
    if (empty($entity_types)) {
      return [];
    }
    // Get Bundles with the flag: product attribute checked.
    $is_product_attribute = $configuration['product_attribute']['is_product_attribute'] ?? FALSE;
    if ($is_product_attribute) {
      /* @var \Drupal\Core\Config\Entity\ConfigEntityBundleBase $type */
      foreach ($entity_types as $delta => $type) {
        if ($type->getThirdPartySetting('digital_store_product', 'is_product_attribute', 0)) {
          $target_bundles[$delta] = $delta;
        }
      }
    }
    return $target_bundles;
  }

}
