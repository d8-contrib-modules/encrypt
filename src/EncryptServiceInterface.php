<?php
/**
 * @file
 * Contains \Drupal\encrypt\EncryptServiceInterface.php
 */
namespace Drupal\encrypt;


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
  function loadEncryptionMethods();

  /**
   * Main encrypt function.
   *
   * @param string $text
   *  The plain text to encrypt.
   *
   * @param string $instance_id
   *  The encryption profile ID linked to Drupal\encrypt\Entity\EncryptionProfile.
   *
   * return string
   *  The encrypted string.
   */
  function encrypt($text, $instance_id);

  /**
   * Main decrypt function.
   *
   * @param string $text
   *  The encrypted text to decrypt.
   *
   * @param string $instance_id
   *  The encryption profile ID linked to Drupal\encrypt\Entity\EncryptionProfile.
   *
   * return string
   *  The decrypted plain string.
   */
  function decrypt($text, $instance_id);
}
