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
   * Returns the registered encryption method plugins.
   *
   * @return array
   *   List of encryption methods.
   */
  function loadEncryptionMethods() {
    $service = \Drupal::getContainer()->get('plugin.manager.encrypt.encryption_methods');

    return $service->getDefinitions();
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
    $key = \Drupal::getContainer()->get('key_manager')->getKey($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = \Drupal::getContainer()->get('plugin.manager.encrypt.encryption_methods')->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->encrypt($text, $key->getKeySettings());
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
    $key = \Drupal::getContainer()->get('key_manager')->getKey($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = \Drupal::getContainer()->get('plugin.manager.encrypt.encryption_methods')->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->decrypt($text, $key->getKeySettings());
  }
}
