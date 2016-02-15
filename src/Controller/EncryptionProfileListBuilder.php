<?php

/**
 * @file
 * Contains Drupal\encrypt\Controller\EncryptionProfileListBuilder.
 */

namespace Drupal\encrypt\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of encryption profile entities.
 */
class EncryptionProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * A configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new EncryptionProfileListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ConfigFactoryInterface $config_factory) {
    parent::__construct($entity_type, $storage);
    $this->config = $config_factory->get('encrypt.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['encryption_method'] = $this->t('Encryption method');
    $header['key'] = $this->t('Key');
    if ($this->config->get('check_profile_status')) {
      $header['status'] = $this->t('Status');
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    // Render encryption method row.
    if ($encryption_method = $entity->getEncryptionMethod()) {
      $row['encryption_method'] = $encryption_method->getLabel();
    }
    else {
      $row['encryption_method'] = $this->t('Error loading encryption method');
    }

    // Render encryption key row.
    if ($key = $entity->getEncryptionKey()) {
      $row['key'] = $key->label();
    }
    else {
      $row['key'] = $this->t('Error loading key');
    }

    // Render status report row.
    if ($this->config->get('check_profile_status')) {
      $errors = $entity->validate();
      if (!empty($errors)) {
        $row['status']['data'] = array(
          '#theme' => 'item_list',
          '#items' => $errors,
          '#attributes' => array("class" => array("color-error")),
        );
      }
      else {
        $row['status'] = $this->t('OK');
      }
    }
    return $row + parent::buildRow($entity);
  }

}
