<?php
/**
 * @file
 * Contains \Drupal\encrypt\Plugin\EncryptionMethod\EncryptionMethodBase.
 */

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\Core\Plugin\PluginBase;
use Drupal\encrypt\EncryptionMethodInterface;

/**
 * Provides a base class for EncryptionMethod plugins.
 */
abstract class EncryptionMethodBase extends PluginBase implements EncryptionMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

}
