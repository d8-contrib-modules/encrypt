<?php
/**
 * @file
 * Contains \Drupal\encrypt\EncryptServiceInterface.php.
 */

namespace Drupal\encrypt;

use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
interface EncryptServiceInterface {

  /**
   * Returns the registered encryption method plugins.
   *
   * @return array
   *   List of encryption methods.
   */
  public function loadEncryptionMethods();

  /**
   * Main encrypt function.
   *
   * @param string $text
   *   The plain text to encrypt.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $encryption_profile
   *   The encryption profile entity.
   *
   * @return string
   *   The encrypted string.
   *
   * @throws \Drupal\encrypt\Exception\EncryptException
   *   Can throw an EncryptException.
   */
  public function encrypt($text, EncryptionProfile $encryption_profile);

  /**
   * Main decrypt function.
   *
   * @param string $text
   *   The encrypted text to decrypt.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $encryption_profile
   *   The encryption profile entity.
   *
   * @return string
   *   The decrypted plain string.
   *
   * @throws \Drupal\encrypt\Exception\EncryptException
   *   Can throw an EncryptException.
   */
  public function decrypt($text, EncryptionProfile $encryption_profile);

}
