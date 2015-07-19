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

  public static $modules = array('key', 'encrypt');

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
    $enc_string = \Drupal::getContainer()->get('encryption')->encrypt('test');
    $this->assertEqual($enc_string, 'encrypted text', 'The encryption service is not properly processing');

    // Test decryption service.
    $dec_string = \Drupal::getContainer()->get('encryption')->decrypt($enc_string);
    $this->assertEqual($dec_string, 'test', 'The decryption service is not properly processing');

  }
}
