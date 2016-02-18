<?php

/**
 * @file
 * Drush integration for Encrypt module.
 */

use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Implements hook_drush_command().
 */
function encrypt_drush_command() {
  $items = array();

  $items['encrypt'] = array(
    'description' => "Encrypt text with the provided encryption profile.",
    'arguments' => array(
      'profile' => 'The machine name of the encryption profile to use.',
    ),
    'options' => array(
      'base64' => 'Output the encrypted text in base64 encoded format.',
    ),
    'examples' => array(
      "drush encrypt profile_name 'text to encrypt'" => 'Encrypts the given text with the specified encryption profile.',
      "drush encrypt --base64 profile_name 'text to encrypt'" => 'Encrypts the given text with the specified encryption profile and base64-encodes output.',
    ),
    'callback' => 'drush_encrypt_encrypt',
  );

  $items['decrypt'] = array(
    'description' => "Decrypt text with the provided encryption profile.",
    'arguments' => array(
      'profile' => 'The machine name of the encryption profile to use.',
    ),
    'options' => array(
      'base64' => 'Output the encrypted text in base64 encoded format.',
    ),
    'examples' => array(
      "drush decrypt profile_name 'text to decrypt'" => 'Decrypts the given text with the specified encryption profile.',
      "drush decrypt --base64 profile_name 'text to decrypt'" => 'Decrypts the given base64-encoded text with the specified encryption profile.',
    ),
    'callback' => 'drush_encrypt_decrypt',
  );

  return $items;
}

/**
 * Encrypt text with the given encryption profile.
 *
 * @param string $encryption_profile_name
 *   The encryption profile machine name.
 * @param string $text
 *   The text to encrypt.
 *
 * @return string
 *   The encrypted text.
 */
function drush_encrypt_encrypt($encryption_profile_name, $text) {
  $service = \Drupal::service('encryption');
  $encryption_profile = EncryptionProfile::load($encryption_profile_name);
  if (!$encryption_profile) {
    return drush_set_error('', dt('Encryption profile "@name" could not be loaded.', array(
      '@name' => $encryption_profile_name,
    )));
  }
  else {
    $encrypted_text = $service->encrypt($text, $encryption_profile);
    if (drush_get_option('base64')) {
      $encrypted_text = base64_encode($encrypted_text);
    }
    drush_print($encrypted_text);
  }
}

/**
 * Decrypt text with the given encryption profile.
 *
 * @param string $encryption_profile_name
 *   The encryption profile machine name.
 * @param string $text
 *   The text to decrypt.
 *
 * @return string
 *   The decrypted text.
 */
function drush_encrypt_decrypt($encryption_profile_name, $text) {
  $service = \Drupal::service('encryption');
  $encryption_profile = EncryptionProfile::load($encryption_profile_name);
  if (!$encryption_profile) {
    return drush_set_error('', dt('Encryption profile "@name" could not be loaded.', array(
      '@name' => $encryption_profile_name,
    )));
  }
  else {
    if (drush_get_option('base64')) {
      $text = base64_decode($text);
    }
    $decrypted_text = $service->decrypt($text, $encryption_profile);
    drush_print($decrypted_text);
  }
}
