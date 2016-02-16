<?php
/**
 * @file
 * Contains \Drupal\encrypt\EncryptionMethodInterface.
 */

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface for EncryptionMethod plugins.
 *
 * @package Drupal\encrypt
 */
interface EncryptionMethodInterface extends PluginInspectionInterface {

  /**
   * Encrypt text.
   *
   * @param string $text
   *   The text to be encrypted.
   * @param string $key
   *   The key to encrypt the text with.
   *
   * @return string
   *   The encrypted text
   */
  public function encrypt($text, $key);

  /**
   * Decrypt text.
   *
   * @param string $text
   *   The text to be decrypted.
   * @param string $key
   *   The key to decrypt the text with.
   *
   * @return string
   *   The decrypted text
   */
  public function decrypt($text, $key);

  /**
   * Check dependencies for the encryption method.
   *
   * @param string $text
   *   The text to be checked.
   * @param string $key
   *   The key to be checked.
   *
   * @return array
   *   An array of error messages, providing info on missing dependencies.
   */
  public function checkDependencies($text = NULL, $key = NULL);

  /**
   * Get the label.
   *
   * @return string
   *   The label for this EncryptionMethod plugin.
   */
  public function getLabel();

}
