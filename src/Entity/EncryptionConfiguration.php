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
 *       "delete" = "Drupal\encrypt\Form\EncryptionConfigurationDeleteForm"
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
 *     "delete-form" = "/admin/structure/encryption_configuration/{encryption_configuration}/delete"
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
  protected $encryptionMethod;

  /**
   * The encryption key, id of Key entity.
   *
   * @var string
   */
  protected $encryptionKey;

  /**
   * If the configuration is to be the default encryption config used for the
   * service.
   *
   * @var boolean
   */
  protected $serviceDefault;

  /**
   * {@inheritdoc}
   */
  public function getEncryptionKey() {
    return $this->encryptionKey;
  }

  /**
   * {@inheritdoc}
   */
  public function getEncryptionMethod() {
    return $this->encryptionMethod;
  }
}
