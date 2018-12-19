<?php

namespace Drupal\digital_store;

use Drupal\node\NodeInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Field\BaseFieldDefinition as EntityBundleFieldDefinition;

/**
 * Defines Entity Helper class.
 */
class DigitalStoreEntityHelper {

  /**
   * {@inheritdoc}
   */
  public function createField(EntityBundleFieldDefinition $field_definition, $lock = TRUE) {
    $field_name = $field_definition->getName();
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    $bundle = $field_definition->getTargetBundle();
    if (empty($field_name) || empty($entity_type_id) || empty($bundle)) {
      drupal_set_message('The passed $field_definition is incomplete.', 'warning');
      return;
    }
    // loadByName() is an API that doesn't exist on the storage classes for
    // the two entity types, so we're using the entity classes directly.
    $field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name);
    $field = FieldConfig::loadByName($entity_type_id, $bundle, $field_name);
    if (!empty($field)) {
      $message = sprintf('The field "%s" already exists on bundle "%s" of entity type "%s".', $field_name, $bundle, $entity_type_id);
      drupal_set_message($message, 'warning');
      return;
    }

    // The field storage might already exist if the field was created earlier
    // on a different bundle of the same entity type.
    if (empty($field_storage)) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type_id,
        'type' => $field_definition->getType(),
        'cardinality' => $field_definition->getCardinality(),
        'settings' => $field_definition->getSettings(),
        'translatable' => $field_definition->isTranslatable(),
        'locked' => $lock,
      ]);
      $field_storage->save();
    }

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
      'label' => $field_definition->getLabel(),
      'required' => $field_definition->isRequired(),
      'settings' => $field_definition->getSettings(),
      'translatable' => $field_definition->isTranslatable(),
      'default_value' => $field_definition->getDefaultValueLiteral(),
      'default_value_callback' => $field_definition->getDefaultValueCallback(),
    ]);
    $field->save();
  }

}
