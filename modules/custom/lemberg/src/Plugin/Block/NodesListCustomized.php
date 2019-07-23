<?php

namespace Drupal\lemberg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of nodes by specified content type.
 *
 * @Block(
 *   id = "lemberg_nodes_list_customized",
 *   admin_label = @Translation("Nodes List (Customized)"),
 * )
 */
class NodesListCustomized extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs the block using dependency injection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'node_count' => 1,
      'node_content_type' => 'article',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $all_content_types = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();
    $node_types = [];
    foreach ($all_content_types as $machine_name => $content_type) {
      $node_types[$machine_name] = $content_type->label();
    }

    $form['node_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Nodes count'),
      '#description' => $this->t('How many Nodes to display in block.'),
      '#default_value' => $this->configuration['node_count'],
    ];
    $form['node_content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Node Content type'),
      '#description' => $this->t('Select Content type to display only Nodes of that type.'),
      '#default_value' => $this->configuration['node_content_type'],
      '#options' => $node_types,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['node_count'] = $form_state->getValue('node_count');
    $this->configuration['node_content_type'] = $form_state->getValue('node_content_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $nodes = $this->getNodeList();

    $content = [];
    foreach ($nodes as $key => $node) {
      $content[] = [
        '#markup' => $node->toLink()->toString(),
        '#type' => 'item',
      ];
    }

    return [
      '#markup' => $this->renderer->render($content),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  private function getNodeList() {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $nids = $node_storage->getQuery()
      ->condition('type', $this->configuration['node_content_type'])
      ->sort('created', 'DESC')
      ->pager($this->configuration['node_count'])
      ->execute();
    $nodes = $node_storage->loadMultiple($nids);

    return $nodes;
  }

}
