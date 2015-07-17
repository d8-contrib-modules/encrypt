<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Drupal\key\KeyManager;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
class EncryptService {

  /**
   * Drupal\key\KeyManager definition.
   *
   * @var Drupal\key\KeyManager
   */
  protected $key_manager;
  /**
   * Constructor.
   */
  public function __construct(KeyManager $key_manager) {
    $this->key_manager = $key_manager;
  }

  /**
   * Main encrypt function.
   *
   * @param string $text
   *  The plain text to encrypt.
   *
   * return string
   *  The encrypted string.
   */
  function encrypt($text) {
    // Get settings.
    $settings = \Drupal::config('encrypt.settings');

    // Load the key.
    $key = $this->key_manager->getKey($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = \Drupal::getContainer()->get('encrypt.encryption')->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->encrypt($text, $key->getContents());
  }

  /**
   * Main decrypt function.
   *
   * @param string $text
   *  The encrypted text to decrypt.
   *
   * return string
   *  The decrypted plain string.
   */
  function decrypt($text) {
    // Get settings.
    $settings = \Drupal::config('encrypt.settings');

    // Load the key.
    $key = $this->key_manager->getKey($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = \Drupal::getContainer()->get('encrypt.encryption')->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->decrypt($text, $key->getContents());
  }
}
