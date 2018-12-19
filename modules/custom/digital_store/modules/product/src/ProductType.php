<?php

namespace Drupal\digital_store_product;

/**
 * Provides logic for handle Product Type.
 */
final class ProductType {

  /**
   * Product Type - WordPress Plugin.
   */
  const WORDPRESS_PLUGIN = 'wordpress.plugin';

  /**
   * Product Type - WordPress Theme.
   */
  const WORDPRESS_THEME = 'wordpress.theme';

  /**
   * The instantiated Product Types.
   *
   * @var array
   */
  public static $types = [];

  /**
   * Gets all available Product Types.
   *
   * @return array
   *   The Product Types.
   */
  public static function getTypes() {
    $definitions = [
      self::WORDPRESS_PLUGIN => t('WordPress Plugin'),
      self::WORDPRESS_THEME => t('WordPress Theme'),
    ];
    foreach ($definitions as $id => $definition) {
      self::$types[$id] = $definition;
    }
    return self::$types;
  }

  /**
   * Gets the labels of all available Product Types.
   *
   * @return array
   *   The labels, keyed by ID.
   */
  public static function getTypeLabels() {
    return self::getTypes();
  }

}
