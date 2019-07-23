<?php

namespace Drupal\lemberg;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Class EntityNameRetriever.
 */
class EntityNameRetriever {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * EntityNameRetriever constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserFromRoute() {
    $user = $this->routeMatch->getParameter('user');

    if ($user instanceof UserInterface) {
      return $user->getDisplayName();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeFromRoute() {
    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface) {
      return $node->getTitle();
    }

    return NULL;
  }

}
