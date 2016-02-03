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
  }

  /**
   * Tests loadEncryptionMethods method.
   *
   * @covers ::__construct
   * @covers ::loadEncryptionMethods
   */
  public function testLoadEncryptionMethods() {
    $definitions = array(
      'mcrypt_aes_256_test' => array(
        'id' => 'mcrypt_aes_256_test',
        'title' => "Mcrypt AES 256 test",
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
    $this->assertEquals(['mcrypt_aes_256_test'], array_keys($methods));
  }

  /**
   * Tests the encrypt method.
   *
   * @covers ::__construct
   * @covers ::encrypt
   */
  public function testEncrypt() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
        'getEncryptionKeyValue',
        'getEncryptionMethod',
        ]
      )
      ->setConstructorArgs(array(
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('encrypt')
      ->will($this->returnValue("encrypted_text"));

    $encrypt_service->expects($this->once())
      ->method('getEncryptionKeyValue')
      ->will($this->returnValue('encryption_key'));

    $encrypt_service->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue($this->encryptionMethod));

    $encrypted_text = $encrypt_service->encrypt("text_to_encrypt", $this->encryptionProfile);
    $this->assertEquals("encrypted_text", $encrypted_text);
  }

  /**
   * Tests the decrypt method.
   *
   * @covers ::__construct
   * @covers ::decrypt
   */
  public function testDecrypt() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
          'getEncryptionKeyValue',
          'getEncryptionMethod',
        ]
      )
      ->setConstructorArgs(array(
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('decrypt')
      ->will($this->returnValue("decrypted_text"));

    $encrypt_service->expects($this->once())
      ->method('getEncryptionKeyValue')
      ->will($this->returnValue('encryption_key'));

    $encrypt_service->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue($this->encryptionMethod));

    $encrypted_text = $encrypt_service->decrypt("text_to_encrypt", $this->encryptionProfile);
    $this->assertEquals("decrypted_text", $encrypted_text);
  }

  /**
   * Tests the getEncryptionKeyValue() method.
   *
   * @covers ::getEncryptionKeyValue
   *
   * @dataProvider encryptionKeyValueDataProvider
   */
  public function testGetEncryptionKeyValue($key, $valid_key) {
    // Set up expectations for the encryption manager.
    $this->encryptManager->expects($this->any())
      ->method('createInstance')
      ->with($this->equalTo('test_encryption_method'))
      ->will($this->returnValue($this->encryptionMethod));

    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
          'getEncryptionMethod',
          'loadEncryptionProfileKey',
        ]
      )
      ->setConstructorArgs(array(
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Set up expectations for encryption method.
    if ($valid_key) {
      $this->encryptionMethod->expects($this->once())
        ->method('encrypt')
        ->will($this->returnValue("encrypted_text"));
      $this->encryptionMethod->expects($this->once())
        ->method('checkDependencies')
        ->will($this->returnValue(array()));
      $encrypt_service->expects($this->once())
        ->method('getEncryptionMethod')
        ->will($this->returnValue($this->encryptionMethod));
    }
    else {
      $this->encryptionMethod->expects($this->never())
        ->method('encrypt');
      $this->encryptionMethod->expects($this->once())
        ->method('checkDependencies')
        ->will($this->returnValue(array("Dependency error")));
      $this->setExpectedException('\Drupal\encrypt\Exception\EncryptException');
      $encrypt_service->expects($this->never())
        ->method('getEncryptionMethod');
    }

    // Set up expectation for handling of encryption profile by
    // the getEncryptionKeyValue() method.
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue("test_encryption_method"));

    $encrypt_service->expects($this->once())
      ->method('loadEncryptionProfileKey')
      ->with($this->encryptionProfile)
      ->will($this->returnValue($key));

    // Call getEncryptionKeyValue() through the public encrypt() method.
    $encrypted_text = $encrypt_service->encrypt("text_to_encrypt", $this->encryptionProfile);
    if ($valid_key) {
      $this->assertEquals("encrypted_text", $encrypted_text);
    }

  }

  /**
   * Data provider for testGetEncryptionKeyValue method.
   *
   * @return array
   *   An array with data for the testGetEncryptionKeyValue method.
   */
  public function encryptionKeyValueDataProvider() {
    return [
      'normal' => ["mustbesixteenbit", TRUE],
      'exception' => ["invalidkey", FALSE],
    ];
  }

}
