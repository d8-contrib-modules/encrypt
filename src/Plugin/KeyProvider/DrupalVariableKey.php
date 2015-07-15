<?php

namespace Drupal\encrypt\Plugin\KeyProvider;

use Drupal\encrypt\KeyProviderBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class DrupalVariableKey
 * @package Drupal\encrypt\Plugin\KeyProvider
 *
 * @KeyProvider(
 *   id = "drupal_variable",
 *   title = @Translation("Drupal encrypt_drupal_variable_key variable"),
 *   description = "Use a variable called encrypt_drupal_variable_key, preferably set in your settings.php $conf array.",
 *   staticKey = TRUE
 * )
 */
class DrupalVariableKey extends PluginBase implements KeyProviderBaseInterface {

  /**
   * @return mixed
   */
  public function getDependencies() {
    $errors = array();

    $key = \Drupal::config('encrypt.settings')->get('encrypt_drupal_variable_key');
    if (empty($key)) {
      $errors[] = t('The encrypt_drupal_variable_key is currently null. You should set it, preferably in $conf in your settings.php.');
    }

    return $errors;
  }

  /**
   * Callback method to return Drupal's private key.
   */
  function getKey() {
    $key = \Drupal::config('encrypt.settings')->get('encrypt_drupal_variable_key');
    if (empty($key)) {
      \Drupal::logger('encrypt')->emergency('You need to set the encrypt_drupal_variable_key variable, preferably in $conf in your settings.php.', array());
      drupal_set_message("Encryption settings are insufficient. See your site log for more information.", 'error');
    }
    return $key;
  }
}