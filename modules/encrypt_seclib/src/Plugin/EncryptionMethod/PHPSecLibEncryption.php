<?php

namespace Drupal\encrypt_seclib\Plugin\EncryptionMethod;

use Drupal\encrypt\EncryptionMethodInterface;
use Drupal\Core\Plugin\PluginBase;
use phpseclib\Crypt\AES;

/**
 * Class PHPSecLibEncryption
 * @package Drupal\encrypt_seclib\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "phpseclib",
 *   title = @Translation("PHP Secure Communications Library (phpseclib)"),
 *   description = "Uses the <a href='http://phpseclib.sourceforge.net/'>phpseclib</a> library. This method is only preferable if you cannot install mcrypt."
 * )
 */
class PHPSecLibEncryption extends PluginBase implements EncryptionMethodInterface {

  /**
   * @return mixed
   */
  public function encrypt($text, $key, $options = array()) {
    $processed_text = '';

    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    if ($path = libraries_get_path('phpseclib')) {
      $aes = new AES();
      $aes->setKey($key);
      $processed_text = $aes->encrypt($text);
    }

    // If base64 encoding is not disabled.
    if (!$disable_base64) {
      $processed_text = base64_encode($processed_text);
    }

    return trim($processed_text);
  }

  /**
   * @return mixed
   */
  public function decrypt($text, $key, $options = array()) {
    $processed_text = '';

    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    // If base64 encoding is not disabled.
    if (!$disable_base64) {
      $text = base64_decode($text);
    }

    if ($path = libraries_get_path('phpseclib')) {
      $aes = new AES();
      $aes->setKey($key);
      $processed_text = $aes->decrypt($text);
    }

    return trim($processed_text);
  }
}
