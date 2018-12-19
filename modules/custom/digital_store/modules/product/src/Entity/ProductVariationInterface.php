<?php

namespace Drupal\digital_store_product\Entity;

use Drupal\digital_store\Price;
use Drupal\digital_store\Entity\PurchasableEntityInterface;

/**
 * Defines the interface for product variations.
 */
interface ProductVariationInterface extends PurchasableEntityInterface {

  /**
   * Gets the label of the entity.
   *
   * @return string|null
   *   The label of the entity, or NULL if there is no label defined.
   */
  public function label();

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id();

  /**
   * Gets the parent product.
   *
   * @return ProductInterface|null
   *   The product entity, or null.
   */
  public function getProduct();

  /**
   * Gets the parent product ID.
   *
   * @return int|null
   *   The product ID, or null.
   */
  public function getProductId();

  /**
   * Set the parent product ID.
   *
   * @param string $id
   *   The product ID.
   *
   * @return int|null
   *   The product ID, or NULL.
   */
  public function setProductId($id);

  /**
   * Get the variation SKU.
   *
   * @return string
   *   The variation SKU.
   */
  public function getSku();

  /**
   * Set the variation SKU.
   *
   * @param string $sku
   *   The variation SKU.
   *
   * @return $this
   */
  public function setSku($sku);

  /**
   * Sets the price.
   *
   * @param \Drupal\digital_store\Price $price
   *   The price.
   *
   * @return $this
   */
  public function setPrice(Price $price);

  /**
   * Gets whether the variation is active.
   *
   * Inactive variations are not visible on add to cart forms.
   *
   * @return bool
   *   TRUE if the variation is active, FALSE otherwise.
   */
  public function isActive();

  /**
   * Sets whether the variation is active.
   *
   * @param bool $active
   *   Whether the variation is active.
   *
   * @return $this
   */
  public function setActive($active);

}
