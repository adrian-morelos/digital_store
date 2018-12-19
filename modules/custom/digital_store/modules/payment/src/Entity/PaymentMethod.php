<?php

namespace Drupal\digital_store_payment\Entity;

use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store_payment\CreditCard;
use Drupal\digital_store_payment\PaymentGatewayMode;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines the payment method entity class.
 */
class PaymentMethod extends DigitalStoreEntityBase implements PaymentMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    $type = [];
    $payment_gateway = $this->getPaymentGateway();
    if (empty($payment_gateway)) {
      $type[] = 'Payment Gateway: ' . $payment_gateway;
    }
    $owner = $this->getOwnerId();
    if (empty($owner)) {
      $type[] = 'Owner: ' . $owner;
    }
    if (!empty($type)) {
      return implode(' - ', $type);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentGateway() {
    $item = $this->get('payment_method_gateway');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentGatewayMode() {
    $item = $this->get('payment_method_gateway_mode');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    $item = $this->get('payment_method_owner');
    if (empty($item)) {
      return NULL;
    }
    $customer = $item->entity;
    // Handle deleted customers.
    if (!$customer) {
      $customer = User::getAnonymousUser();
    }
    return $customer;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    $item = $this->get('payment_method_owner');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('payment_method_owner', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('payment_method_owner', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteId() {
    $item = $this->get('payment_method_remote_id');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRemoteId($remote_id) {
    $this->set('payment_method_remote_id', $remote_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBillingProfile() {
    $item = $this->get('payment_method_billing_profile');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBillingProfile(array $profile = []) {
    $this->set('payment_method_billing_profile', $profile);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isReusable() {
    $item = $this->get('payment_method_reusable');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setReusable($reusable) {
    $this->set('payment_method_reusable', $reusable);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault() {
    $item = $this->get('payment_method_is_default');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefault($default) {
    $this->set('payment_method_is_default', $default);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isExpired() {
    $expires = $this->getExpiresTime();
    return $expires > 0 && $expires <= \Drupal::time()->getRequestTime();
  }

  /**
   * {@inheritdoc}
   */
  public function getExpiresTime() {
    $item = $this->get('payment_method_expires');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpiresTime($timestamp) {
    $this->set('payment_method_expires', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardType() {
    $item = $this->get('card_type');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCardType($card_type) {
    $this->set('card_type', $card_type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardNumber() {
    $item = $this->get('card_number');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCardNumber($card_number) {
    $this->set('card_number', $card_number);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardExpirationMonth() {
    $item = $this->get('card_exp_month');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCardExpirationMonth($card_exp_month) {
    $this->set('card_exp_month', $card_exp_month);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardExpirationYear() {
    $item = $this->get('card_exp_year');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCardExpirationYear($card_exp_year) {
    $this->set('card_exp_year', $card_exp_year);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
    // Field: Card type.
    $fields['card_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Card type'))
      ->setDescription(t('The credit card type.'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', CreditCard::getTypeLabels());
    // Field: Card number.
    $fields['card_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Card number'))
      ->setDescription(t('The last few digits of the credit card number'))
      ->setRequired(TRUE);
    // Field: Card expiration month.
    // card_exp_month and card_exp_year are not required because they might
    // not be known (tokenized non-reusable payment methods).
    $fields['card_exp_month'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Card expiration month'))
      ->setDescription(t('The credit card expiration month.'))
      ->setSetting('size', 'tiny');
    // Field: Card expiration year.
    $fields['card_exp_year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Card expiration year'))
      ->setDescription(t('The credit card expiration year.'))
      ->setSetting('size', 'small');
    // Field: Payment gateway.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'payment_gateway' => 'payment_gateway',
      ],
      'sort' => [
        'field' => 'name',
        'direction' => 'asc',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['payment_method_gateway'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Payment gateway'))
      ->setDescription(t('The payment gateway.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', $handler_settings)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 0,
        'settings' => [
          'link' => TRUE,
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'placeholder' => '',
          'size' => 60,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Payment gateway mode.
    $fields['payment_method_gateway_mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Payment gateway mode'))
      ->setDescription(t('The payment gateway mode.'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', PaymentGatewayMode::getModeLabels())
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
        'settings' => [
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Owner.
    $handler_settings = [
      'include_anonymous' => TRUE,
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => NULL,
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
    ];
    $fields['payment_method_owner'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The payment method owner.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default:user')
      ->setSetting('handler_settings', $handler_settings)
      ->setDefaultValueCallback('Drupal\digital_store_payment\Entity\PaymentMethod::getCurrentUserId')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Remote ID.
    $fields['payment_method_remote_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote ID'))
      ->setDescription(t('The payment method remote ID.'))
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Billing profile.
    $default_value = [
      'country_code' => 'US',
      'langcode' => 'en',
      'administrative_area' => '',
      'locality' => '',
      'dependent_locality' => '',
      'postal_code' => '',
      'sorting_code' => '',
      'address_line1' => '',
      'address_line2' => '',
      'organization' => '',
      'given_name' => '',
      'additional_name' => '',
      'family_name' => '',
    ];
    $available_countries = [
      'CA' => 'CA',
      'US' => 'CA',
    ];
    $field_overrides = [
      'givenName' => [
        'override' => 'optional',
      ],
      'familyName' => [
        'override' => 'optional',
      ],
      'organization' => [
        'override' => 'optional',
      ],
      'addressLine1' => [
        'override' => 'optional',
      ],
      'addressLine2' => [
        'override' => 'optional',
      ],
      'postalCode' => [
        'override' => 'optional',
      ],
      'locality' => [
        'override' => 'optional',
      ],
      'administrativeArea' => [
        'override' => 'optional',
      ],
    ];
    $fields['payment_method_billing_profile'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Billing profile'))
      ->setDescription(t('Billing profile'))
      ->setRequired(FALSE)
      ->setDefaultValue($default_value)
      ->setSetting('available_countries', $available_countries)
      ->setSetting('langcode_override', '')
      ->setSetting('field_overrides', $field_overrides)
      ->setDisplayOptions('form', [
        'type' => 'address_default',
        'weight' => 0,
        'settings' => [
          'default_country' => 'US',
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'address_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: is Reusable.
    $fields['payment_method_reusable'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Reusable'))
      ->setDescription(t('Whether the payment method is reusable.'))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: is Default Flag.
    // 'default' is a reserved SQL word, hence the 'is_' prefix.
    $fields['payment_method_is_default'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Default'))
      ->setDefaultValue(FALSE)
      ->setDescription(t("Whether this is the user's default payment method."))
      ->setSettings([
        'on_label' => t('Yes'),
        'off_label' => t('No'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE);
    // Field: Expires timestamp.
    $fields['payment_method_expires'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expires'))
      ->setDescription(t('The time when the payment method expires. 0 for never.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 1,
        'settings' => [
          'date_format' => 'custom',
          'custom_date_format' => 'n/Y',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(0);
    // Return The Fields.
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PAYMENT_METHOD_BUNDLE;
  }

}
