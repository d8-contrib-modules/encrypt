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
    $header['key'] = $this->t('Key');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    $row['key'] = $entity->getEncryptionKey();
    return $row + parent::buildRow($entity);
  }

}
