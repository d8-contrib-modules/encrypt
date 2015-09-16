<?php
namespace Drupal\encrypt_seclib\Tests;

/**
 * Test encryption method and key provider implementation.
 * 
 * @group encrypt
 */
class EncryptEncryptionMethodPluginsTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'Encryption Method and Key Providers Plugin tests',
      'description' => 'Test encryption method and key provider implementation.',
      'group' => 'Encrypt',
    ];
  }

  public /**
   * Enable encrypt module.
   */
  function setUp() {
    parent::setUp('encrypt_seclib');
    $adminUser = $this->drupalCreateUser(['administer encrypt']);
    $this->drupalLogin($adminUser);
  }

  public /**
   * The declared encryption method appears on the add configuration page.
   */
  function testPluginsAppearInList() {
    $this->drupalGet('admin/config/system/encrypt/add');
    $this->assertText('Test Method', t('Encryption method name is present.'));
    $this->assertText('This is just a test encryption method.', t('Encryption method description is present.'));
    $this->assertText('Test Key Provider', t('Key provider name is present.'));
    $this->assertText('This is just a test key provider.', t('Key provider description is present.'));
  }

  public /**
   * Test that plugins cannot be enabled if dependencies are not met.
   */
  function testPluginDependencies() {
    // First, set the variable to trigger our unmet dependency.
    \Drupal::config('encrypt.settings')->set('encrypt_test_trigger_unmet_deps', TRUE)->save();

    // Then make sure dependency errors appear on the page, and the method
    // cannot be enabled.
    $this->drupalGet('admin/config/system/encrypt/add');
    $this->assertText('This is an unmet dependency.');
    $this->assertFieldByXPath('//input[@name="encrypt_encryption_method" and @value="test" and @disabled]');

    // Now disable the unmet dependency and make sure all is OK. Note that this
    // should also implicitly test the plugin cache-clearing mechanism.
    \Drupal::config('encrypt.settings')->set('encrypt_test_trigger_unmet_deps', FALSE)->save();
    $this->drupalGet('admin/config/system/encrypt');
    $this->assertNoText('This is an unmet dependency.');
    $this->assertNoFieldByXPath('//input[@name="encrypt_encryption_method" and @value="test" and @disabled]');
  }

}
