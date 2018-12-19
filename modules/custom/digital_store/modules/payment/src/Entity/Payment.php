<?php

namespace Drupal\digital_store_payment\Entity;

use Drupal\digital_store\Price;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store_order\Entity\Order;
use Drupal\digital_store_payment\PaymentStatus;
use Drupal\digital_store_payment\PaymentGatewayMode;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;

/**
 * Defines the payment entity class.
 */
class Payment extends DigitalStoreEntityBase implements PaymentInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // UIs should use the number formatter to show a more user-readable version.
    return $this->getAmount()->__toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    // @todo.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentGateway() {
    $item = $this->get('payment_gateway');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentGatewayMode() {
    $item = $this->get('payment_gateway_mode');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    $item = $this->get('payment_method');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);

    return $referenced_entity ? new PaymentMethod($referenced_entity) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod(PaymentMethodInterface $payment_method = NULL) {
    if (!$payment_method) {
      return NULL;
    }
    $id = $payment_method->id();
    if (empty($id)) {
      return NULL;
    }
    $this->set('payment_method', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethodId($id = NULL) {
    if (empty($id)) {
      return NULL;
    }
    $this->set('payment_method', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethodId() {
    $item = $this->get('payment_method');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    $item = $this->get('payment_order_id');
    if (empty($item)) {
      return NULL;
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return NULL;
    }
    $referenced_entity = reset($referenced_entities);

    return $referenced_entity ? new Order($referenced_entity) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderId() {
    $item = $this->get('payment_order_id');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteId() {
    $item = $this->get('payment_remote_id');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRemoteId($remote_id) {
    $this->set('payment_remote_id', $remote_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteState() {
    $item = $this->get('payment_remote_state');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRemoteState($remote_state) {
    $this->set('payment_remote_state', $remote_state);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance() {
    if ($amount = $this->getAmount()) {
      $refunded_amount = $this->getRefundedAmount();
      return $amount->subtract($refunded_amount);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    $item = $this->get('payment_amount');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
    $price_item = $item->first();
    return $price_item->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount(Price $amount) {
    $this->set('payment_amount', $amount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRefundedAmount() {
    $item = $this->get('payment_refunded_amount');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
    $price_item = $item->first();
    return $price_item->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function setRefundedAmount(Price $refunded_amount) {
    $this->set('payment_refunded_amount', $refunded_amount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    $item = $this->get('payment_state');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setState($state_id) {
    $this->set('payment_state', $state_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizedTime() {
    $item = $this->get('payment_authorized');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthorizedTime($timestamp) {
    $this->set('payment_authorized', $timestamp);
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
    $item = $this->get('payment_expires');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpiresTime($timestamp) {
    $this->set('payment_expires', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted() {
    $item = $this->get('payment_completed');
    if (empty($item)) {
      return FALSE;
    }
    return !$item->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedTime() {
    $item = $this->get('payment_completed');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletedTime($timestamp) {
    $this->set('payment_completed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_PAYMENT_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];
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
    $fields['payment_gateway'] = BaseFieldDefinition::create('entity_reference')
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
    $fields['payment_gateway_mode'] = BaseFieldDefinition::create('list_string')
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
    // Field: Payment method.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'payment_method' => 'payment_method',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['payment_method'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Payment method'))
      ->setDescription(t('The payment method.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setReadOnly(TRUE);
    // Field: Order.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'order' => 'order',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['payment_order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setDescription(t('The parent order.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setReadOnly(TRUE);
    // Field: Remote ID.
    $fields['payment_remote_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote ID'))
      ->setDescription(t('The remote payment ID.'))
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Remote State.
    $fields['payment_remote_state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote State'))
      ->setDescription(t('The remote payment state.'))
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Payment amount.
    $available_currencies = [
      'USD' => 'USD',
    ];
    $fields['payment_amount'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Amount'))
      ->setDescription(t('The payment amount.'))
      ->setRequired(TRUE)
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Refunded amount.
    $fields['payment_refunded_amount'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Refunded amount'))
      ->setDescription(t('The refunded payment amount.'))
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Payment state.
    $fields['payment_state'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('State'))
      ->setDescription(t('The payment state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setSetting('allowed_values', PaymentStatus::getStateLabels());
    // Field: Authorized timestamp.
    $fields['payment_authorized'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Authorized'))
      ->setDescription(t('The time when the payment was authorized.'))
      ->setDisplayConfigurable('view', TRUE);
    // Field: Expires timestamp.
    $fields['payment_expires'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expires'))
      ->setDescription(t('The time when the payment expires. 0 for never.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(0);
    // Field: Completed timestamp.
    $fields['payment_completed'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Completed'))
      ->setDescription(t('The time when the payment was completed.'))
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}
