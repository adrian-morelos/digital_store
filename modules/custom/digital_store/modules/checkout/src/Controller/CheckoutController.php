<?php

namespace Drupal\digital_store_checkout\Controller;

use Drupal\Core\Form\FormState;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormAjaxException;
use Drupal\Core\Session\AccountInterface;
use Drupal\digital_store_cart\CartSession;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\digital_store_order\Entity\Order;
use Drupal\Core\Form\EnforcedResponseException;
use Drupal\digital_store_cart\CartSessionInterface;
use Drupal\digital_store_cart\CartProviderInterface;
use Drupal\digital_store_order\Entity\OrderInterface;
use Drupal\digital_store_checkout\Form\CheckoutPaymentForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\digital_store_checkout\CheckoutFlowStepsInterface;

/**
 * Provides the checkout form page.
 */
class CheckoutController extends ControllerBase {

  /**
   * The cart provider.
   *
   * @var \Drupal\digital_store_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The cart session.
   *
   * @var \Drupal\digital_store_cart\CartSessionInterface
   */
  protected $cartSession;

  /**
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\digital_store_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\digital_store_cart\CartSessionInterface $cart_session
   *   The cart session.
   */
  public function __construct(FormBuilderInterface $form_builder, CartProviderInterface $cart_provider, CartSessionInterface $cart_session) {
    $this->formBuilder = $form_builder;
    $this->cartProvider = $cart_provider;
    $this->cartSession = $cart_session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('digital_store_cart.cart_provider'),
      $container->get('digital_store_cart.cart_session')
    );
  }

  /**
   * Redirect the user to the right checkout flows step ID.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response object
   */
  public function checkoutRedirect() {
    /** @var \Drupal\digital_store_order\Entity\OrderInterface $order */
    $cart = $this->cartProvider->getCart();
    if (!$cart) {
      // Redirect to Cart URL.
      return $this->redirect('digital_store_cart.cart_page');
    }
    $item = $cart->get('checkout_flow_step');
    if (empty($item)) {
      // Redirect to Cart URL.
      return $this->redirect('digital_store_cart.cart_page');
    }
    $step_id = $item->value;
    if (empty($step_id)) {
      // Redirect to Cart URL.
      return $this->redirect('digital_store_cart.cart_page');
    }
    $route_name = "digital_store_checkout.{$step_id}";
    // Redirect to right step ID.
    return $this->redirect($route_name, ['order' => $cart->id()]);
  }

  /**
   * Builds and processes the form provided by the order's checkout flow.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
   *   The render form, otherwise a redirect response object
   */
  public function checkoutPage(RouteMatchInterface $route_match) {
    /** @var \Drupal\digital_store_order\Entity\OrderInterface $order */
    $node = $route_match->getParameter('order');
    $requested_step_id = $route_match->getParameter('step');
    $order = new Order($node);
    $step_id = $this->getCheckoutStepId($order);
    if (empty($step_id)) {
      // Redirect to Cart URL.
      return $this->redirect('digital_store_cart.cart_page');
    }
    if ($step_id == CheckoutFlowStepsInterface::SHOPPING_CART) {
      // Redirect to Cart URL.
      return $this->redirect('digital_store_cart.cart_page');
    }
    if ($step_id != $requested_step_id) {
      $route_name = "digital_store_checkout.{$step_id}";
      // Redirect to right step ID.
      return $this->redirect($route_name, ['order' => $order->id()]);
    }
    if ($step_id == CheckoutFlowStepsInterface::PAYMENT_INFORMATION) {
      return $this->getPaymentPage($order);
    }
    if ($step_id == CheckoutFlowStepsInterface::COMPLETED) {
      return $this->getOrderReceivedPage($order);
    }
    // Redirect to Cart URL.
    return $this->redirect('digital_store_cart.cart_page');
  }

  /**
   * Builds the Order Received render element.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface
   *   The cart order.
   *
   * @return array
   *   The render element.
   */
  public function getOrderReceivedPage(OrderInterface $order) {
    return [
      '#type' => 'checkout_completion_message_step',
      '#order' => $order,
    ];
  }

  /**
   * Builds the Checkout payment form.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface
   *   The cart order.
   *
   * @return array
   *   The render form.
   */
  public function getPaymentPage(OrderInterface $order) {
    // Add order to the form state.
    $form_state = (new FormState())->setFormState([
      'order' => $order,
    ]);
    try {
      $form = $this->formBuilder->buildForm(CheckoutPaymentForm::class, $form_state);
    }
    catch (EnforcedResponseException $e) {
      $form = [];
    }
    catch (FormAjaxException $e) {
      $form = [];
    }
    return $form;
  }

  /**
   * Gets the order's checkout step ID.
   *
   * Ensures that the user is allowed to access the requested step ID,
   * when given. In case the requested step ID is empty, invalid, or
   * not allowed, a different step ID will be returned.
   *
   * @param \Drupal\digital_store_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return string|bool
   *   The checkout step ID, if the other have no a valid step id return FALSE.
   */
  public function getCheckoutStepId(OrderInterface $order) {
    // Customers can't edit orders that have already been placed.
    if ($order->getState() != 'draft') {
      return CheckoutFlowStepsInterface::COMPLETED;
    }
    $item = $order->get('checkout_flow_step');
    if (empty($item)) {
      return FALSE;
    }
    return $item->value;
  }

  /**
   * Checks access for the form page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\digital_store_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('order');
    if ($order->getState() == 'canceled') {
      return AccessResult::forbidden()->addCacheableDependency($order);
    }

    // The user can checkout only their own non-empty orders.
    if ($account->isAuthenticated()) {
      $customer_check = $account->id() == $order->getCustomerId();
    }
    else {
      $active_cart = $this->cartSession->hasCartId($order->id(), CartSession::ACTIVE);
      $completed_cart = $this->cartSession->hasCartId($order->id(), CartSession::COMPLETED);
      $customer_check = $active_cart || $completed_cart;
    }

    $access = AccessResult::allowedIf($customer_check)
      ->andIf(AccessResult::allowedIf($order->hasItems()))
      ->addCacheableDependency($order->getEntity());

    return $access;
  }


}