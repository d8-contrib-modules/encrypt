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
   * return string
   *  The encrypted string.
   */
  function encrypt($text);

  /**
   * Main decrypt function.
   *
   * @param string $text
   *  The encrypted text to decrypt.
   *
   * return string
   *  The decrypted plain string.
   */
  function decrypt($text);
}
