<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptionProfileInterface.
 */

namespace Drupal\encrypt;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Title entities.
 */
interface EncryptionProfileInterface extends ConfigEntityInterface {
  /**
   * Gets the encryption configuration method.
   *
   * @return \Drupal\encrypt\EncryptionMethodInterface
   */
  public function getEncryptionMethod();

  /**
   * Gets the encryption profile key.
   *
   * @return \Drupal\key\KeyInterface
   */
  public function getEncryptionKey();

  /**
   * Validate the EncryptionProfile entity.
   *
   * @return array
   *   An array of validation errors. Empty if no errors.
   */
  public function validate();

}
