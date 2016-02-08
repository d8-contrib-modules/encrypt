<?php

/**
 * @file
 * Contains \Drupal\encrypt\Annotation\EncryptionMethod.
 */

namespace  Drupal\encrypt\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a EncryptionMethod annotation object.
 *
 * @ingroup encrypt
 *
 * @Annotation
 */
class EncryptionMethod extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the encryption method.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * The description shown to users.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description = '';


  /**
   * The key type(s) this encryption method should use.
   *
   * If none specified, all keys within the group "encryption" will be
   * available to this encryption method.
   *
   * This setting should refer to a valid Plugin ID of type KeyType.
   * For example "aes_encryption", provided by the Key module.
   *
   * @var array
   */
  public $key_type = [];

  /**
   * The key sizes allowed to be used with this encryption method.
   *
   * Example values: "128_bits", "192_bits", "256_bits"
   *
   * @see \Drupal\key\KeyProvider\AesEncryptionKeyType
   *
   * @var array
   */
  public $key_size = [];

}
