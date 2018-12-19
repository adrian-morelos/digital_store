<?php

namespace Drupal\digital_store_checkout\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\digital_store_order\Entity\OrderInterface;

/**
 * Define Base Step Class.
 */
abstract class CheckoutFlowStepElement extends FormElement {

  /**
   * Gets the current user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The current user.
   */
  protected static function currentUser() {
    return \Drupal::currentUser();
  }

  /**
   * Prepares a #type 'checkout flow step' render element.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderStep(array $element) {
    $element['#attributes']['type'] = 'checkout-flow-step';
    $element['#attributes']['class'][] = 'r';
    $element['#prefix'] = '<div class="c-f-s">';
    $element['#suffix'] = '</div>';
    return $element;
  }

  /**
   * Validates the trigger is an associated to a given action.
   *
   * @param string $action
   *   The action name.
   * @param \Drupal\Core\Form\FormStateInterface $element_state
   *   The current state of the complete form.
   *
   * @return bool
   *   TRUE if the trigger is an associated to a given action, otherwise FALSE.
   */
  public static function isTriggeringAction($action = '', FormStateInterface $element_state = NULL) {
    if (empty($action)) {
      return FALSE;
    }
    $triggering_element = $element_state->getTriggeringElement();
    if (empty($triggering_element)) {
      return FALSE;
    }
    $operation = $triggering_element['#action'] ?? NULL;
    if (empty($operation)) {
      return FALSE;
    }
    return ($operation == $action);
  }

  /**
   * Validates the order parameter.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return bool
   *   TRUE if the #order value is valid, FALSE otherwise.
   */
  public static function validateOrder($order = NULL) {
    if (!$order) {
      return FALSE;
    }
    if (!($order instanceof OrderInterface)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Validates the order parameter .
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return bool
   *   TRUE if the #order value is valid, FALSE otherwise.
   */
  public static function isCartEmpty(OrderInterface $order = NULL) {
    if (!$order) {
      return TRUE;
    }
    if (empty($order->getItems())) {
      return TRUE;
    }
    return FALSE;
  }

}
