<?php

namespace Drupal\news_entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for News edit forms.
 *
 * @ingroup news_entity
 */
class NewsEntityForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Multi steps of the form.
   *
   * @var int
   */
  protected $step;

  /**
   * Boolean indicating whether the entity form is multistep.
   *
   * @var bool
   */
  protected $multistep;

  /**
   * Constructs a new NewsEntityForm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, AccountProxyInterface $account) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\news_entity\Entity\NewsEntity $entity */
    if ($this->entity->isNew()) {
      if (!$form_state->has('entity_form_initialized')) {
        $this->init($form_state);
      }

      $this->step = $form_state->get('page_num') ?: 1;
      $this->multistep = FALSE;

      $form_display = 'news_entity.news_entity.step_' . $this->step;
      if ($entity_form_display = EntityFormDisplay::load($form_display)) {
        $this->multistep = TRUE;
        $this->setFormDisplay($entity_form_display, $form_state);
      }

      $form = parent::buildForm($form, $form_state);

      if ($this->multistep) {
        if ($this->step == 1) {
          $form_state->set('page_num', 1);
          $form['actions']['submit']['#value'] = $this->t('Next');
        }
        else {
          $form['actions']['back'] = [
            '#type' => 'submit',
            '#value' => $this->t('Back'),
            '#submit' => ['::pageBackSubmit'],
            '#limit_validation_errors' => [],
          ];
        }
      }

      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($form_state->has('page_num') && $form_state->get('page_num') == 1) {
      $form_state->set('page_num', 2)->setRebuild(TRUE);
    }
    else {
      $entity = $this->entity;

      $status = parent::save($form, $form_state);

      switch ($status) {
        case SAVED_NEW:
          $this->messenger()->addMessage($this->t('Created the %label News.', [
            '%label' => $entity->label(),
          ]));
          break;

        default:
          $this->messenger()->addMessage($this->t('Saved the %label News.', [
            '%label' => $entity->label(),
          ]));
      }
      $form_state->setRedirect('entity.news_entity.canonical', ['news_entity' => $entity->id()]);
    }
  }

  /**
   * Provides custom submission handler for 'Back' button (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageBackSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      ->set('page_num', 1)
      ->setRebuild(TRUE);
  }

}
