<?php

namespace Drupal\digital_store_analytics\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\digital_store_analytics\AnalyticsClientInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Event subscriber subscribing to KernelEvents::REQUEST.
 */
class TrackingSubscriber implements EventSubscriberInterface {

  /**
   * The GA Analytics Client.
   *
   * @var \Drupal\digital_store_analytics\AnalyticsClientInterface
   */
  protected $analyticsClient;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\digital_store_analytics\AnalyticsClientInterface $analytics_client
   *   The GA Analytics Client.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current account.
   */
  public function __construct(AnalyticsClientInterface $analytics_client, AccountInterface $account) {
    $this->analyticsClient = $analytics_client;
    $this->account = $account;
  }

  /**
   * Send tracking to GA.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The subscribed event.
   */
  public function sendTracking(GetResponseEvent $event) {
    if ($this->account->isAuthenticated()) {
      return;
    }
    $uuid = \Drupal::service('uuid');
    $cid = $uuid->generate();
    $dl = Url::fromRoute('<current>', [], ['absolute' => 'true']);
    $parameters = [
      'cid' => $cid,
      't' => 'pageview',
      'dl' => $dl->toString(),
      'uip' => \Drupal::request()->getClientIp(),
    ];
    $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? NULL;
    if ($http_user_agent) {
      $parameters['ua'] = $http_user_agent;
    }
    $this->analyticsClient->sendHit($parameters);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['sendTracking'];
    return $events;
  }

}
