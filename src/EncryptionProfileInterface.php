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
   * Gets the encryption configurations key.
   *
   * @return \Drupal\encrypt\EncryptionMethodInterface
   */
  public function getEncryptionMethod();

  /**
   * Gets if this encryption configuration is the default.
   *
   * @return boolean
   */
  public function getServiceDefault();

  /**
   * Sets the encryption configuration to be the default.
   *
   */
  public function setServiceDefault();
}
