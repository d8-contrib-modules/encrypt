<?php

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\encrypt\Annotation\EncryptionMethod;
use Drupal\encrypt\EncryptionMethodBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class PHPSecLibEncryption
 * @package Drupal\encrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "phpseclib",
 *   title = @Translation("PHP Secure Communications Library (phpseclib)"),
 *   description = "Uses the <a href='http://phpseclib.sourceforge.net/'>phpseclib</a> library. This method is only preferable if you cannot install mcrypt."
 * )
 */
class PHPSecLibEncryption extends PluginBase implements EncryptionMethodBaseInterface {

  /**
   * @return mixed
   */
  public function getDependencies() {
    $errors = array();

    if (!\Drupal::moduleHandler()->moduleExists('libraries')) {
      $errors[] = t('You must download and enable the <a href="http://drupal.org/project/libraries">Libraries API</a> module.');
    }
    if (!file_exists('sites/all/libraries/phpseclib') && !file_exists(\Drupal\Core\DrupalKernel::findSitePath() . '/phpseclib')) {
      $errors[] = t('You must download the <a href="http://phpseclib.sourceforge.net/">phpseclib</a> library and place it in either sites/all/libraries or sites/yourdomain/libraries.');
    }

    return $errors;
  }

  /**
   * @return mixed
   */
  public function encrypt($text, $key, $options = array()) {
    $processed_text = '';

    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    if ($path = libraries_get_path('phpseclib')) {
      // Include the AES file and instantiate.
      require_once $path . '/Crypt/AES.php';
      $aes = new Crypt_AES();

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
      // Include the AES file and instantiate.
      require_once $path . '/Crypt/AES.php';
      $aes = new Crypt_AES();

      $aes->setKey($key);

      $processed_text = $aes->decrypt($text);
    }

    return trim($processed_text);
  }
}
