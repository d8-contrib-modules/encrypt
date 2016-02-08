<?php

/**
 * @file
 * Contains Drupal\encrypt\Entity\EncryptionConfiguration.
 */

namespace Drupal\encrypt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\encrypt\EncryptionProfileInterface;
use Drupal\encrypt\Exception\KeyNotAllowedException;

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
 *     "canonical" = "/admin/config/system/encryption/profile/{encryption_profile}",
 *     "add-form" = "/admin/config/system/encryption/profile/add",
 *     "edit-form" = "/admin/config/system/encryption/profile/{encryption_profile}/edit",
 *     "delete-form" = "/admin/config/system/encryption/profile/{encryption_profile}/delete",
 *     "collection" = "/admin/config/system/encryption/profile",
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
  public function getEncryptionKey() {
    return $this->encryption_key;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $errors = $this->validate();
    if (!empty($errors)) {
      throw new KeyNotAllowedException(implode(';', $errors));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = [];
    $allowed_keys = array_keys($this->getAllowedKeys());
    if (empty($allowed_keys)) {
      $errors[] = t('No valid keys found for the selected encryption method');
    }
    else {
      if (!in_array($this->getEncryptionKey(), $allowed_keys)) {
        $errors[] = t('The selected key cannot be used with the selected encryption method.');
      }
    }
    return $errors;
  }

  /**
   * Get a list of allowed keys for the given encryption method.
   *
   * @param string $encryption_method
   *   The selected encryption method.
   * @return array
   *   A list of allowed keys.
   */
  public function getAllowedKeys($encryption_method = NULL) {
    $allowed_keys = [];
    if (!$encryption_method) {
      $encryption_method = $this->getEncryptionMethod();
    }
    $encryption_method_definition = static::getEncryptionMethodManager()->getDefinition($encryption_method);

    /** @var $key \Drupal\key\KeyInterface */
    foreach ($this->getKeyRepository()->getKeys() as $key) {
      $key_type = $key->getKeyType();
      $key_type_definition = $this->getKeyManager()->getDefinition($key_type->getPluginId());

      // @TODO: remove this check and only get Keys of type EncryptionKeyType or child classes.
      // This still needs to be implemented in Key module first.
      // Don't allow keys with key types other than encryption.
      if ($key_type_definition['group'] != "encryption") {
        continue;
      }

      // Don't allow keys with incorrect sizes.
      if (isset($encryption_method_definition['key_size'])) {
        $allowed_key_sizes = $encryption_method_definition['key_size'];
        $key_type_config = $key_type->getConfiguration();
        if (!isset($key_type_config['key_size']) || !$this->validKeySize($key_type_config['key_size'], $allowed_key_sizes)) {
          continue;
        }
      }

      // Don't allow keys with incorrect key_type, if defined in the encryption
      // method definition.
      if (isset($encryption_method_definition['key_type']) && !empty($encryption_method_definition['key_type'])) {
        if (!in_array($key_type->getPluginId(), $encryption_method_definition['key_type'])) {
          continue;
        }
      }

      $key_id = $key->id();
      $key_title = $key->label();
      $allowed_keys[$key_id] = (string) $key_title;
    }
    return $allowed_keys;
  }

  /**
   * Validates if key size matches the allowed values of the encryption method.
   *
   * @param mixed $key_size
   *   The key size as defined by the Key type.
   * @param array $allowed_key_sizes
   *   The allowed key sizes as defined by the encryption method.
   * @return bool
   *   Whether or not the key size is valid.
   */
  protected function validKeySize($key_size, array $allowed_key_sizes) {
    $valid = FALSE;
    // Make sure we're dealing with an integer.
    // Strips out optional "_bits" suffix from Key module.
    $key_size = (int) $key_size;

    if (!empty($allowed_key_sizes)) {
      foreach ($allowed_key_sizes as $allowed) {
        // Check if allowed key size is a range or not.
        if (strpos($allowed, '-') !== FALSE) {
          list($min, $max) = explode('-', $allowed, 2);
          if (($min <= $key_size) && ($key_size <= $max)) {
            $valid = TRUE;
          }
        }
        else {
          if ($allowed == $key_size) {
            $valid = TRUE;
          }
        }
      }
    }

    return $valid;
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

  /**
   * Gets the key manager service.
   *
   * @return \Drupal\key\Plugin\KeyPluginManager
   *   The Key manager service.
   */
  protected static function getKeyManager() {
    return \Drupal::service('plugin.manager.key.key_type');
  }
}
