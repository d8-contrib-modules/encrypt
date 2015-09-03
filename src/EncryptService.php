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
      /** @var $enc_config \Drupal\encrypt\Entity\EncryptionConfiguration */
      if (!$enc_config = $this->entityManager->getStorage('encryption_configuration')
        ->load($inst_id)) {
        return FALSE;
      }
    } else {
      // Load the default.
      /** @var $enc_config \Drupal\encrypt\Entity\EncryptionConfiguration */
      $enc_config = $this->entityManager->getStorage('encryption_configuration')
        ->loadByProperties(array('service_default' => TRUE));
    }

    // Load the key.
    $key_value = $this->key->getKeyValue($enc_config->getEncryptionKey());

    // Load the encryption method.
    $enc_method = $this->encryptManager->createInstance($enc_config->getEncryptionMethod());

    // Return the encrypted string.
    return $enc_method->encrypt($text, $key_value);
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $inst_id = NULL) {
    if ($inst_id) {
      /** @var $enc_config \Drupal\encrypt\Entity\EncryptionConfiguration */
      if (!$enc_config = $this->entityManager->getStorage('encryption_configuration')
        ->load($inst_id)) {
        return FALSE;
      }
    } else {
      // Load the default.
      /** @var $enc_config \Drupal\encrypt\Entity\EncryptionConfiguration */
      $enc_config = $this->entityManager->getStorage('encryption_configuration')
        ->loadByProperties(array('service_default' => TRUE));
    }

    // Load the key.
    $key_value = $this->key->getKeyValue($enc_config->getEncryptionKey());

    // Load the encryption method.
    $enc_method = $this->encryptManager->createInstance($enc_config->getEncryptionMethod());

    // Return the encrypted string.
    return $enc_method->decrypt($text, $key_value);
  }
}
