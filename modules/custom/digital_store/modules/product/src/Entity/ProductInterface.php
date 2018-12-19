<?php

namespace Drupal\digital_store_product\Entity;

/**
 * Defines the interface for products.
 */
interface ProductInterface {

  /**
   * Gets the variation IDs.
   *
   * @return int[]
   *   The variation IDs.
   */
  public function getVariationIds();

  /**
   * Gets the variations.
   *
   * @return \Drupal\digital_store_product\Entity\ProductVariationInterface[]
   *   The variations.
   */
  public function getVariations();

  /**
   * Gets the product summary.
   *
   * @return \Drupal\node\NodeInterface
   *   The product summary entity.
   */
  public function getProductSummary();

  /**
   * Gets the product setting.
   *
   * @return \Drupal\digital_store_product\Plugin\Field\FieldType\PriceSettingItem
   *   The product's price config.
   */
  public function getProductSetting();

  /**
   * Sets the variations.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   *
   * @return $this
   */
  public function setVariations(array $variations);

  /**
   * Gets whether the product has variations.
   *
   * A product must always have at least one variation, but a newly initialized
   * (or invalid) product entity might not have any.
   *
   * @return bool
   *   TRUE if the product has variations, FALSE otherwise.
   */
  public function hasVariations();

  /**
   * Adds a variation.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function addVariation(ProductVariationInterface $variation);

  /**
   * Removes a variation.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function removeVariation(ProductVariationInterface $variation);

  /**
   * Checks whether the product has a given variation.
   *
   * @param \Drupal\digital_store_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return bool
   *   TRUE if the variation was found, FALSE otherwise.
   */
  public function hasVariation(ProductVariationInterface $variation);

  /**
   * Gets the default variation.
   *
   * @return \Drupal\digital_store_product\Entity\ProductVariationInterface|null
   *   The default variation, or NULL if none found.
   */
  public function getDefaultVariation();

}
