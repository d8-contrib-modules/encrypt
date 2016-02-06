<?php
/**
 * @file
 * Contains Drupal\Tests\encrypt\Unit\Plugin/EncryptionMethod/McryptAES256EncryptionTest.
 */

namespace Drupal\Tests\encrypt\Unit\Plugin\EncryptionMethod;

use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for McryptAES256Encryption Plugin.
 *
 * @ingroup encrypt
 *
 * @group encrypt
 *
 * @coversDefaultClass \Drupal\encrypt\Plugin\EncryptionMethod\McryptAES256Encryption
 *
 * @requires extension mcrypt
 */
class McryptAES256EncryptionTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests checkDependencies method.
   *
   * @covers ::__construct
   * @covers ::checkDependencies
   *
   * @dataProvider dependenciesDataProvider
   */
  public function testCheckDependencies($key, $expected) {
    $encryption_method = $this->getMockBuilder('\Drupal\encrypt\Plugin\EncryptionMethod\McryptAES256Encryption')
      ->setMethods([
        'decrypt',
        'encrypt',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $errors = $encryption_method->checkDependencies("some_text", $key);
    $this->assertEquals($expected, $errors);
  }

  /**
   * Data provider for checkDependencies method.
   *
   * @return array
   *   An array with keys to test, and matching errors return array.
   */
  public function dependenciesDataProvider() {
    return [
      'valid_key' => ["mustbesixteenbit", []],
      'invalid_key' => ["invalidkey", [t('Key length must be 16, 24, or 32 characters long.')]],
    ];
  }

  /**
   * Tests the encrypt method.
   *
   * @covers ::__construct
   * @covers ::encrypt
   *
   * @dataProvider encryptDataProvider
   */
  public function testEncrypt($plaintext, $key, $expected) {
    $encryption_method = $this->getMockBuilder('\Drupal\encrypt\Plugin\EncryptionMethod\McryptAES256Encryption')
      ->setMethods([
        'decrypt',
        'checkDependencies',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $encrypted_text = $encryption_method->encrypt($plaintext, $key, array());
    $this->assertEquals($expected, $encrypted_text);
  }

  /**
   * Tests the decrypt method.
   *
   * @covers ::__construct
   * @covers ::decrypt
   *
   * @dataProvider encryptDataProvider
   */
  public function testDecrypt($expected, $key, $encrypted) {
    $encryption_method = $this->getMockBuilder('\Drupal\encrypt\Plugin\EncryptionMethod\McryptAES256Encryption')
      ->setMethods([
        'encrypt',
        'checkDependencies',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $decrypted_text = $encryption_method->decrypt($encrypted, $key, array());
    $this->assertEquals($expected, $decrypted_text);
  }

  /**
   * Data provider for encrypt / decrypt methods.
   *
   * @return array
   *   Array containing plain text, key and encrypted data.
   */
  public function encryptDataProvider() {
    return [
      [
        'unencryptedtext',
        'themcrypttestkey',
        'k0dT0DzFAbyoO8K5/KMu0Ow0HpHgV1oprno5ACDfldg=',
      ],
      [
        'testing 123 &*#',
        'mustbesixteenbit',
        'MFevzSmMAS/kQSrVhSFiBIc2sDCee/UbQHcnguCXPJ8=',
      ],
    ];
  }

}
