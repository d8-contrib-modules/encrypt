<?php

/**
 * @file
 * Contains Drupal\encrypt\Entity\EncryptionConfiguration.
 */

namespace Drupal\encrypt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\encrypt\EncryptionConfigurationInterface;

/**
 * Defines the Title entity.
 *
 * @ConfigEntityType(
 *   id = "encryption_configuration",
 *   label = @Translation("Encryption Configuration"),
 *   handlers = {
 *     "list_builder" = "Drupal\encrypt\Controller\EncryptionConfigurationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\encrypt\Form\EncryptionConfigurationForm",
 *       "edit" = "Drupal\encrypt\Form\EncryptionConfigurationForm",
 *       "delete" = "Drupal\encrypt\Form\EncryptionConfigurationDeleteForm",
 *       "default" = "Drupal\encrypt\Form\EncryptionConfigurationDefaultForm"
 *     }
 *   },
 *   config_prefix = "encryption_configuration",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/encryption_configuration/{encryption_configuration}",
 *     "edit-form" = "/admin/structure/encryption_configuration/{encryption_configuration}/edit",
 *     "delete-form" = "/admin/structure/encryption_configuration/{encryption_configuration}/delete",
 *     "collection" = "/admin/structure/encryption_configuration",
 *     "set-default" = "/admin/structure/encryption_configuration/{encryption_configuration}/default",
 *   }
 * )
 */
class EncryptionConfiguration extends ConfigEntityBase implements EncryptionConfigurationInterface {
  /**
   * The encryption configuration ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The label.
   *
   * @var string
   */
  protected $label;

  /**
   * The encryption method, id of EncryptionMethod plugin.
   *
   * @var \Drupal\encrypt\EncryptionMethodInterface
   */
  protected $encryption_method;

  /**
   * The encryption key, id of Key entity.
   *
   * @var string
   */
  protected $encryption_key;

  /**
   * If the configuration is to be the default encryption config used for the
   * service.
   *
   * @var boolean
   */
  protected $service_default;

  /**
   * {@inheritdoc}
   */
  public function getEncryptionKey() {
    return $this->encryption_key;
  }

  /**
   * {@inheritdoc}
   */
  public function getEncryptionMethod() {
    return $this->encryption_method;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceDefault() {
    return $this->service_default;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceDefault() {
    $entities = \Drupal::entityManager()
      ->getStorage('encryption_configuration')
      ->loadByProperties(['service_default'=>TRUE]);
    foreach ($entities as $entity) {
      $entity->service_default = FALSE;
      $entity->save();
    }

    $this->service_default = TRUE;
    $this->save();
  }
}
