<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptionMethodInterface.
 */

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface EncryptionMethodInterface.
 *
 * @package Drupal\encrypt
 */
interface EncryptionMethodInterface extends PluginInspectionInterface {

  /**
   * Encrypt text with the given key.
   *
   * @return mixed
   *   The encrypted text.
   */
  public function encrypt($text, $key);

  /**
   * Decrypt text with the given key.
   *
   * @return mixed
   *   The decrypted text.
   */
  public function decrypt($text, $key);

  /**
   * Enforce dependencies for this encryption method.
   *
   * @return array
   *   An array of error messages.
   */
  public function checkDependencies($text = NULL, $key = NULL);

}
