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
   * Define key types this encryption method should be restricted to.
   *
   * Return an array of KeyType plugin IDs that restrict the allowed key types
   * for usage with this encryption method.
   *
   * The KeyType plugin IDs should refer to valid subclasses of
   * \Drupal\key\Plugin\KeyTypeBase.
   * For example "aes_encryption" or "authentication" as provided by the Key
   * module.
   */
  public $key_types = [];
}
