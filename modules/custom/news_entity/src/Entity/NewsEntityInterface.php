<?php

namespace Drupal\news_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining News entities.
 *
 * @ingroup news_entity
 */
interface NewsEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the News name.
   *
   * @return string
   *   Name of the News.
   */
  public function getName();

  /**
   * Sets the News name.
   *
   * @param string $name
   *   The News name.
   *
   * @return \Drupal\news_entity\Entity\NewsEntityInterface
   *   The called News entity.
   */
  public function setName($name);

  /**
   * Gets the News creation timestamp.
   *
   * @return int
   *   Creation timestamp of the News.
   */
  public function getCreatedTime();

  /**
   * Sets the News creation timestamp.
   *
   * @param int $timestamp
   *   The News creation timestamp.
   *
   * @return \Drupal\news_entity\Entity\NewsEntityInterface
   *   The called News entity.
   */
  public function setCreatedTime($timestamp);

}
