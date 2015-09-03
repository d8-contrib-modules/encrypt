<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptionConfigurationInterface.
 */

namespace Drupal\encrypt;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Title entities.
 */
interface EncryptionConfigurationInterface extends ConfigEntityInterface {
  /**
   * Gets the encryption configurations key.
   *
   * @return \Drupal\key\Entity\Key
   */
  public function getEncryptionKey();

  /**
   * Gets the encryption configurations key.
   *
   * @return \Drupal\encrypt\EncryptionMethodInterface
   */
  public function getEncryptionMethod();
}
