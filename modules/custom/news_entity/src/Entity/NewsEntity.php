<?php

namespace Drupal\news_entity\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the News entity.
 *
 * @ingroup news_entity
 *
 * @ContentEntityType(
 *   id = "news_entity",
 *   label = @Translation("News"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\news_entity\NewsEntityListBuilder",
 *     "views_data" = "Drupal\news_entity\Entity\NewsEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\news_entity\Form\NewsEntityForm",
 *       "add" = "Drupal\news_entity\Form\NewsEntityForm",
 *       "edit" = "Drupal\news_entity\Form\NewsEntityForm",
 *       "delete" = "Drupal\news_entity\Form\NewsEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\news_entity\NewsEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\news_entity\NewsEntityAccessControlHandler",
 *   },
 *   base_table = "news_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer news entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/news_entity/{news_entity}",
 *     "add-form" = "/admin/structure/news_entity/add",
 *     "edit-form" = "/admin/structure/news_entity/{news_entity}/edit",
 *     "delete-form" = "/admin/structure/news_entity/{news_entity}/delete",
 *     "collection" = "/admin/structure/news_entity",
 *   },
 *   field_ui_base_route = "news_entity.settings"
 * )
 */
class NewsEntity extends ContentEntityBase implements NewsEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the News entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the News is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
