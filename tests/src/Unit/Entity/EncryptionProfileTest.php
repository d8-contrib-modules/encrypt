<?php
/**
 * @file
 * Contains Drupal\Tests\encrypt\Unit\Entity\EncryptionProfileTest.
 */

namespace Drupal\Tests\encrypt\Unit\Entity;

use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for EncryptionProfile class.
 *
 * @ingroup encrypt
 *
 * @group encrypt
 *
 * @coversDefaultClass \Drupal\encrypt\Entity\EncryptionProfile
 */
class EncryptionProfileTest extends UnitTestCase {

  /**
   * A mocked Key entity.
   *
   * @var \Drupal\key\Entity\Key|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $key;

  /**
   * A mocked EncryptionMethod.
   *
   * @var \Drupal\encrypt\EncryptionMethodInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $encryptionMethod;

  /**
   * A mocked EncryptionMethodManager.
   *
   * @var \Drupal\encrypt\EncryptionMethodManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $encryptionMethodManager;

  /**
   * A mocked KeyRepository.
   *
   * @var \Drupal\key\KeyRepository|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $keyRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Mock a Key entity.
    $this->key = $this->getMockBuilder('\Drupal\key\Entity\Key')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up expectations for key.
    $key_type = $this->getMockBuilder('\Drupal\key\Plugin\KeyType\EncryptionKeyType')
      ->disableOriginalConstructor()
      ->getMock();
    $key_type->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('encryption'));
    $this->key->expects($this->any())
      ->method('getKeyType')
      ->will($this->returnValue($key_type));
    $this->key->expects($this->any())
      ->method('getKeyValue')
      ->will($this->returnValue("key_value"));

    // Mock an EncryptionMethod.
    $this->encryptionMethod = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Set up expectations for encryption method.
    $this->encryptionMethod->expects($this->any())
      ->method('checkDependencies')
      ->will($this->returnValue(array()));

    // Mock an EncryptionMethodManager.
    $this->encryptionMethodManager = $this->getMockBuilder('\Drupal\encrypt\EncryptionMethodManager')
      ->disableOriginalConstructor()
      ->getMock();

    // Mock a KeyRepository.
    $this->keyRepository = $this->getMockBuilder('\Drupal\key\KeyRepository')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Tests the EncryptionProfile validate method.
   *
   * @covers ::__construct
   * @covers ::validate
   *
   * @dataProvider validateDataProvider
   */
  public function testValidate($enc_method_id, $enc_key, $enc_method_def, $expected_errors) {
    // Set up a mock for the EncryptionProfile class to mock some methods.
    $encryption_profile = $this->getMockBuilder('\Drupal\encrypt\Entity\EncryptionProfile')
      ->setMethods([
        'getEncryptionMethod',
        'getEncryptionKey',
        'getEncryptionMethodManager',
        'getKeyRepository',
      ]
      )
      ->disableOriginalConstructor()
      ->getMock();

    // Set expectations for EncryptionMethodManager.
    $this->encryptionMethodManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->equalTo('test_encryption_method'))
      ->will($this->returnValue($enc_method_def));
    $this->encryptionMethodManager->expects($this->any())
      ->method('createInstance')
      ->with($this->equalTo('test_encryption_method'))
      ->will($this->returnValue($this->encryptionMethod));

    // Set expectations for KeyRepository.
    if ($enc_key == "test_key") {
      $this->keyRepository->expects($this->any())
        ->method('getKey')
        ->will($this->returnValue($this->key));
    }
    if ($enc_key == "wrong_key") {
      $this->keyRepository->expects($this->any())
        ->method('getKey')
        ->will($this->returnValue(FALSE));
    }

    // Set expectations for EncryptionProfile entity.
    $encryption_profile->expects($this->any())
      ->method('getEncryptionMethod')
      ->will($this->returnValue($enc_method_id));

    $encryption_profile->expects($this->any())
      ->method('getEncryptionKey')
      ->will($this->returnValue($enc_key));

    $encryption_profile->expects($this->any())
      ->method('getEncryptionMethodManager')
      ->will($this->returnValue($this->encryptionMethodManager));

    $encryption_profile->expects($this->any())
      ->method('getKeyRepository')
      ->will($this->returnValue($this->keyRepository));

    $errors = $encryption_profile->validate();
    $this->assertEquals($expected_errors, $errors);
  }

  /**
   * Data provider for validate() function.
   */
  public function validateDataProvider() {
    $valid_definition = array(
      'id' => 'test_encryption_method',
      'title' => "Test encryption method",
      'key_type' => ['encryption'],
    );

    $invalid_allowed_keytypes = $valid_definition;
    $invalid_allowed_keytypes['key_type'] = ['other_encryption'];

    return [
      'invalid_properties' => [
        NULL,
        NULL,
        NULL,
        ['No encryption method selected.', 'No encryption key selected'],
      ],
      'invalid_encryption_method' => [
        'test_encryption_method',
        'test_key',
        NULL,
        ['The encryption method linked to this encryption profile does not exist.'],
      ],
      'invalid_key' => [
        'test_encryption_method',
        'wrong_key',
        $valid_definition,
        ['The key linked to this encryption profile does not exist.'],
      ],
      'invalid_keytypes' => [
        'test_encryption_method',
        'test_key',
        $invalid_allowed_keytypes,
        ['The selected key cannot be used with the selected encryption method.'],
      ],
      'normal' => [
        'test_encryption_method',
        'test_key',
        $valid_definition,
        [],
      ],
    ];
  }

}
