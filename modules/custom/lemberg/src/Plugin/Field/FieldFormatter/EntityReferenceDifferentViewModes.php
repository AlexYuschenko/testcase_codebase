<?php

namespace Drupal\lemberg\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementation of the 'entity reference different view modes' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_different_view_modes",
 *   label = @Translation("Entity Reference (Different View Modes)"),
 *   description = @Translation("Entity Reference (Different View Modes)"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceDifferentViewModes extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a EntityReferenceDifferentViewModes instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'first_view_mode' => 'default',
      'view_mode' => 'default',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $elements['first_view_mode'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('View mode for First element'),
      '#default_value' => $this->getSetting('first_view_mode'),
      '#required' => TRUE,
    ];
    $elements['view_mode'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('View mode for rest elements'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $view_modes = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $first_view_mode = $this->getSetting('first_view_mode');
    $summary[] = t('First element rendered as @mode', ['@mode' => isset($view_modes[$first_view_mode]) ? $view_modes[$first_view_mode] : $view_mode]);
    $view_mode = $this->getSetting('view_mode');
    $summary[] = t('Rest elements rendered as @mode', ['@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $first_view_mode = $this->getSetting('first_view_mode');
    $rest_view_mode = $this->getSetting('view_mode');
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      if (!isset($is_first_element)) {
        $view_mode = $first_view_mode;
        $is_first_element = TRUE;
      }
      else {
        $view_mode = $rest_view_mode;
      }
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()
        ->getId());
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that have a view
    // builder.
    $target_type = $field_definition->getFieldStorageDefinition()
      ->getSetting('target_type');

    return \Drupal::entityTypeManager()
      ->getDefinition($target_type)
      ->hasViewBuilderClass();
  }

}
