<?php

/**
 * @file
 * Definition of Drupal\encrypt\Tests\EncryptService.
 */

namespace Drupal\encrypt\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the encrypt service.
 *
 * @group encrypt
 */
class EncryptService extends WebTestBase {

  public static $modules = array('key', 'encrypt', 'dblog');

  /**
   * Test both encrypt and decrypt functions.
   */
  function testEncryptAndDecrypt() {

    // Create user with permission to create policy.
    $adminUser = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($adminUser);

    // Create new simple key.
    $this->drupalGet('admin/config/system/keys/add');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'key_provider' => 'config',
      'key_provider_settings[key_value]' => 'mustbesixteenbit',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));


    // Make encrypt provider.
    $edit = [
      'id' => 'test_encrypt_provider',
      'label' => 'test enc provider',
      'encryption_key' => 'testing_key',
      'encryption_method' => 'mcrypt_aes_256',
    ];
    $this->drupalPostForm('admin/config/system/encryption/profiles/add', $edit, t('Save'));


    // Test encryption service.
    $test_string = 'testing 123 &*#';

    $this->verbose('Testing string: ' . $test_string);

    $enc_string = \Drupal::service('encryption')->encrypt($test_string, 'test_encrypt_provider');

    $this->verbose('Encrypted string: ' . $enc_string);

    $this->assertEqual($enc_string, 'MFevzSmMAS/kQSrVhSFiBIc2sDCee/UbQHcnguCXPJ8=', 'The encryption service is not properly processing');

    // Test decryption service.
    $dec_string = \Drupal::service('encryption')->decrypt($enc_string, 'test_encrypt_provider');

    $this->verbose('Decrypted string: ' . $dec_string);

    $this->assertEqual($dec_string, $test_string, 'The decryption service is not properly processing');
  }
}
