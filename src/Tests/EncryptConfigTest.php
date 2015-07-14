<?php
namespace Drupal\encrypt\Tests;

/**
 * Test basic management of configuration, including adding, editing, and deleting.
 * 
 * @group encrypt
 */
class EncryptConfigTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  protected $privilegedUser;

  public static function getInfo() {
    return [
      'name' => 'Configuration Management',
      'description' => 'Test basic management of configuration, including adding, editing, and deleting.',
      'group' => 'Encrypt',
    ];
  }

  public /**
   * Enable encrypt module; create and log in privileged user.
   */
  function setUp() {
    parent::setUp('encrypt');

    $this->privilegedUser = $this->drupalCreateUser(['administer encrypt']);
    $this->drupalLogin($this->privilegedUser);
  }

  public /**
   * Test that the configuration table was created on install.
   *
   * The table should exist and a default configuration should have been
   * added.
   */
  function testConfigInstall() {
    // Test that the encrypt_config table was created.
    $this->assertTrue(db_table_exists('encrypt_config'), 'The table for storing configurations was created.');

    // Test that the default configuration was added and is enabled.
    $default_config = encrypt_get_default_config();
    $this->assertTrue($default_config['name'] == 'default', 'A default configuration was added.');
    $this->assertTrue($default_config['enabled'], 'The default configuration is enabled.');
  }

  public /**
   * Test configuration management.
   *
   * Ensure that a configuration can be added, loaded, edited, made the
   * default, and deleted.
   */
  function testConfigManage() {
    // Create the test configuration.
    $fields = [];
    $fields['label'] = t('Test');
    $fields['name'] = strtolower($fields['label']);
    $fields['description'] = t('This is the original description.');
    $fields['enabled'] = FALSE;
    $fields['encrypt_encryption_method'] = 'default';
    $fields['encrypt_key_provider'] = 'drupal_private_key';
    $this->drupalPost('admin/config/system/encrypt/add', $fields, t('Save configuration'));
    $this->assertText(t('The configuration @label has been added.', [
      '@label' => $fields['label']
      ]));

    // Load the test configuration.
    $config = encrypt_get_config($fields['name'], TRUE);
    $this->assertTrue($config['label'] == $fields['label'], format_string('The configuration @label was loaded.', [
      '@label' => $fields['label']
      ]));

    // Edit the test configuration.
    $edit_fields = $fields;
    unset($edit_fields['name']);
    $edit_fields['description'] = t('This is the edited description.');
    $this->drupalPost('admin/config/system/encrypt/edit/' . $fields['name'], $edit_fields, t('Save configuration'));
    $this->assertText(t('The configuration @label has been updated.', [
      '@label' => $fields['label']
      ]));

    // Make the test configuration the default.
    $this->drupalGet('admin/config/system/encrypt/default/' . $fields['name']);
    $this->assertText(t('The configuration @label has been made the default.', [
      '@label' => $fields['label']
      ]));
    $default_config = encrypt_get_default_config(TRUE);
    $this->assertTrue($default_config['name'] == $fields['name'], 'The test configuration is the default.');
    $test_config = encrypt_get_config($fields['name'], TRUE);
    $this->assertTrue($test_config['enabled'], 'The test configuration is enabled.');

    // Ensure that the default configuration cannot be deleted.
    $this->drupalGet('admin/config/system/encrypt/delete/' . $default_config['name']);
    $this->assertText(t('The default configuration cannot be deleted.'));

    // Make the test configuration not the default, then delete it.
    $this->drupalGet('admin/config/system/encrypt/default/default');
    $this->drupalGet('admin/config/system/encrypt/delete/' . $fields['name']);
    $this->drupalPost(NULL, [], t('Delete'));
    $this->assertText(t('The configuration @label has been deleted.', [
      '@label' => $fields['label']
      ]));
  }

  public /**
   * Test an encryption with just a configuration.
   */
  function testConfigEncrypt() {
    $config = encrypt_get_default_config(TRUE);

    $random = $this->randomName(10);
    $encrypted = encrypt($random, [], NULL, NULL, $config['name']);

    // Test that the original value does not equal the encrypted value
    // (i.e. that the data is actually being encrypted).
    $this->assertTrue(strpos($encrypted, 'a:') === 0, 'Config: The encrypted value is a serialized array.');
    $encryptedArray = unserialize($encrypted);
    $this->assertNotEqual($random, $encryptedArray['text'], 'Config: A value, encrypted, does not equal itself.');
    $this->assertEqual($encryptedArray['method'], 'default', 'Config: Encryption method stored correctly.');

    $decrypted = decrypt($encrypted, [], 'default');
    $this->assertEqual($random, $decrypted, 'Config: A value, decrypted, equals itself.');
  }

}
