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
   * @return string
   *   The plugin ID of the selected EncryptionMethod plugin.
   */
  public function getEncryptionMethod();

  /**
   * Gets the encryption profile key.
   *
   * @return string
   *   The ID of the selected Key entity.
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
