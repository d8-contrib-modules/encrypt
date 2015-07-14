<?php

namespace Drupal\encrypt\Plugin\KeyProvider;

use Drupal\encrypt\Annotation\KeyProvider;
use Drupal\encrypt\KeyProviderBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class DrupalPrivateKey
 * @package Drupal\encrypt\Plugin\KeyProvider
 *
 * @KeyProvider(
 *   id = "drupal_private_key",
 *   title = @Translation("Drupal Private Key"),
 *   description = "Use Drupal's private key from the database.",
 *   staticKey = TRUE
 * )
 */
class DrupalPrivateKey extends PluginBase implements KeyProviderBaseInterface {

  /**
   * @return mixed
   */
  public function getDependencies() {
    return array();
  }

  /**
   * Callback method to return Drupal's private key.
   */
  function getKey() {
    return drupal_get_private_key();
  }
}