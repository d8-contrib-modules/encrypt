<?php

/**
 * @file
 * Contains Drupal\encrypt\KeyType\AesEncryptionKeyType.
 */

namespace Drupal\encrypt\Plugin\KeyType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\key\Plugin\KeyType\EncryptionKeyType;
use Drupal\key\Plugin\KeyPluginFormInterface;

/**
 * Adds a key type for AES encryption.
 *
 * @KeyType(
 *   id = "aes_encryption",
 *   label = @Translation("AES Encryption"),
 *   description = @Translation("Used for encrypting and decrypting data with the Advanced Encryption Standard (AES) cipher."),
 *   group = "encryption",
 *   key_value = {
 *     "plugin" = "text_field"
 *   }
 * )
 */
class AesEncryptionKeyType extends EncryptionKeyType implements KeyPluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'key_size' => 256,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['key_size'] = array(
      '#type' => 'select',
      '#title' => $this->t('Key size'),
      '#description' => $this->t('The size of the key in bits. 128 bits is 16 bytes.'),
      '#options' => [128 => 128, 192 => 192, 256 => 256],
      '#default_value' => $this->getConfiguration()['key_size'],
      '#required' => TRUE,
    );

    return $form;
  }

}
