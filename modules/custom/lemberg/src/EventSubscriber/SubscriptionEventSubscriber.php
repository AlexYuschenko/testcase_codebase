<?php

namespace Drupal\lemberg\EventSubscriber;

use Drupal\lemberg\Event\SubscriptionEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SubscriptionEventSubscriber.
 *
 * @package Drupal\lemberg\EventSubscriber
 */
class SubscriptionEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Role ID for subscriber users.
   */
  const SUBSCRIBER_ROLE = 'subscriber';

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * SubscriptionEventSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountInterface $account, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->account = $account;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->roleStorage = $this->entityTypeManager->getStorage('user_role');
    $this->userStorage = $this->entityTypeManager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      SubscriptionEvent::SUBSCRIBE_USER => 'addSubscriberRole',
    ];
  }

  /**
   * Adds 'subscriber' role to current user.
   */
  public function addSubscriberRole(SubscriptionEvent $event) {
    // Create 'subscriber' role if not exists.
    if (!$this->roleStorage->load(self::SUBSCRIBER_ROLE)) {
      $role_data = [
        'id' => self::SUBSCRIBER_ROLE,
        'label' => ucfirst(self::SUBSCRIBER_ROLE),
      ];
      $this->roleStorage->create($role_data)->save();
    }

    $user = $this->userStorage->load($this->account->id());
    // Adds 'subscriber' role to current user if not exists.
    if ($user->hasRole(self::SUBSCRIBER_ROLE)) {
      $this->messenger->addMessage($this->t('Already subscribed.'));
    }
    else {
      $user->addRole(self::SUBSCRIBER_ROLE);
      $user->save();

      $this->messenger->addMessage($this->t('Successfully subscribed'));
    }

    $event->stopPropagation();
  }

}
