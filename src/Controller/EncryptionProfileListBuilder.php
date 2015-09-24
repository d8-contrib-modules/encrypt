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
    $header['key'] = $this->t('Key');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    $row['service_default'] = ($entity->getServiceDefault())?"Yes":"No";

    $profile_keys = \Drupal::config('encrypt.settings')->get('profile_keys');
    foreach ($profile_keys as $profile_key) {
      if ($profile_key->encryption_profile == $entity->id()) {
        $row['key'] = $profile_key->encryption_key;
      }
    }

    if (empty($row['key'])) {
      $row['key'] = 'default';
    }
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
