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
   * A mocked EntityManager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

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
   * A mocked Key entity.
   *
   * @var \Drupal\key\Entity\Key|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $key;

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

    // Set up a mock EncryptionMethod plugin.
    $this->encryptionMethod = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock EntityManager.
    $this->entityManager = $this->getMockBuilder('\Drupal\Core\Entity\EntityManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Mock the Entity storage layer.
    $entity_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');

    // Expect the entity storage to return a EncryptionProfile entity.
    $entity_storage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($this->encryptionProfile));

    // Set up expectations for the entity manager.
    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($entity_storage));

    // Set up a mock EncryptionMethodManager.
    $this->encryptManager = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodManager')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up expectations for the encryption manager.
    $this->encryptManager->expects($this->any())
      ->method('createInstance')
      ->with($this->equalTo('test_encryption_method'))
      ->will($this->returnValue($this->encryptionMethod));

    // Set up a mock KeyRepository.
    $this->keyRepository = $this->getMockBuilder('\Drupal\key\KeyRepository')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up a mock KeyRepository.
    $this->key = $this->getMockBuilder('\Drupal\key\Entity\Key')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up expectations for the key and key repository.
    $this->key->expects($this->any())
      ->method('getKeyValue')
      ->will($this->returnValue("mustbesixteenbit"));
    $this->keyRepository->expects($this->any())
      ->method('getKey')
      ->with("test_encryption_key")
      ->will($this->returnValue($this->key));
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
      $this->entityManager,
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
   * @covers ::loadEncryptionProfile
   * @covers ::loadEncryptionMethod
   * @covers ::loadEncryptionProfileKey
   */
  public function testEncrypt() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
        'loadEncryptionProfile',
        'loadEncryptionMethod',
        'loadEncryptionProfileKey',
        'checkDependencies',
      ]
      )
      ->setConstructorArgs(array(
        $this->entityManager,
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Mock the methods on EncryptService that are out of scope for this test.
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue("test_encryption_method"));
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionKey')
      ->will($this->returnValue("test_encryption_key"));

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('checkDependencies')
      ->will($this->returnValue(array()));
    $this->encryptionMethod->expects($this->once())
      ->method('encrypt')
      ->will($this->returnValue("encrypted_text"));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfile')
      ->with($this->equalTo('test_encryption_profile'))
      ->will($this->returnValue($this->encryptionProfile));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionMethod')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfileKey')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypted_text = $encrypt_service->encrypt("text_to_encrypt", "test_encryption_profile");
    $this->assertEquals("encrypted_text", $encrypted_text);
  }

  /**
   * Tests the encrypt method throwing an exception.
   *
   * @covers ::__construct
   * @covers ::encrypt
   * @covers ::loadEncryptionProfile
   * @covers ::loadEncryptionMethod
   * @covers ::loadEncryptionProfileKey
   *
   * @@expectedException \Exception
   */
  public function testEncryptException() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
        'loadEncryptionProfile',
        'loadEncryptionMethod',
        'loadEncryptionProfileKey',
        'checkDependencies',
      ])
      ->setConstructorArgs(array(
        $this->entityManager,
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Mock the methods on EncryptService that are out of scope for this test.
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue("test_encryption_method"));
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionKey')
      ->will($this->returnValue("test_encryption_key"));

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('checkDependencies')
      ->will($this->returnValue(['Dependency error']));
    $this->encryptionMethod->expects($this->never())
      ->method('encrypt');

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfile')
      ->with($this->equalTo('test_encryption_profile'))
      ->will($this->returnValue($this->encryptionProfile));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionMethod')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfileKey')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->encrypt("text_to_encrypt", "test_encryption_profile");
  }

  /**
   * Tests the decrypt method.
   *
   * @covers ::__construct
   * @covers ::decrypt
   * @covers ::loadEncryptionProfile
   * @covers ::loadEncryptionMethod
   * @covers ::loadEncryptionProfileKey
   */
  public function testDecrypt() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
        'loadEncryptionProfile',
        'loadEncryptionMethod',
        'loadEncryptionProfileKey',
        'checkDependencies',
      ])
      ->setConstructorArgs(array(
        $this->entityManager,
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Mock the methods on EncryptService that are out of scope for this test.
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue("test_encryption_method"));
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionKey')
      ->will($this->returnValue("test_encryption_key"));

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('checkDependencies')
      ->will($this->returnValue(array()));
    $this->encryptionMethod->expects($this->once())
      ->method('decrypt')
      ->will($this->returnValue("decrypted_text"));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfile')
      ->with($this->equalTo('test_encryption_profile'))
      ->will($this->returnValue($this->encryptionProfile));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionMethod')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfileKey')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $decrypted_text = $encrypt_service->decrypt("text_to_decrypt", "test_encryption_profile");
    $this->assertEquals("decrypted_text", $decrypted_text);
  }

  /**
   * Tests the decrypt method throwing an exception.
   *
   * @covers ::__construct
   * @covers ::decrypt
   * @covers ::loadEncryptionProfile
   * @covers ::loadEncryptionMethod
   * @covers ::loadEncryptionProfileKey
   *
   * @@expectedException \Exception
   */
  public function testDecryptException() {
    // Set up a mock for the EncryptService class to mock some methods.
    $encrypt_service = $this->getMockBuilder('\Drupal\encrypt\EncryptService')
      ->setMethods([
        'loadEncryptionProfile',
        'loadEncryptionMethod',
        'loadEncryptionProfileKey',
        'checkDependencies',
      ])
      ->setConstructorArgs(array(
        $this->entityManager,
        $this->encryptManager,
        $this->keyRepository,
      ))
      ->getMock();

    // Mock the methods on EncryptService that are out of scope for this test.
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionMethod')
      ->will($this->returnValue("test_encryption_method"));
    $this->encryptionProfile->expects($this->once())
      ->method('getEncryptionKey')
      ->will($this->returnValue("test_encryption_key"));

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->once())
      ->method('checkDependencies')
      ->will($this->returnValue(['Dependency error']));
    $this->encryptionMethod->expects($this->never())
      ->method('decrypt');

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfile')
      ->with($this->equalTo('test_encryption_profile'))
      ->will($this->returnValue($this->encryptionProfile));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionMethod')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->expects($this->any())
      ->method('loadEncryptionProfileKey')
      ->with($this->equalTo($this->encryptionProfile))
      ->will($this->returnValue($this->encryptionMethod));

    $encrypt_service->encrypt("text_to_decrypt", "test_encryption_profile");
  }

}
