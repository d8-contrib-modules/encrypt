<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptionMethodPluginCollection.
 */

namespace Drupal\encrypt;

use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a container for lazily loading EncryptionMethod plugins.
 */
class EncryptionMethodPluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\encrypt\EncryptionMethodInterface
   *   The Encryption Method plugin to get.
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addInstanceId($id, $configuration = NULL) {
    $this->instanceId = $id;
    parent::addInstanceId($id, $configuration);
    if ($configuration !== NULL) {
      $this->setConfiguration($configuration);
    }
  }

}
