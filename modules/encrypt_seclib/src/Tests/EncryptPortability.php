<?php
namespace Drupal\encrypt\Tests;

/**
 * Test to make sure an encryption array carries its encryption method and key provider with it to ensure portability.
 * 
 * @group encrypt
 */
class EncryptPortability extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'Encryption Portability tests',
      'description' => 'Test to make sure an encryption array carries its encryption method and key provider with it to ensure portability.',
      'group' => 'Encrypt',
    ];
  }

  public function setUp() {
    parent::setUp('encrypt');
  }

  public /**
   * Ensure that a method and key provider are stored with an encrypted value.
   */
  function testMethodAndKeyProviderPortability() {
    // Generate some text to encrypt and encrypt it.
    $text = $this->randomName(10);
    $encrypted = encrypt($text, [], 'default', 'drupal_private_key');
    $encrypted_array = unserialize($encrypted);

    $this->assertEqual($encrypted_array['method'], 'default', t('Encryption method is stored with an encrypted value.'));
    $this->assertEqual($encrypted_array['key_provider'], 'drupal_private_key', t('Key provider is stored with an encrypted value.'));
  }

  public /**
   * Test off-the-cuff decrypting of a value using decrypt() with some text and paramters.
   */
  function testDecryptingRandomValue() {
    // Generate some text and encrypt it.
    $text = $this->randomName(10);
    $encrypted = encrypt($text, [], 'default', 'drupal_private_key');
    $encrypted_array = unserialize($encrypted);

    // First, just check to see that the value was actually encrypted.
    $this->assertNotEqual($text, $encrypted_array['text'], t('The value was actually encrypted.'));

    // Attempt to decrypt it without using the encryption array.
    $decrypted = decrypt($encrypted_array['text'], [], 'default', 'drupal_private_key');
    $this->assertEqual($text, $decrypted, t('The value was successfully decrypted.'));
  }

  public /**
   * Test decrypting when only an encryption method is provided (no key provider).
   *
   * We are likely to encounter this when sites upgrade from 1.x to 2.x, since key providers
   * did not exist in 1.x.
   */
  function testDecryptWithoutKeyProvider() {
    // Generate some text and encrypt it.
    $text = $this->randomName(10);
    $encrypted = encrypt($text);

    // Now, we'll manually remove the key provider array key and reserialize.
    $encrypted_array = unserialize($encrypted);
    $this->assertTrue(isset($encrypted_array['key_provider']), t('The key provider key exists in the encrypted array.'));
    unset($encrypted_array['key_provider']);
    $this->assertEqual(count($encrypted_array), 5, t('The key provider was successfully unset.'));
    $encrypted = serialize($encrypted_array);

    // Now, we'll attempt to decrypt.
    $decrypted = decrypt($encrypted);
    $this->assertEqual($decrypted, $text, t('The value was successfully decrypted.'));
  }

}
