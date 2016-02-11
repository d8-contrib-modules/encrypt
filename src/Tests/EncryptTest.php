<?php

/**
 * @file
 * Contains Drupal\encrypt\Tests\EncryptServiceTest.
 */

namespace Drupal\encrypt\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the encrypt admin UI and encryption / decryption service.
 *
 * @group encrypt
 */
class EncryptTest extends WebTestBase {

  /**
   * Exempt from strict schema checking.
   *
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   *
   * @var bool
   */
  // @TODO: remove if https://www.drupal.org/node/2666196 is fixed.
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = array('key', 'encrypt', 'encrypt_test');

  /**
   * An administrator user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;


  /**
   * A test key.
   *
   * @var \Drupal\key\Entity\Key
   */
  protected $testKey;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer encrypt',
      'administer keys',
    ]);
    $this->drupalLogin($this->adminUser);
    $this->createTestKey();
  }

  /**
   * Creates a test key for usage in the tests.
   */
  protected function createTestKey() {
    $this->drupalGet('admin/config/system/keys/add');
    $edit = [
      'key_type' => 'encryption',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_type');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'key_type' => "encryption",
      'key_type_settings[key_size]' => '128',
      'key_provider' => 'config',
      'key_input_settings[key_value]' => 'mustbesixteenbit',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->testKey = \Drupal::service('key.repository')->getKey('testing_key');
    $this->assertTrue($this->testKey, 'Key was succesfully saved.');
  }

  /**
   * Test adding an encryption profile and encrypting / decrypting with it.
   */
  public function testEncryptAndDecrypt() {
    // Create an encryption profile config entity.
    $this->drupalGet('admin/config/system/encryption/profiles/add');

    // Check if the plugin exists.
    $this->assertOption('edit-encryption-method', 'test_encryption_method', t('Encryption method option is present.'));
    $this->assertText('Test Encryption method', t('Encryption method text is present'));

    $edit = [
      'encryption_method' => 'test_encryption_method',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'encryption_method');

    $edit = [
      'id' => 'test_encryption_profile',
      'label' => 'Test encryption profile',
      'encryption_method' => 'test_encryption_method',
      'encryption_key' => $this->testKey->id(),
    ];
    $this->drupalPostForm('admin/config/system/encryption/profiles/add', $edit, t('Save'));

    $encryption_profile = \Drupal::service('entity.manager')->getStorage('encryption_profile')->load('test_encryption_profile');
    $this->assertTrue($encryption_profile, 'Encryption profile was succesfully saved.');

    // Test the encryption service with our encryption profile.
    $test_string = 'testing 123 &*#';
    $enc_string = \Drupal::service('encryption')->encrypt($test_string, $encryption_profile);
    $this->assertEqual($enc_string, 'zhfgorfvkgrraovggrfgvat 123 &*#', 'The encryption service is not properly processing');

    // Test the decryption service with our encryption profile.
    $dec_string = \Drupal::service('encryption')->decrypt($enc_string, $encryption_profile);
    $this->assertEqual($dec_string, $test_string, 'The decryption service is not properly processing');
  }

  /**
   * Tests validation of encryption profiles.
   */
  public function testProfileValidation() {
    // Create an encryption profile config entity.
    $this->drupalGet('admin/config/system/encryption/profiles/add');

    // Check if the plugin exists.
    $this->assertOption('edit-encryption-method', 'test_encryption_method', t('Encryption method option is present.'));
    $this->assertText('Test Encryption method', t('Encryption method text is present'));

    $edit = [
      'encryption_method' => 'test_encryption_method',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'encryption_method');

    $edit = [
      'id' => 'test_encryption_profile',
      'label' => 'Test encryption profile',
      'encryption_method' => 'test_encryption_method',
      'encryption_key' => 'testing_key',
    ];
    $this->drupalPostForm('admin/config/system/encryption/profiles/add', $edit, t('Save'));

    // Now delete the testkey.
    $this->testKey->delete();

    // Check if the error message is shown.
    $this->drupalGet('admin/config/system/encryption/profiles');
    $this->assertText('The key linked to this encryption profile does not exist.');

    // Test "check_profile_status" setting.
    $this->config('encrypt.settings')
      ->set('check_profile_status', FALSE)
      ->save();
    $this->drupalGet('admin/config/system/encryption/profiles');
    $this->assertNoText('The key linked to this encryption profile does not exist.');
  }

}
