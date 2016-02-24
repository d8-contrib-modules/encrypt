<?php
/**
 * @file
 * Contains \Drupal\encrypt\Plugin\EncryptionMethod\EncryptionMethodBase.
 */

namespace Drupal\encrypt\Plugin\EncryptionMethod;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginBase;
use Drupal\encrypt\EncryptionMethodInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for EncryptionMethod plugins.
 */
abstract class EncryptionMethodBase extends PluginBase implements EncryptionMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

}
