<?php

/**
 * @file
 * Definition of Drupal\encrypt_seclib\Tests\EncryptService.
 */

namespace Drupal\encrypt_seclib\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the encrypt service.
 *
 * @group encrypt
 */
class EncryptService extends WebTestBase {

  public static $modules = array('composer_manager', 'key', 'encrypt_seclib', 'dblog');
  protected $profile = 'standard';

  /**
   * Test both encrypt and decrypt functions.
   */
  function testEncryptAndDecrypt() {

    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user1);

    // Create new simple key.
    $this->drupalGet('admin/config/security/key/add');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'key_provider' => 'config',
      'key_settings[key_value]' => 'test this key out',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));


    // Change encrypt settings.
    $edit = [
      'id' => 'test_profile',
      'label' => 'test enc profile',
      'encryption_key' => 'testing_key',
      'encryption_method' => 'phpseclib',
    ];
    $this->drupalPostForm('admin/config/security/encryption/profile/add', $edit, t('Save'));


    // Test encryption service.
    $test_string = 'testing 123 &*#';

    $this->verbose('Testing string: ' . $test_string);

    $enc_string = \Drupal::service('encryption')->encrypt($test_string, 'test_profile');

    $this->verbose('Encrypted string: ' . $enc_string);

    $this->assertEqual($enc_string, 'xLAVoczg3Jl9JsL6U/uDhQ==', 'The encryption service is not properly processing');

    // Test decryption service.
    $dec_string = \Drupal::service('encryption')->decrypt($enc_string, 'test_profile');

    $this->verbose('Decrypted string: ' . $dec_string);

    $this->assertEqual($dec_string, $test_string, 'The decryption service is not properly processing');
  }
}
