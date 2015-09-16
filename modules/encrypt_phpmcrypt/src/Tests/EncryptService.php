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

  public static $modules = array('key', 'encrypt_seclib', 'dblog');

  /**
   * Test both encrypt and decrypt functions.
   */
  function testEncryptAndDecrypt() {

    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array('administer site configuration', 'administer encrypt'));
    $this->drupalLogin($user1);

    // Create new simple key.
    $this->drupalGet('admin/config/system/key/add');
    $edit = [
      'key_type' => 'key_type_simple',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_type');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'key_type' => 'key_type_simple',
      'key_settings[simple_key_value]' => 'test this key out',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));


    // Change encrypt settings.
    $edit = [
      'encryption_key' => 'testing_key',
      'encryption_method' => 'mcrypt_aes_256',
    ];
    $this->drupalPostForm('admin/config/security/encryption', $edit, t('Save configuration'));


    // Test encryption service.
    $test_string = 'testing 123 &*#';

    $this->verbose('Testing string: ' . $test_string);

    $enc_string = \Drupal::service('encryption')->encrypt($test_string);

    $this->verbose('Encrypted string: ' . $enc_string);

    $this->assertEqual($enc_string, 'n76uUVe8NGZsV2WES4NOJOiCYgtGtYq7tfpcykwfkmI=', 'The encryption service is not properly processing');

    // Test decryption service.
    $dec_string = \Drupal::service('encryption')->decrypt($enc_string);

    $this->verbose('Decrypted string: ' . $dec_string);

    $this->assertEqual($dec_string, $test_string, 'The decryption service is not properly processing');
  }
}
