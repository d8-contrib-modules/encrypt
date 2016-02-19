<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptionProfileManager.
 */

namespace Drupal\encrypt;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines an EncryptionProfile manager.
 */
class EncryptionProfileManager implements EncryptionProfileManagerInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Construct the EncryptionProfileManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEncryptionProfilesByEncryptionMethod($encryption_method_id) {
    return $this->entityManager->getStorage('encryption_profile')->loadByProperties(array('encryption_method' => $encryption_method_id));
  }

  /**
   * {@inheritdoc}
   */
  public function getEncryptionProfilesByEncryptionKey($key_id) {
    return $this->entityManager->getStorage('encryption_profile')->loadByProperties(array('encryption_key' => $key_id));
  }

}
