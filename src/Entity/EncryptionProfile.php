<?php

/**
 * @file
 * Contains Drupal\encrypt\Entity\EncryptionConfiguration.
 */

namespace Drupal\encrypt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\encrypt\EncryptionProfileInterface;

/**
 * Defines the Title entity.
 *
 * @ConfigEntityType(
 *   id = "encryption_profile",
 *   label = @Translation("Encryption Profile"),
 *   handlers = {
 *     "list_builder" = "Drupal\encrypt\Controller\EncryptionProfileListBuilder",
 *     "form" = {
 *       "add" = "Drupal\encrypt\Form\EncryptionProfileForm",
 *       "edit" = "Drupal\encrypt\Form\EncryptionProfileForm",
 *       "delete" = "Drupal\encrypt\Form\EncryptionProfileDeleteForm",
 *       "default" = "Drupal\encrypt\Form\EncryptionProfileDefaultForm"
 *     }
 *   },
 *   config_prefix = "encryption_profile",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/security/encryption/profile/{encryption_profile}",
 *     "edit-form" = "/admin/config/security/encryption/profile/{encryption_profile}/edit",
 *     "delete-form" = "/admin/config/security/encryption/profile/{encryption_profile}/delete",
 *     "collection" = "/admin/config/security/encryption/profile",
 *     "set-default" = "/admin/config/security/encryption/profile/{encryption_profile}/default",
 *   }
 * )
 */
class EncryptionProfile extends ConfigEntityBase implements EncryptionProfileInterface {
  /**
   * The encryption profile ID.
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
   * If the profile is to be the default encryption config used for the
   * service.
   *
   * @var boolean
   */
  protected $service_default;

  /**
   * The encryption key.
   *
   * @var string
   */
  protected $encryption_key;

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
  public function getEncryptionKey() {
    return $this->encryption_key;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceDefault() {
    $entities = \Drupal::entityManager()
      ->getStorage('encryption_profile')
      ->loadByProperties(['service_default'=>TRUE]);
    foreach ($entities as $entity) {
      $entity->service_default = FALSE;
      $entity->save();
    }

    $this->service_default = TRUE;
    $this->save();
  }
}
