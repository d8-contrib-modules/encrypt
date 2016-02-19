<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptionProfileManagerInterface.
 */

namespace Drupal\encrypt;

/**
 * Provides an interface defining an EncryptionProfile manager.
 */
interface EncryptionProfileManagerInterface {

  /**
   * Get EncryptionProfile entities by encryption method plugin ID.
   *
   * @param string $encryption_method_id
   *   The plugin ID of the EncryptionMethod.
   *
   * @return \Drupal\encrypt\EncryptionProfileInterface[]
   *   An array of EncryptionProfile entities.
   */
  public function getEncryptionProfilesByEncryptionMethod($encryption_method_id);

  /**
   * Get EncryptionProfile entities by encryption Key entity ID.
   *
   * @param string $key_id
   *   The plugin ID of the EncryptionMethod.
   *
   * @return \Drupal\encrypt\EncryptionProfileInterface[]
   *   An array of EncryptionProfile entities.
   */
  public function getEncryptionProfilesByEncryptionKey($key_id);

}
