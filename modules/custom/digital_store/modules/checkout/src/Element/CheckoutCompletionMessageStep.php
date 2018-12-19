<?php

namespace Drupal\digital_store_checkout\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides the render element: Checkout completion message.
 *
 * @code
 * $build['checkout_completion_message'] = [
 *   '#type' => 'checkout_completion_message_step',
 *   '#order' => NULL,
 * ];
 * @endcode
 *
 * @RenderElement("checkout_completion_message_step")
 */
class CheckoutCompletionMessageStep extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'checkout_completion_message',
      '#pre_render' => [
        [$class, 'preRenderElement'],
      ],
      '#order' => NULL,
      '#attributes' => [
      ],
    ];
  }

  /**
   * Pre-render callback; Process custom attribute options.
   *
   * @param array $element
   *   The renderable array representing the element with '#type' => 'marquee'
   *   property set.
   *
   * @return array
   *   The passed in element with changes made to attributes depending on
   *   context.
   */
  public static function preRenderElement(array $element = []) {
    $element['#attributes']['class'][] = 'checkout-completion-message';
    return $element;
  }

}
