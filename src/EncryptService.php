<?php
/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Drupal\key\KeyRepository;
use Drupal\encrypt\Entity\EncryptionProfile;
use Drupal\encrypt\Exception\EncryptException;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
class EncryptService implements EncryptServiceInterface {

  /**
   * The EncryptionMethod plugin manager.
   *
   * @var \Drupal\encrypt\EncryptionMethodManager
   */
  protected $encryptManager;

  /**
   * The KeyRepository.
   *
   * @var \Drupal\key\KeyRepository
   */
  protected $keyRepository;

  /**
   * The encryption method to use.
   *
   * @var \Drupal\encrypt\EncryptionMethodInterface
   */
  protected $encryptionMethod;


  /**
   * {@inheritdoc}
   *
   * @param \Drupal\encrypt\EncryptionMethodManager $encrypt_manager
   *   The EncryptionMethod plugin manager.
   * @param \Drupal\key\KeyRepository $key_repository
   *   The KeyRepository.
   */
  public function __construct(EncryptionMethodManager $encrypt_manager, KeyRepository $key_repository) {
    $this->encryptManager = $encrypt_manager;
    $this->keyRepository = $key_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function loadEncryptionMethods() {
    return $this->encryptManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function encrypt($text, EncryptionProfile $encryption_profile) {
    // Get the key and check dependencies.
    $key_value = $this->getEncryptionKeyValue($text, $encryption_profile);
    // Return the encrypted value.
    return $this->getEncryptionMethod()->encrypt($text, $key_value);
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, EncryptionProfile $encryption_profile) {
    // Get the key and check dependencies.
    $key_value = $this->getEncryptionKeyValue($text, $encryption_profile);
    // Return the encrypted value.
    return $this->getEncryptionMethod()->decrypt($text, $key_value);
  }

  /**
   * Get the used encryption method.
   *
   * @return \Drupal\encrypt\EncryptionMethodInterface
   *   The used encryption method.
   *
   * @codeCoverageIgnore
   */
  protected function getEncryptionMethod() {
    return $this->encryptionMethod;
  }

  /**
   * Get the used encryption key.
   *
   * @param string $text
   *   The text to encrypt / decrypt.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $encryption_profile
   *   The encryption profile to use.
   *
   * @return string
   *   The encryption key value.
   *
   * @throws \Drupal\encrypt\Exception\EncryptException
   *   Can throw an EncryptException.
   */
  protected function getEncryptionKeyValue($text, EncryptionProfile $encryption_profile) {
    // Load the encryption method.
    $this->encryptionMethod = $encryption_profile->getEncryptionMethod();

    // Load the encryption key.
    $key_value = $this->loadEncryptionProfileKey($encryption_profile);

    // Check for missing dependencies.
    $errors = $this->encryptionMethod->checkDependencies($text, $key_value);

    if (!empty($errors)) {
      // Throw an exception with the errors from the encryption method.
      throw new EncryptException(implode('; ', $errors));
    }
    else {
      return $key_value;
    }
  }

  /**
   * Loads an encryption profile key.
   *
   * @param \Drupal\encrypt\Entity\EncryptionProfile $encryption_profile
   *   The encryption profile to use.
   *
   * @return string
   *   The encryption key value.
   */
  protected function loadEncryptionProfileKey(EncryptionProfile $encryption_profile) {
    $key = $encryption_profile->getEncryptionKey();
    return $key->getKeyValue();
  }

}
