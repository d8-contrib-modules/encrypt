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
    if ($this->validate($text, $encryption_profile)) {
      $key = $encryption_profile->getEncryptionKey();
      return $encryption_profile->getEncryptionMethod()->encrypt($text, $key->getKeyValue());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, EncryptionProfile $encryption_profile) {
    if ($this->validate($text, $encryption_profile)) {
      $key = $encryption_profile->getEncryptionKey();
      return $encryption_profile->getEncryptionMethod()->decrypt($text, $key->getKeyValue());
    }
  }

  /**
   * Determines whether the input is valid for encryption / decryption.
   *
   * @param $text
   *   The text to encrypt / decrypt.
   * @param \Drupal\encrypt\Entity\EncryptionProfile $encryption_profile
   *   The encryption profile to validate.
   *
   * @return bool
   *   Whether the encryption profile validated correctly.
   *
   * @throws \Drupal\encrypt\Exception\EncryptException
   *   Error with validation failures.
   */
  protected function validate($text, EncryptionProfile $encryption_profile) {
    $errors = $encryption_profile->validate($text);
    if (!empty($errors)) {
      // Throw an exception with the errors from the encryption method.
      throw new EncryptException(implode('; ', $errors));
    }
    return TRUE;
  }

}
