<?php

namespace Drupal\digital_store_cart;

use Drupal\digital_store_cart\Form\AddToCartForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\digital_store_product\Entity\Product;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;

/**
 * Provides #lazy_builder callbacks.
 */
class ProductLazyBuilders {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new CartLazyBuilders object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder, EntityRepositoryInterface $entity_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Builds the add to cart form.
   *
   * @param string $product_id
   *   The product ID.
   * @param string $view_mode
   *   The view mode used to render the product.
   * @param bool $combine
   *   TRUE to combine order items containing the same product variation.
   * @param string $langcode
   *   The langcode for the language that should be used in form.
   *
   * @return array
   *   A renderable array containing the cart form.
   */
  public function addToCartForm($product_id, $view_mode, $combine, $langcode) {
    /** @var \Drupal\node\NodeInterface $product_entity */
    $product_entity = $this->entityTypeManager->getStorage('node')
      ->load($product_id);
    if ($product_entity->bundle() != Product::getBundle()) {
      return [];
    }
    // Load Product for current language.
    $product_entity = $this->entityRepository->getTranslationFromContext($product_entity, $langcode);
    $product = new Product($product_entity);
    $default_variation = $product->getDefaultVariation();
    if (!$default_variation) {
      return [];
    }
    $form_state = (new FormState())->setFormState([
      'product' => $product,
      'view_mode' => $view_mode,
      'settings' => [
        'combine' => $combine,
      ],
    ]);
    return $this->formBuilder->buildForm(AddToCartForm::class, $form_state);
  }

}
