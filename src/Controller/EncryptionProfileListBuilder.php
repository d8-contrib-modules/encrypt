<?php

/**
 * @file
 * Contains Drupal\encrypt\Controller\EncryptionProfileListBuilder.
 */

namespace Drupal\encrypt\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of encryption profile entities.
 */
class EncryptionProfileListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['service_default'] = $this->t('Default');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    $row['service_default'] = ($entity->getServiceDefault())?"Yes":"No";
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $operations = parent::getDefaultOperations($entity);

    $operations['set_default'] = array(
      'title' => t('Set Default'),
      'weight' => -10,
      'url' => $entity->urlInfo('set-default'),
    );

    return $operations;
  }

}
