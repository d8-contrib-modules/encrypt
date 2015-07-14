<?php
namespace Drupal\encrypt\Tests;

/**
 * Test basic encrypting and decripting of a string.
 * 
 * @group encrypt
 */
class EncryptEncryptDecryptTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'Encrypt and Decrypt a String',
      'description' => 'Test basic encrypting and decripting of a string.',
      'group' => 'Encrypt',
    ];
  }

  public /**
   * Enable encrypt module.
   */
  function setUp() {
    parent::setUp('encrypt');
  }

  public /**
   * Test encryption and decryption with the "None" method.
   */
  function testNoneEncryptDecrypt() {
    // First, generate a random string to encrypt.
    $random = $this->randomName(10);

    // Encrypt the string.
    $encrypted = encrypt($random, [], 'none');
    $this->assertNotEqual($random, $encrypted, t('None: A value, encrypted, does not equal itself.'));
    $this->assertTrue(strpos($encrypted, 'a:') === 0, t('None: The encrypted value is a serialized array.'));

    // Since no actual encryption is being performed, ensure that the "encrypted" text is the same as the original.
    $encryptedArray = unserialize($encrypted);
    $this->assertEqual($random, $encryptedArray['text'], t('None: Initial value equals "encrypted" value.'));
    $this->assertEqual($encryptedArray['method'], 'none', t('None: Encryption method stored correctly.'));

    // Then, decrypt the encrypted string.
    $decrypted = decrypt($encrypted, [], 'none');
    $this->assertEqual($random, $decrypted, t('None: A value, decrypted, equals itself.'));
  }

  public /**
   * Test encryption and decryption with the "Basic" method.
   *
   * Pretty much the same as the "None" tests. See that method for more detailed comments.
   */
  function testBasicEncryptDecrypt() {
    $random = $this->randomName(10);
    $encrypted = encrypt($random, [], 'default');

    // Test that the original value does not equal the encrypted value (i.e. that the data is actually being encrypted).
    $this->assertTrue(strpos($encrypted, 'a:') === 0, t('Basic: The encrypted value is a serialized array.'));
    $encryptedArray = unserialize($encrypted);
    $this->assertNotEqual($random, $encryptedArray['text'], t('Basic: A value, encrypted, does not equal itself.'));
    $this->assertEqual($encryptedArray['method'], 'default', t('Basic: Encryption method stored correctly.'));

    $decrypted = decrypt($encrypted, [], 'default');
    $this->assertEqual($random, $decrypted, t('Basic: A value, decrypted, equals itself.'));
  }

  public /**
   * Test encryption and decryption with the "MCrypt" method.
   *
   * Pretty much the same as the "None" tests. See that method for more detailed comments.
   */
  function testMCryptEncryptDecrypt() {
    if (function_exists('mcrypt_encrypt')) {
      $random = $this->randomName(10);
      $encrypted = encrypt($random, [], 'mcrypt_rij_256');

      // Test that the original value does not equal the encrypted value (i.e. that the data is actually being encrypted).
      $this->assertTrue(strpos($encrypted, 'a:') === 0, t('MCrypt: The encrypted value is a serialized array.'));
      $encryptedArray = unserialize($encrypted);
      $this->assertNotEqual($random, $encryptedArray['text'], t('MCrypt: A value, encrypted, does not equal itself.'));
      $this->assertEqual($encryptedArray['method'], 'mcrypt_rij_256', t('MCrypt: Encryption method stored correctly.'));

      $decrypted = decrypt($encrypted, [], 'mcrypt_rij_256');
      $this->assertEqual($random, $decrypted, t('MCrypt: A value, decrypted, equals itself.'));
    }
    else {
      debug('MCrypt extension not present. Skipping tests.');
    }
  }

}
