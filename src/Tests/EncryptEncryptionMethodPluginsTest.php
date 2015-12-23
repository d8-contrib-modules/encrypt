<?php
namespace Drupal\encrypt\Tests;

/**
 * Test encryption method and key provider implementation.
 * 
 * @group encrypt
 */
class EncryptEncryptionMethodPluginsTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';
  public static $modules = ['encrypt', 'encrypt_seclib', 'key', 'dblog'];

  public static function getInfo() {
    return [
      'name' => 'Encryption Method and Key Providers Plugin tests',
      'description' => 'Test encryption method and key provider implementation.',
      'group' => 'Encrypt',
    ];
  }

  /**
   * Enable encrypt module.
   */
  public function setUp() {
    parent::setUp();
    $adminUser = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($adminUser);
  }

  /**
   * The declared encryption method appears on the add configuration page.
   */
  public function testPluginsAppearInList() {
    $this->drupalGet('admin/config/system/encryption/profile/add');
    // Check if the plugin exists.
    $this->assertOption('edit-encryption-method', 'phpseclib', t('Encryption method option is present.'));
    $this->assertText('PHP Secure Communications Library (phpseclib)', t('Encryption method text is present'));
  }

}
