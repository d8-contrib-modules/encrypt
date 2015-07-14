<?php

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\encrypt\Annotation\EncryptionMethod;
use Drupal\encrypt\EncryptionMethodBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class NoEncryption
 * @package Drupal\encrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "no_encryption",
 *   title = @Translation("No Encryption"),
 *   description = "Do not encrypt"
 * )
 */
class NoEncryption extends PluginBase implements EncryptionMethodBaseInterface {

  /**
   * @return mixed
   */
  public function getDependencies() {
    return array();
  }

  /**
   * @return mixed
   */
  public function encrypt($text, $key, $options = array()) {
    return $text;
  }

  /**
   * @return mixed
   */
  public function decrypt($text, $key, $options = array()) {
    return $text;
  }
}
