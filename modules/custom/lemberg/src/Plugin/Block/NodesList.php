<?php

namespace Drupal\lemberg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of nodes grouped by content type.
 *
 * @Block(
 *   id = "lemberg_nodes_list",
 *   admin_label = @Translation("Nodes List"),
 * )
 */
class NodesList extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
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
  public function build() {
    $nodes = $this->getNodeList();

    $content = [];
    foreach ($nodes as $key => $node) {
      $node_link = $node->toLink()->toString();
      $content[] = [
        '#markup' => $node_link . ' (' . $node->bundle() . ')',
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
    $storage = $this->entityTypeManager->getStorage('node');
    $nids = $storage->getQuery()->sort('type')->execute();
    $nodes = $storage->loadMultiple($nids);
    return $nodes;
  }

}
