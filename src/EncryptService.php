<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Drupal\key\KeyManager;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
class EncryptService implements EncryptServiceInterface {

  /**
   * @var \Drupal\encrypt\EncryptionMethodManager
   */
  protected $manager;

  /**
   * @var \Drupal\key\KeyManager
   */
  protected $key;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * @param \Drupal\encrypt\EncryptionMethodManager $manager
   * @param \Drupal\key\KeyManager $key
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(EncryptionMethodManager $manager, KeyManager $key, ConfigFactoryInterface $config) {
    $this->manager = $manager;
    $this->key = $key;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  function loadEncryptionMethods() {
    return $this->manager->getDefinitions();
  }


  /**
   * {@inheritdoc}.
   */
  function encrypt($text) {
    // Get settings.
    $settings = $this->config->get('encrypt.settings');
    // Load the key.
    $key_value = $this->key->getKeyValue($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = $this->manager->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->encrypt($text, $key_value);
  }

  /**
   * {@inheritdoc}
   */
  function decrypt($text) {
    // Get settings.
    $settings = $this->config->get('encrypt.settings');

    // Load the key.
    $key = $this->key->getKey($settings->get('encryption_key'));

    // Load the encryption method.
    $enc_method = $this->manager->createInstance($settings->get('encryption_method'));

    // Return the encrypted string.
    return $enc_method->decrypt($text, $key->getKeyValue());
  }
}
