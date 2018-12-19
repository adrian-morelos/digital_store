<?php

namespace Drupal\digital_store_order\Entity;

use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\digital_store\Price;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\digital_store_order\OrderStatus;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\digital_store\Entity\DigitalStoreEntityBase;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Defines the order entity class.
 */
class Order extends DigitalStoreEntityBase implements OrderInterface {

  /**
   * {@inheritdoc}
   */
  public function getOrderNumber() {
    return $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomer() {
    $item = $this->get('customer');
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
  public function setCustomer(UserInterface $account) {
    $this->set('customer', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomerId() {
    $item = $this->get('customer');
    if (empty($item)) {
      return NULL;
    }
    return $item->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomerId($uid) {
    $this->set('customer', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    $item = $this->get('mail');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmail($mail) {
    $this->set('mail', $mail);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpAddress() {
    $item = $this->get('ip_address');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setIpAddress($ip_address) {
    $this->set('ip_address', $ip_address);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    $item = $this->get('order_items');
    if (empty($item)) {
      return [];
    }
    $referenced_entities = $item->referencedEntities();
    if (empty($referenced_entities)) {
      return [];
    }
    $items = [];
    foreach ($referenced_entities as $key => $entity) {
      $items[] = new OrderItem($entity);
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function setItems(array $order_items) {
    $this->set('order_items', $order_items);
    $this->recalculateTotalPrice();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasItems() {
    $item = $this->get('order_items');
    if (empty($item)) {
      return FALSE;
    }
    return !$item->isEmpty();
  }

  /**
   * Gets the index of the given order item.
   *
   * @param \Drupal\digital_store_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return int|bool
   *   The index of the given order item, or FALSE if not found.
   */
  protected function getItemIndex(OrderItemInterface $order_item) {
    $item = $this->get('order_items');
    if (empty($item)) {
      return FALSE;
    }
    $values = $item->getValue();
    $order_item_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $values);
    return array_search($order_item->id(), $order_item_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function addItem(OrderItemInterface $order_item) {
    if (!$this->hasItem($order_item)) {
      $this->get('order_items')->appendItem($order_item->id());
      $this->recalculateTotalPrice();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeItem(OrderItemInterface $order_item) {
    $index = $this->getItemIndex($order_item);
    if ($index !== FALSE) {
      $this->get('order_items')->offsetUnset($index);
      $this->recalculateTotalPrice();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem(OrderItemInterface $order_item) {
    return $this->getItemIndex($order_item) !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubtotalPrice() {
    /** @var \Drupal\digital_store\Price $subtotal_price */
    $subtotal_price = NULL;
    foreach ($this->getItems() as $order_item) {
      if ($order_item_total = $order_item->getTotalPrice()) {
        $subtotal_price = $subtotal_price ? $subtotal_price->add($order_item_total) : $order_item_total;
      }
    }
    return $subtotal_price;
  }

  /**
   * {@inheritdoc}
   */
  public function recalculateTotalPrice() {
    /** @var \Drupal\digital_store\Price $total_price */
    $total_price = NULL;
    foreach ($this->getItems() as $order_item) {
      if ($order_item_total = $order_item->getTotalPrice()) {
        $total_price = $total_price ? $total_price->add($order_item_total) : $order_item_total;
      }
    }
    $this->set('order_total_price', $total_price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalPrice() {
    $item = $this->get('order_total_price');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
    try {
      $price_item = $item->first();
    }
    catch (MissingDataException $e) {
      return NULL;
    }
    return $price_item->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalPaid() {
    $item = $this->get('total_paid');
    $is_empty = ($item) ? $item->isEmpty() : TRUE;
    if (!$is_empty) {
      /* @var \Drupal\digital_store\Plugin\Field\FieldType\PriceItem $price_item */
      try {
        $price_item = $item->first();
        return $price_item->toPrice();
      }
      catch (MissingDataException $e) {
        return NULL;
      }
    }
    elseif ($total_price = $this->getTotalPrice()) {
      // Provide a default without storing it, to avoid having to update
      // the field if the order currency changes before the order is placed.
      return new Price('0', $total_price->getCurrencyCode());
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setTotalPaid(Price $total_paid) {
    $this->set('total_paid', $total_paid);
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance() {
    if ($total_price = $this->getTotalPrice()) {
      return $total_price->subtract($this->getTotalPaid());
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isPaid() {
    $balance = $this->getBalance();
    return $balance && ($balance->isNegative() || $balance->isZero());
  }

  /**
   * {@inheritdoc}
   */
  public function orderIsOnCart() {
    $item = $this->get('cart');
    if (empty($item)) {
      return FALSE;
    }
    return !empty($item->value);
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $item = $this->get('locked');
    if (empty($item)) {
      return FALSE;
    }
    return !empty($item->value);
  }

  /**
   * {@inheritdoc}
   */
  public function setIsOnCart($value = FALSE) {
    $this->set('cart', $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lock() {
    $this->set('locked', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unlock() {
    $this->set('locked', FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlacedTime() {
    $item = $this->get('placed');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlacedTime($timestamp) {
    $this->set('placed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedTime() {
    $item = $this->get('completed');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getBillingDetail() {
    $item = $this->get('billing_details');
    if (empty($item)) {
      return NULL;
    }
    if ($item->isEmpty()) {
      return NULL;
    }
    /* @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address_item */
    try {
      $address_item = $item->first();
    }
    catch (MissingDataException $e) {
      return NULL;
    }
    return $address_item->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setBillingDetail(array $address = []) {
    $this->set('billing_details', $address);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletedTime($timestamp) {
    $this->set('completed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    $item = $this->get('state');
    if (empty($item)) {
      return NULL;
    }
    return $item->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setState($step) {
    $this->set('state', $step);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // Maintain the order Ip Address.
    if ($this->isNew()) {
      if (!$this->getIpAddress()) {
        $this->setIpAddress(\Drupal::request()->getClientIp());
      }
    }
    // Maintain the order customer.
    $customer = $this->getCustomer();
    if ($this->getCustomerId() && $customer->isAnonymous()) {
      $this->set('uid', 0);
    }
    // Maintain the order email.
    if (!$this->getEmail() && $customer->isAuthenticated()) {
      $this->setEmail($customer->getEmail());
    }
    // Maintain Order total price up-to-date.
    $this->recalculateTotalPrice();
    // Maintain the completed timestamp.
    $original_state = '';
    $original = $this->getOriginal();
    if (!is_null($original)) {
      $original = new Order($original);
      $original_state = $original->getState();
    }
    $state = $this->getState();
    if ($state == 'completed' && $original_state != 'completed') {
      if (empty($this->getCompletedTime())) {
        $this->setCompletedTime(\Drupal::time()->getRequestTime());
      }
    }
    // Return the base entity.
    return $this->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function postSave() {
    // Ensure there's a back-reference on each order item.
    foreach ($this->getItems() as $order_item) {
      if (empty($order_item->getOrderId())) {
        $order_item->setOrderId($this->id());
        try {
          $order_item->save();
        }
        catch (EntityStorageException $e) {
          // @todo add an error log entry.
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete() {
    // Delete the order items of a deleted order.
    $order_items = [];
    foreach ($this->getItems() as $order_item) {
      $order_items[$order_item->id()] = $order_item->getEntity();
    }
    /** @var \Drupal\node\NodeStorageInterface $order_item_storage */
    $order_item_storage = \Drupal::service('entity_type.manager')
      ->getStorage('node');
    try {
      $order_item_storage->delete($order_items);
    }
    catch (EntityStorageException $e) {
      // @todo add an error log entry.
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundle() {
    return DIGITAL_STORE_ORDER_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions() {
    $fields = [];

    // Field: Customer.
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
    $fields['customer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Customer'))
      ->setDescription(t('The customer.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default:user')
      ->setSetting('handler_settings', $handler_settings)
      ->setDefaultValueCallback('Drupal\digital_store_order\Entity\Order::getCurrentUserId')
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

    // Field: Contact email.
    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Contact email'))
      ->setDescription(t('The email address associated with the order.'))
      ->setDefaultValue('')
      ->setRequired(FALSE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'basic_string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => 2,
        'settings' => [
          'placeholder' => 'jane.doe@example.com',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Field: IP address.
    $fields['ip_address'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP address'))
      ->setDescription(t('The IP address of the order.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 128)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'region' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    // Field: Total price.
    $available_currencies = [
      'USD' => 'USD',
    ];
    $fields['order_total_price'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Total price'))
      ->setDescription(t('The total price of the order.'))
      ->setReadOnly(TRUE)
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'price_default',
        'weight' => 3,
        'settings' => [
          'available_currencies ' => $available_currencies
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    // Field: Total paid.
    $fields['total_paid'] = BaseFieldDefinition::create('price')
      ->setLabel(t('Total paid'))
      ->setDescription(t('The total paid price of the order.'))
      ->setSetting('available_currencies', $available_currencies)
      ->setDisplayOptions('form', [
        'type' => 'price_default',
        'weight' => 4,
        'settings' => [
          'available_currencies ' => $available_currencies
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    // Field: Placed.
    $fields['placed'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Placed'))
      ->setDescription(t('The time when the order was placed.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Field: Completed.
    $fields['completed'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Completed'))
      ->setDescription(t('The time when the order was completed.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Field: Order Items.
    $handler_settings = [
      'filter' => [
        'type' => '_none',
      ],
      'target_bundles' => [
        'order_item' => 'order_item',
      ],
      'sort' => [
        'field' => '_none',
      ],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];
    $fields['order_items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Order items')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', $handler_settings)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => 'order item',
          'label_plural' => 'order items',
          'allow_new' => TRUE,
          'match_operator' => 'CONTAINS',
          'allow_existing' => 'false',
          'form_mode' => 'default',
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'order_item_table',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Field: Billing Details.
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
    $fields['billing_details'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Billing Details'))
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

    // Field: Cart.
    $fields['cart'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Cart'))
      ->setDescription(t('Whether the order is on the cart.'))
      ->setDefaultValue(FALSE)
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
    // Field: Lock.
    $fields['locked'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Locked'))
      ->setDescription(t('Locked Status.'))
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
      ->setDefaultValue(FALSE);
    // Field: State.
    $fields['state'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Order State'))
      ->setDescription(t('The order state.'))
      ->setSetting('allowed_values', OrderStatus::getOrderStatusLabels())
      ->setReadOnly(TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue('draft')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 3,
        'settings' => [
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
    // Return The Fields.
    return $fields;
  }

}