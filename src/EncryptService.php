<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\key\KeyManager;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
class EncryptService implements EncryptServiceInterface {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\encrypt\EncryptionMethodManager
   */
  protected $encryptManager;

  /**
   * @var \Drupal\key\KeyManager
   */
  protected $key;


  /**
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   * @param \Drupal\encrypt\EncryptionMethodManager $manager
   * @param \Drupal\key\KeyManager $key
   */
  public function __construct(EntityManagerInterface $entityManager, EncryptionMethodManager $encryptManager, KeyManager $key) {
    $this->entityManager = $entityManager;
    $this->encryptManager = $encryptManager;
    $this->key = $key;
  }

  /**
   * {@inheritdoc}
   */
  public function loadEncryptionMethods() {
    return $this->encryptManager->getDefinitions();
  }


  /**
   * {@inheritdoc}.
   */
  public function encrypt($text, $inst_id = NULL) {

    if ($inst_id) {
      /** @var $enc_profile \Drupal\encrypt\Entity\EncryptionProfile */
      if (!$enc_profile = $this->entityManager->getStorage('encryption_profile')
        ->load($inst_id)) {
        return FALSE;
      }
    } else {
      // Load the default.
      $enc_profiles = $this->entityManager->getStorage('encryption_profile')
        ->loadByProperties(['service_default' => TRUE]);
      /** @var $enc_profile \Drupal\encrypt\Entity\EncryptionProfile */
      $enc_profile = array_shift($enc_profiles);
    }

    // Load the key.
    $key_id = $enc_profile->getEncryptionKey();
    if ($key_id != 'default') {
      $key_value = $this->key->getKeyValue($key_id);
    } else {
      $key_value = $this->key->getDefaultKeyValue();
    }

    // Load the encryption method.
    $enc_method = $enc_profile->getEncryptionMethod();
    $enc_method = $this->encryptManager->createInstance($enc_method);

    // Return the encrypted string.
    return $enc_method->encrypt($text, $key_value);
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $inst_id = NULL) {
    if ($inst_id) {
      /** @var $enc_profile \Drupal\encrypt\Entity\EncryptionProfile */
      if (!$enc_profile = $this->entityManager->getStorage('encryption_profile')
        ->load($inst_id)) {
        return FALSE;
      }
    } else {
      // Load the default.
      $enc_profiles = $this->entityManager->getStorage('encryption_profile')
        ->loadByProperties(array('service_default' => TRUE));
      /** @var $enc_profile \Drupal\encrypt\Entity\EncryptionProfile */
      $enc_profile = array_shift($enc_profiles);
    }

    // Load the key.
    $key_id = $enc_profile->getEncryptionKey();
    if ($key_id != 'default') {
      $key_value = $this->key->getKeyValue($key_id);
    } else {
      $key_value = $this->key->getDefaultKeyValue();
    }

    // Load the encryption method.
    // Load the encryption method.
    $enc_method = $enc_profile->getEncryptionMethod();
    $enc_method = $this->encryptManager->createInstance($enc_method);

    // Return the encrypted string.
    return $enc_method->decrypt($text, $key_value);
  }
}
