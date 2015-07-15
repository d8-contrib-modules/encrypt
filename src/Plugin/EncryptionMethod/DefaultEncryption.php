<?php

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\encrypt\Annotation\EncryptionMethod;
use Drupal\encrypt\EncryptionMethodBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class DefaultEncryption
 * @package Drupal\encrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "default_encryption",
 *   title = @Translation("Basic"),
 *   description = "This is the basic default encryption type that does not require any special extensions."
 * )
 */
class DefaultEncryption extends PluginBase implements EncryptionMethodBaseInterface {

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
    $processed_text = '';

    // Caching length operations to speed up for loops.
    $text_length = strlen($text);
    $key_length = strlen($key);

    // Loop through each character.
    for ($i = 0; $i < $text_length; $i++) {
      $char = substr($text, $i, 1);
      $keychar = substr($key, ($i % $key_length) - 1, 1);
      $char = chr(ord($char) + ord($keychar));
      $processed_text .= $char;
    }

    return $processed_text;
  }

  /**
   * @return mixed
   */
  public function decrypt($text, $key, $options = array()) {
    $processed_text = '';

    // Caching length operations to speed up for loops.
    $text_length = strlen($text);
    $key_length = strlen($key);

    // Loop through each character.
    for ($i = 0; $i < $text_length; $i++) {
      $char = substr($text, $i, 1);
      $keychar = substr($key, ($i % $key_length) - 1, 1);
      $char = chr(ord($char) - ord($keychar));
      $processed_text .= $char;
    }

    return $processed_text;
  }
}