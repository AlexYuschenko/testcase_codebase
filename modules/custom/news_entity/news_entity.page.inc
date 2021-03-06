<?php

/**
 * @file
 * Contains news_entity.page.inc.
 *
 * Page callback for News entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for News templates.
 *
 * Default template: news_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_news_entity(array &$variables) {
  // Fetch NewsEntity Entity Object.
  $news_entity = $variables['elements']['#news_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
