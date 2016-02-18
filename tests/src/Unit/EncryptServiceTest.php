<?php
/**
 * @file
 * Contains Drupal\Tests\encrypt\Unit\EncryptServiceTest.
 */

namespace Drupal\Tests\encrypt\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\encrypt\EncryptService;

/**
 * Unit tests for EncryptService class.
 *
 * @ingroup encrypt
 *
 * @group encrypt
 *
 * @coversDefaultClass \Drupal\encrypt\EncryptService
 */
class EncryptServiceTest extends UnitTestCase {

  /**
   * A mocked EncryptionProfile entity.
   *
   * @var \Drupal\encrypt\Entity\EncryptionProfile|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $encryptionProfile;

  /**
   * A mocked EncryptionMethodManager.
   *
   * @var \Drupal\encrypt\EncryptionMethodManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $encryptManager;

  /**
   * A mocked KeyRepository.
   *
   * @var \Drupal\key\KeyRepository|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $keyRepository;

  /**
   * A mocked EncryptionMethod plugin.
   *
   * @var \Drupal\encrypt\EncryptionMethodInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $encryptionMethod;

  /**
   * A mocked Key entity.
   *
   * @var \Drupal\key\Entity\Key|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $key;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Set up a mock EncryptionProfile entity.
    $this->encryptionProfile = $this->getMockBuilder('\Drupal\encrypt\Entity\EncryptionProfile')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock EncryptionMethodManager.
    $this->encryptManager = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodManager')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock KeyRepository.
    $this->keyRepository = $this->getMockBuilder('\Drupal\key\KeyRepository')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock EncryptionMethod plugin.
    $this->encryptionMethod = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock Key entity.
    $this->key = $this->getMockBuilder('\Drupal\key\Entity\Key')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Tests loadEncryptionMethods method.
   *
   * @covers ::__construct
   * @covers ::loadEncryptionMethods
   */
  public function testLoadEncryptionMethods() {
    $definitions = array(
      'test_encryption_method' => array(
        'id' => 'test_encryption_method',
        'title' => "Test encryption method",
      ),
    );

    $this->encryptManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $service = new EncryptService(
      $this->encryptManager,
      $this->keyRepository
    );

    $methods = $service->loadEncryptionMethods();
    $this->assertEquals(['test_encryption_method'], array_keys($methods));
  }

  /**
   * Tests the encrypt & decrypt method.
   *
   * @covers ::__construct
   * @covers ::encrypt
   * @covers ::decrypt
   * @covers ::valid
   *
   * @dataProvider encryptionDataProvider
   */
  public function testEncryptDecrypt($key, $valid_key) {
    // Set up expectations for Key.
    $this->key->expects($this->any())
      ->method('getKeyValue')
      ->will($this->returnValue($key));

    if ($valid_key) {
      // Set up expectations for encryption method.
      $this->encryptionMethod->expects($this->once())
        ->method('encrypt')
        ->will($this->returnValue("encrypted_text"));
      $this->encryptionMethod->expects($this->once())
        ->method('decrypt')
        ->will($this->returnValue("decrypted_text"));

      // Set up expectations for encryption profile.
      $this->encryptionProfile->expects($this->any())
        ->method('getEncryptionKey')
        ->will($this->returnValue($this->key));
      $this->encryptionProfile->expects($this->any())
        ->method('getEncryptionMethod')
        ->will($this->returnValue($this->encryptionMethod));
      $this->encryptionProfile->expects($this->any())
        ->method('validate')
        ->will($this->returnValue(array()));
    }
    else {
      // Set up expectations for encryption profile.
      $this->encryptionProfile->expects($this->never())
        ->method('getEncryptionKey');
      $this->encryptionProfile->expects($this->never())
        ->method('getEncryptionMethod');
      $this->encryptionProfile->expects($this->any())
        ->method('validate')
        ->will($this->returnValue(array("Validation error")));
      $this->setExpectedException('\Drupal\encrypt\Exception\EncryptException');
    }

    $service = new EncryptService(
      $this->encryptManager,
      $this->keyRepository
    );

    $encrypted_text = $service->encrypt("text_to_encrypt", $this->encryptionProfile);
    $decrypted_text = $service->decrypt("text_to_decrypt", $this->encryptionProfile);
    if ($valid_key) {
      $this->assertEquals("encrypted_text", $encrypted_text);
      $this->assertEquals("decrypted_text", $decrypted_text);
    }
  }

  /**
   * Data provider for encrypt / decrypt method.
   *
   * @return array
   *   An array with data for the test method.
   */
  public function encryptionDataProvider() {
    return [
      'normal' => ["validkey", TRUE],
      'exception' => ["invalidkey", FALSE],
    ];
  }

}
