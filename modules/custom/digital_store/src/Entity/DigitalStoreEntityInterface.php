<?php

namespace Drupal\digital_store\Entity;

/**
 * Defines a common interface for all entity objects.
 */
interface DigitalStoreEntityInterface {

  /**
   * Gets the base entity.
   *
   * @return \Drupal\node\NodeInterface
   *   The base node entity.
   */
  public function getEntity();

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id();

  /**
   * Gets Attribute value.
   *
   * @param string $name
   *   The attribute name.
   *
   * @return mixed
   *   The attribute value.
   */
  public function getAttribute($name);

  /**
   * Sets a Attribute value.
   *
   * @param string $name
   *   The name of the attribute to set; e.g., 'title' or 'name'.
   * @param mixed $value
   *   The value to set, or NULL to unset the attribute.
   *
   * @return $this
   */
  public function setAttribute($name, $value);

  /**
   * Gets field value.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return mixed
   *   The field value.
   */
  public function get($field_name);

  /**
   * Sets a field value.
   *
   * @param string $field_name
   *   The name of the field to set; e.g., 'title' or 'name'.
   * @param mixed $value
   *   The value to set, or NULL to unset the field.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If the specified field does not exist.
   */
  public function set($field_name, $value);

  /**
   * Sets entity title.
   *
   * @param string $title
   *   The entity label.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Determines whether the entity is new.
   *
   * Usually an entity is new if no ID exists for it yet. However, entities may
   * be enforced to be new with existing IDs too.
   *
   * @return bool
   *   TRUE if the entity is new, or FALSE if the entity has already been saved.
   *
   * @see \Drupal\Core\Entity\EntityInterface::enforceIsNew()
   */
  public function isNew();

  /**
   * Gets the bundle of the entity.
   *
   * @return string
   *   The bundle of the entity. Defaults to the entity type ID if the entity
   *   type does not make use of different bundles.
   */
  public function bundle();

  /**
   * Gets the label of the entity.
   *
   * @return string|null
   *   The label of the entity, or NULL if there is no label defined.
   */
  public function label();

  /**
   * Loads an entity.
   *
   * @param mixed $id
   *   The id of the entity to load.
   *
   * @return static
   *   The entity object or NULL if there is no entity with the given ID.
   */
  public static function load($id);

  /**
   * Loads one or more entities.
   *
   * @param array $ids
   *   An array of entity IDs, or NULL to load all entities.
   *
   * @return static[]
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadMultiple(array $ids = NULL);

  /**
   * Saves an entity permanently.
   *
   * When saving existing entities, the entity is assumed to be complete,
   * partial updates of entities are not supported.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public function save();

  /**
   * Deletes an entity permanently.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public function delete();

  /**
   * Check if the entity has a given field.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return bool
   *   TRUE if the entity has the field, otherwise FALSE..
   */
  public function hasField($field_name);

  /**
   * Gets the original entity.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The base node entity.
   */
  public function getOriginal();

  /**
   * Gets the node creation timestamp.
   *
   * @param bool $formatted
   *   Flag to return the time $formatted.
   *
   * @return int
   *   Creation timestamp of the node.
   */
  public function getCreatedTime($formatted = FALSE);

  /**
   * Sets the node creation timestamp.
   *
   * @param int $timestamp
   *   The node creation timestamp.
   *
   * @return \Drupal\node\NodeInterface
   *   The called node entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the timestamp of the last entity change for the current translation.
   *
   * @param bool $formatted
   *   Flag to return the time $formatted.
   *
   * @return int
   *   The timestamp of the last entity save operation.
   */
  public function getChangedTime($formatted = FALSE);

  /**
   * Sets the timestamp of the last entity change for the current translation.
   *
   * @param int $timestamp
   *   The timestamp of the last entity save operation.
   *
   * @return $this
   */
  public function setChangedTime($timestamp);

  /**
   * Gets the public URL for this entity.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return string
   *   The URL for this entity.
   */
  public function url($rel = 'canonical', array $options = []);

}
