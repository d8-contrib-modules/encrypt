<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\key\KeyRepository;

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
   * @var \Drupal\key\KeyRepository
   */
  protected $key;


  /**
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   * @param \Drupal\encrypt\EncryptionMethodManager $manager
   * @param \Drupal\key\KeyRepository $key
   */
  public function __construct(EntityManagerInterface $entityManager, EncryptionMethodManager $encryptManager, KeyRepository $key) {
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
  public function encrypt($text, $inst_id) {

    // Load the profile.
    $enc_profile = $this->loadEncryptionProfile($inst_id);

    // Load the method.
    $enc_method = $this->loadEncryptionMethod($enc_profile);

    // Load the key.
    $key_value = $this->loadEncryptionProfileKey($enc_profile);

    // Check for missing dependencies.
    $errors = $enc_method->checkDependencies($text, $key_value);
    if (empty($errors)) {
      // Return the encrypted string.
      return $enc_method->encrypt($text, $key_value);
    } else {
      // Throw an exception with the errors noted
      throw new \Exception(implode('; ', $errors));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $inst_id) {

    // Load the profile.
    $enc_profile = $this->loadEncryptionProfile($inst_id);

    // Load the method.
    $enc_method = $this->loadEncryptionMethod($enc_profile);

    // Load the key.
    $key_value = $this->loadEncryptionProfileKey($enc_profile);

    // Check for missing dependencies.
    $errors = $enc_method->checkDependencies($text, $key_value);
    if (empty($errors)) {
      // Return the encrypted string.
      return $enc_method->decrypt($text, $key_value);
    } else {
      // Throw an exception with the errors noted
      throw new \Exception(implode('; ', $errors));
    }
  }

  /**
   * Loads an encryption profile instance.
   * @param string $inst_id
   */
  private function loadEncryptionProfile($inst_id) {
    /** @var $enc_profile \Drupal\encrypt\Entity\EncryptionProfile */
    if (!$enc_profile = $this->entityManager->getStorage('encryption_profile')
        ->load($inst_id)) {
      throw new \Exception('Encryption profile was not found.');
    }

    return $enc_profile;
  }

  /**
   * Loads an encryption profile method.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $enc_profile
   */
  private function loadEncryptionMethod($enc_profile) {
    // Load the encryption method.
    $enc_method = $enc_profile->getEncryptionMethod();
    return $this->encryptManager->createInstance($enc_method);
  }

  /**
   * Loads an encryption profile key.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $enc_profile
   */
  private function loadEncryptionProfileKey($enc_profile) {
    // Load the key.
    $key_id = $enc_profile->getEncryptionKey();
    return $this->key->getKey($key_id)->getKeyValue();
  }
}
