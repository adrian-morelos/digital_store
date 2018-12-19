<?php

namespace Drupal\digital_store\Entity;

use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityMalformedException;

/**
 * Implements digital store entity base.
 *
 */
abstract class DigitalStoreEntityBase implements DigitalStoreEntityInterface {

  /**
   * The base node reference.
   *
   * @var \Drupal\node\NodeInterface $entity
   */
  protected $entity = NULL;

  /**
   * Constructs an Entity object.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The base node.
   */
  public function __construct(NodeInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->entity->label();
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function get($field_name) {
    if (!$this->hasField($field_name)) {
      return NULL;
    }
    return $this->entity->get($field_name);
  }

  /**
   * {@inheritdoc}
   */
  public function set($field_name, $value) {
    if (!$this->hasField($field_name)) {
      return NULL;
    }
    return $this->entity->set($field_name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->label();
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->entity->setTitle($title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime($formatted = FALSE) {
    $timestamp = $this->entity->getCreatedTime();
    if (!empty($timestamp) && $formatted) {
      $timestamp = date('Y-m-d H:i:s');
    }
    return $timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->entity->setCreatedTime($timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime($formatted = FALSE) {
    $timestamp = $this->entity->getChangedTime();
    if (!empty($timestamp) && $formatted) {
      $timestamp = \Drupal::service('date.formatter')
        ->formatTimeDiffSince($timestamp);
    }
    return $timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->entity->setChangedTime($timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->entity->getOwner();
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->entity->setOwner($account);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttribute($name) {
    return $this->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function setAttribute($name, $value) {
    return $this->set($name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->entity->getOwnerId();
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->entity->setOwnerId($uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNew() {
    return $this->entity->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public function bundle() {
    return $this->entity->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    return $this->entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    $this->entity->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $this->entity->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function url($rel = 'canonical', array $options = []) {
    try {
      $url = $this->entity->toUrl($rel = 'canonical', $options = []);
    }
    catch (EntityMalformedException $e) {
      $url = NULL;
    }
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function hasField($field_name) {
    if (!$this->entity) {
      return FALSE;
    }
    return (bool) $this->entity->hasField($field_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginal() {
    if (!isset($this->entity->original)) {
      return NULL;
    }
    return $this->entity->original;
  }

  /**
   * {@inheritdoc}
   */
  public static function load($id) {
    $entity_manager = \Drupal::entityTypeManager();
    $node = $entity_manager->getStorage('node')->load($id);
    if (is_null($node)) {
      return NULL;
    }
    $entity_class = get_called_class();
    return new $entity_class($node);
  }

  /**
   * {@inheritdoc}
   */
  public static function loadMultiple(array $ids = NULL) {
    $entity_manager = \Drupal::entityTypeManager();
    $entities = $entity_manager->getStorage('node')->loadMultiple($ids);
    if (empty($entities)) {
      return [];
    }
    $entity_class = get_called_class();
    $items = [];
    foreach ($entities as $key => $node) {
      $items[] = new $entity_class($node);
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $values = []) {
    /* @var \Drupal\digital_store\Entity\DigitalStoreEntityBase $entity_class */
    $entity_class = get_called_class();
    // Add Bundle.
    $values['type'] = $entity_class::getBundle();
    $node = \Drupal::entityTypeManager()->getStorage('node')->create($values);
    return new $entity_class($node);
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Gets base field definitions.
   *
   * @return array
   *   The field definitions.
   */
  public static function baseFieldDefinitions() {
    return [];
  }

  /**
   * Gets Attribute Bundle name.
   *
   * @return string|null
   *   The Bundle name.
   */
  public static function getBundle() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFields() {
    /* @var \Drupal\digital_store\Entity\DigitalStoreEntityBase $entity_class */
    $entity_class = get_called_class();
    $bundle = $entity_class::getBundle();
    $fields = $entity_class::baseFieldDefinitions();
    if (empty($fields)) {
      return NULL;
    }
    $digital_store_entity_helper = \Drupal::service('digital_store.entity_helper');
    foreach ($fields as $name => $definition) {
      $definition->setName($name)
        ->setTargetEntityTypeId('node')
        ->setTargetBundle($bundle);
      $digital_store_entity_helper->createField($definition, $lock = TRUE);
    }
  }

}
