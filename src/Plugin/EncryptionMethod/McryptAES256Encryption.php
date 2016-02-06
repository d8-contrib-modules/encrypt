<?php

/**
 * @file
 * Contains \Drupal\encrypt\Plugin\EncryptionMethod\McryptAES256Encryption.
 */

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\encrypt\EncryptionMethodInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class McryptAES256Encryption.
 *
 * @package Drupal\encrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "mcrypt_aes_256",
 *   title = @Translation("Mcrypt AES 256"),
 *   description = "This uses PHPs mcrypt extension and <a href='http://en.wikipedia.org/wiki/Advanced_Encryption_Standard'>AES-256</a>.",
 *   key_types = {"aes_encryption"}
 * )
 */
class McryptAES256Encryption extends PluginBase implements EncryptionMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function checkDependencies($text = NULL, $key = NULL) {
    $errors = array();

    if (!function_exists('mcrypt_encrypt')) {
      $errors[] = t('MCrypt library not installed.');
    }

    if (!empty($key) and !in_array(strlen($key), [16, 24, 32])) {
      $errors[] = t('Key length must be 16, 24, or 32 characters long.');
    }

    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function encrypt($text, $key, $options = array()) {
    $processed_text = '';

    // Key cannot be too long for this encryption.
    $key = \Drupal\Component\Utility\Unicode::substr($key, 0, 32);

    // Define iv cipher.
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    $processed_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);

    // Check if we are disabling base64 encoding.
    if (!$disable_base64) {
      $processed_text = base64_encode($processed_text);
    }

    return $processed_text;
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $key, $options = array()) {
    $processed_text = '';

    // Key cannot be too long for this encryption.
    $key = \Drupal\Component\Utility\Unicode::substr($key, 0, 32);

    // Define iv cipher.
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    // Check if we are disabling base64 encoding.
    if (!$disable_base64) {
      $text = base64_decode($text);
    }

    // Decrypt text.
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv));
  }

}
