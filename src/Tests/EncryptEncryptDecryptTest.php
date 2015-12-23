<?php
namespace Drupal\encrypt\Tests;

/**
 * Test basic encrypting and decripting of a string.
 * 
 * @group encrypt
 */
class EncryptEncryptDecryptTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';
  public static $modules = array('encrypt', 'key', 'dblog');

  public static function getInfo() {
    return [
      'name' => 'Encrypt and Decrypt a String',
      'description' => 'Test basic encrypting and decripting of a string.',
      'group' => 'Encrypt',
    ];
  }

  protected function stageTest() {
    // Stage a key and make default.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/key/add');
    $edit = [
    'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
    'id' => 'testing_key',
    'label' => 'Testing Key',
    'key_provider' => 'config',
    'key_settings[key_value]' => 'mustbesixteenbit',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Setup an initial encryption profile and default it.
    $this->drupalGet('admin/config/system/encryption/profile/add');
    $edit = [
      'id' => 'testing_profile',
      'label' => 'Testing profile',
      'encryption_key' => 'testing_key',
      'encryption_method' => 'mcrypt_aes_256',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
  }

  /**
   * Test encryption and decryption with the "MCrypt" method.
   *
   * Pretty much the same as the "None" tests. See that method for more detailed comments.
   */
  public function testMCryptEncryptDecrypt() {
    if (function_exists('mcrypt_encrypt')) {
      $this->stageTest();

      // Run encrypt test.
      $srv = \Drupal::service('encryption');
      $random = $this->randomString(10);
      $encrypted = $srv->encrypt($random, 'testing_profile');

      // Test that the original value does not equal the encrypted value (i.e. that the data is actually being encrypted).
      $this->assertNotEqual($random, $encrypted, t('MCrypt: A value, encrypted, does not equal itself.'));

      $decrypted = $srv->decrypt($encrypted, 'testing_profile');
      $this->assertEqual($random, $decrypted, t('MCrypt: A value, decrypted, equals itself.'));

    }
    else {
      debug('MCrypt extension not present. Skipping tests.');
    }
  }

}
