<?php

namespace Drupal\lemberg\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SubscriptionEvent.
 *
 * Event that is fired when user submit Subscription form.
 */
class SubscriptionEvent extends Event {

  /**
   * Name of the event fired when user Subscribe.
   */
  const SUBSCRIBE_USER = 'lemberg.subscribe_user';

}
