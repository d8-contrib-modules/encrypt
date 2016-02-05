<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptionMethodInterface.
 */

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface EncryptionMethodInterface
 *
 * @package Drupal\encrypt
 */
interface EncryptionMethodInterface extends PluginInspectionInterface {

  /**
   * Encrypt text with the given key.
   *
   * @return mixed
   */
  public function encrypt($text, $key);

  /**
   * Decrypt text with the given key.
   *
   * @return mixed
   */
  public function decrypt($text, $key);

  /**
   * Enforce dependencies for this encryption method.
   *
   * Return an array of error messages in case one or more dependencies
   * are not met. Return an empty array if everything is OK.
   *
   * @return array
   */
  public function checkDependencies($text = NULL, $key = NULL);
}
