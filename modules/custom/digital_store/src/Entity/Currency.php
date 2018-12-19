<?php

namespace Drupal\digital_store\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the currency entity class.
 *
 * @ConfigEntityType(
 *   id = "currency",
 *   label = @Translation("Currency"),
 *   label_collection = @Translation("Currencies"),
 *   label_singular = @Translation("currency"),
 *   label_plural = @Translation("currencies"),
 *   label_count = @PluralTranslation(
 *     singular = "@count currency",
 *     plural = "@count currencies",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\digital_store\Form\CurrencyForm",
 *       "edit" = "Drupal\digital_store\Form\CurrencyForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\digital_store\CurrencyRouteProvider",
 *     },
 *     "list_builder" = "Drupal\digital_store\CurrencyListBuilder",
 *   },
 *   admin_permission = "administer currency",
 *   config_prefix = "currency",
 *   entity_keys = {
 *     "id" = "currencyCode",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "currencyCode",
 *     "name",
 *     "numericCode",
 *     "symbol",
 *     "fractionDigits"
 *   },
 *   links = {
 *     "add-form" = "/admin/digital_store/config/currencies/add-custom",
 *     "edit-form" = "/admin/digital_store/config/currencies/{currency}",
 *     "delete-form" = "/admin/digital_store/config/currencies/{currency}/delete",
 *     "collection" = "/admin/digital_store/config/currencies"
 *   }
 * )
 */
class Currency extends ConfigEntityBase implements CurrencyInterface {

  /**
   * The alphanumeric currency code.
   *
   * @var string
   */
  protected $currencyCode;

  /**
   * The currency name.
   *
   * @var string
   */
  protected $name;

  /**
   * The numeric currency code.
   *
   * @var string
   */
  protected $numericCode;

  /**
   * The currency symbol.
   *
   * @var string
   */
  protected $symbol;

  /**
   * The number of fraction digits.
   *
   * @var int
   */
  protected $fractionDigits = 2;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->currencyCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->currencyCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($currency_code) {
    $this->currencyCode = $currency_code;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumericCode() {
    return $this->numericCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setNumericCode($numeric_code) {
    $this->numericCode = $numeric_code;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSymbol() {
    return $this->symbol;
  }

  /**
   * {@inheritdoc}
   */
  public function setSymbol($symbol) {
    $this->symbol = $symbol;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFractionDigits() {
    return $this->fractionDigits;
  }

  /**
   * {@inheritdoc}
   */
  public function setFractionDigits($fraction_digits) {
    $this->fractionDigits = $fraction_digits;
    return $this;
  }

}
