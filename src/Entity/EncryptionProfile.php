<?php

/**
 * @file
 * Contains Drupal\encrypt\Entity\EncryptionConfiguration.
 */

namespace Drupal\encrypt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\encrypt\EncryptionProfileInterface;
use Drupal\encrypt\Exception\EncryptException;

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
 *   config_prefix = "profile",
 *   admin_permission = "administer encrypt",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/system/encryption/profiles/{encryption_profile}",
 *     "add-form" = "/admin/config/system/encryption/profiles/add",
 *     "edit-form" = "/admin/config/system/encryption/profiles/manage/{encryption_profile}",
 *     "delete-form" = "/admin/config/system/encryption/profiles/manage/{encryption_profile}/delete",
 *     "collection" = "/admin/config/system/encryption/profiles"
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
   * The encryption method, ID of EncryptionMethod plugin.
   *
   * @var string
   */
  protected $encryption_method;

  /**
   * The encryption key ID.
   *
   * @var string
   */
  protected $encryption_key;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $errors = $this->validate();
    if (!empty($errors)) {
      throw new EncryptException(implode(';', $errors));
    }
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
  public function getEncryptionKey() {
    return $this->encryption_key;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = [];

    if (!$this->getEncryptionMethod()) {
      $errors[] = t('No encryption method selected.');
    }

    if (!$this->getEncryptionKey()) {
      $errors[] = t('No encryption key selected');
    }

    $encryption_method_definition = static::getEncryptionMethodManager()->getDefinition($this->getEncryptionMethod());
    $allowed_key_types = $encryption_method_definition['key_type'];
    if (!empty($allowed_key_types)) {
      $selected_key = $this->getKeyRepository()->getKey($this->getEncryptionKey());
      $selected_key_type = $selected_key->getKeyType();
      if (!in_array($selected_key_type->getPluginId(), $allowed_key_types)) {
        $errors[] = t('The selected key cannot be used with the selected encryption method.');
      }
    }

    return $errors;
  }

  /**
   * Gets the encryption method manager.
   *
   * @return \Drupal\encrypt\EncryptionMethodManager
   *   The EncryptionMethodManager.
   */
  protected static function getEncryptionMethodManager() {
    return \Drupal::service('plugin.manager.encrypt.encryption_methods');
  }

  /**
   * Gets the key repository service.
   *
   * @return \Drupal\Key\KeyRepository
   *   The Key repository service.
   */
  protected static function getKeyRepository() {
    return \Drupal::service('key.repository');
  }

}
