<?php

namespace Drupal\encrypt\Plugin\KeyProvider;

use Drupal\encrypt\Annotation\KeyProvider;
use Drupal\encrypt\KeyProviderBaseInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class FileKey
 * @package Drupal\encrypt\Plugin\KeyProvider
 *
 * @KeyProvider(
 *   id = "file_key",
 *   title = @Translation("Key File"),
 *   description = "Use a file to store the encryption key.",
 *   staticKey = TRUE
 * )
 */
class FileKey extends PluginBase implements KeyProviderBaseInterface {

  protected $ENCRYPT_FILE_NAME = 'encrypt_key.key';

  /**
   * @return mixed
   */
  public function getDependencies() {
    return array();
  }

  function configurationSaveHandler($form, &$form_state){
    $directory = $form_state->getValue('encrypt_key_providers_file_settings')['path'];
    $file = $directory . '/' . $this->ENCRYPT_FILE_NAME;

    // Check for contents of file; do not write over the key.
    if (file_exists($file) && file_get_contents($file)) {
      return;
    }

    // Create a default key.
    $open_file = fopen($file, 'wb');
    $new_key = md5(uniqid(mt_rand(0, mt_rand()), TRUE)) . md5(uniqid(mt_rand(0, mt_rand()), TRUE));
    fwrite($open_file, $new_key);
    fclose($open_file);

    // Ensure that the file is only readable and writable by owner.
    \Drupal::service('file_system')->chmod($file, 0600);

    drupal_set_message(t('The key file has been written to %file', array('%file' => $file)));
  }

  /**
   * Callback method to return Drupal's private key.
   */
  function getKey($settings = array()) {
    $file_name = $settings['path'] . '/' . ENCRYPT_FILE_NAME;
    // If the file doesn't exist, just abort.
    if (!file_exists($file_name)) {
      drupal_set_message(t('The file %file does not exist! Encrypt cannot retrieve encryption key.', array('%file' => $file_name)), 'error');
      return;
    }

    switch ($settings['method']) {
      case 'file_contents':
        return file_get_contents($file_name);

      case 'md5':
        return md5_file($file_name);

    }
  }
}

/**
 * Settings form for our key provider.
 */
function encrypt_file_key_settings_form($defaults) {
  $form = array();

  $form['path'] = array(
    '#type' => 'textfield',
    '#title' => t('Path to Key File'),
    '#description' => t('Filepath may be absolute (e.g. %abs) or relative to the Drupal directory (e.g. %rel).', array(
      '%abs' => '/var/www',
      '%rel' => '../../keys',
    )),
    '#default_value' => isset($defaults['path']) ? $defaults['path'] : '',
    '#element_validate' => array('encrypt_file_key_path_validate'),
    '#required' => TRUE,
    '#field_suffix' => '/' . ENCRYPT_FILE_NAME,
  );
  $form['method'] = array(
    '#type' => 'select',
    '#title' => t('Method'),
    '#options' => array(
      'file_contents' => t('File Contents'),
      'md5' => t('MD5'),
    ),
    '#default_value' => isset($defaults['method']) ? $defaults['method'] : 'file_contents',
  );

  return $form;
}

/**
 * Validate key path.
 */
function encrypt_file_key_path_validate($element, &$form_state, $form) {
  $directory = $element['#value'];
  $file = $element['#value'] . '/' . ENCRYPT_FILE_NAME;

  if (!file_exists($directory)) {
    form_error($element, t('The directory %dir does not exist.', array('%dir' => $directory)));
  }
  elseif (!is_dir($directory)) {
    form_error($element, t('The key path provided exists, but is not a directory.'));
  }
  elseif (!file_exists($file) && !is_writable($directory)) {
    form_error($element, t('The directory %dir needs to be writable in order to create the key file.', array(
      '%dir' => $directory,
    )));
  }
  elseif (file_exists($file) && !is_readable($file)) {
    form_error($element, t('The key file %file exists, but is not readable by the web server.', array(
      '%file' => $file,
    )));
  }
}
