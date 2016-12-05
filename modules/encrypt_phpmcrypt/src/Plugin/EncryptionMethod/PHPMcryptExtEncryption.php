<?php

namespace Drupal\encrypt_phpmcrypt\Plugin\EncryptionMethod;

use Drupal\encrypt\EncryptionMethodInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class PHPMcryptExtEncryption
 * @package Drupal\encrypt_phpmcrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "phpmcrypt",
 *   title = @Translation("PHP Mcrypt Extension (mcrypt)"),
 *   description = "Uses the <a href='http://php.net/manual/en/book.mcrypt.php'>mcrypt</a> PHP extension."
 * )
 */
class PHPMcryptExtEncryption extends PluginBase implements EncryptionMethodInterface {

  /**
   * @return mixed
   */
  public function encrypt($text, $key, $options = array()) {
    $processed_text = '';

    $disable_base64 = array_key_exists('base64', $options) && $options['base64'] == FALSE;

    $this->init_mcrypt($options,$key);

    $processed_text = mcrypt_generic($this->encryption_descriptor, $text);

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

    $this->init_mcrypt($options,$key);
    $processed_text = mdecrypt_generic($this->encryption_descriptor, $text);

    return trim($processed_text);
  }

  /**
   * @return mixed
   */
  public function getDependencies() {
    return array();
  }
  
  /**
   * Helper method to do setup for encrypt() and decrypt().
   */
  public function init_mcrypt($options, $key) {
    $this->algorithm = array_key_exists('algorithm', $options) ? $options['algorithm'] : '';
    $this->algorithm_directory = array_key_exists('algorithm_directory', $options) ? $options['algorithm_directory'] : '';
    $this->mode = array_key_exists('mode', $options) ? $options['mode'] : '';
    $this->mode_directory = array_key_exists('mode_directory', $options) ? $options['mode_directory'] : '';
    $this->encryption_descriptor = mcrypt_module_open($algorithm, $algorithm_directory, $mode, $mode_direcotry);
    
    $this->initialization_vector = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
    mcrypt_generic_init($this->encryption_descriptor, $key, $this->initialization_vector);
  }
  
  /**
   * Helper method to shut down the mcrypt extension.
   */
  public function shutdown_mcrypt() {
    mcrypt_generic_deinit($this->encryption_descriptor);
    mcrypt_generic_close($this->encryption_descriptor);
  }


  protected $algorithm;
  protected $algorithm_directory;
  protected $mode;
  protected $mode_directory;
  protected $encryption_descriptor;
  protected $initialization_vector;
}
