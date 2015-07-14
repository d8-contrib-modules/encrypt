<?php

namespace Drupal\encrypt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class EncryptController extends ControllerBase
{
  public function encryptConfigsList(){
    $configs = encrypt_get_configs();
    $methods = encrypt_get_encryption_methods();
    $providers = encrypt_get_key_providers();
    $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');

    $header = array(
      t('Name'),
      t('Encryption Method'),
      t('Key Provider'),
      t('Created'),
      t('Status'),
      array('data' => t('Operations'), 'colspan' => '3'),
    );
    $rows = array();

    foreach ($configs as $key => $config) {
      $label = $config['label'];
      $name = $config['name'];
      $description = $config['description'];
      $method = $config['method'];
      $provider = $config['provider'];
      $created = format_date($config['created'], 'short');

      $config_url_str = str_replace('_', '-', $name);

      $variables = array(
        'label' => $label,
        'name' => $name,
        'description' => $description,
      );

      $row = array();

      // Set the name column.
      // @FIXME _theme is internal
      // $row = array(_theme('encrypt_configs_list_description', $variables));
      $row[] = array('data' => $variables['name']);

      // Set the encryption method column.
      $row[] = array('data' => $methods[$method]['title']);

      // Set the key provider column.
      $row[] = array('data' => $providers[$provider]['title']);

      // Set the created column.
      $row[] = array('data' => $created);

      // Set the status column.
      $status = array();
      $status[] = ($config['enabled']) ? t('Enabled') : t('Disabled');
      if ($default_config == $config['name']) {
        $status[] = t('Default');
      }
      $row[] = array('data' => implode(', ', $status));

      // Set the edit column.
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      //$row[] = array('data' => l(t('edit'), ENCRYPT_MENU_PATH . '/edit/' . $config_url_str));


      // Set the delete column.
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      // $row[] = array('data' => l(t('delete'), ENCRYPT_MENU_PATH . '/delete/' . $config_url_str));


      // Set the make default column if this is not already the default.
      if ($default_config != $name) {
        $row[] = array('data' => \Drupal::l(t('make default'), Url::fromRoute('encrypt.make_default', array('encrypt_config' => $config_url_str))));
      }
      else {
        $row[] = array('data' => '');
      }

      $rows[] = $row;
    }

   return array(
     '#theme' => 'table',
     '#header' => $header,
     '#rows' => $rows,
     //'#empty' => t('No encryption configurations are available. <a href="@link">Add a configuration</a>.', array('@link' => url(ENCRYPT_MENU_PATH . '/add'))),
   );

  }

  /**
   * Returns HTML for a configuration description.
   *
   * @param array $variables
   *   An associative array containing:
   *   - label: The human-readable label of the configuration.
   *   - name: The machine name of the configuration.
   *   - description: A brief description of the configuration.
   *
   * @ingroup themeable
   */
  function theme_encrypt_configs_list_description($variables) {
    $label = $variables['label'];
    $name = $variables['name'];
    $description = $variables['description'];

    $output = check_plain($label);
    $output .= ' <small>' . t('(Machine name: @name)', array('@name' => $name)) . '</small>';
    $output .= '<div class="description">' . filter_xss_admin($description) . '</div>';

    return $output;
  }

  /**
   * Menu callback to make a configuration the default.
   */
  function configMakeDefault($encrypt_config) {
    $encrypt_config = encrypt_get_config($encrypt_config);

    \Drupal::configFactory()->getEditable('encrypt.settings')->set('encrypt_default_config', $encrypt_config['name'])->save();


    $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');
    $t_args = array('%label' => $encrypt_config['label']);
    if ($default_config == $encrypt_config['name']) {
      // If the configuration is not enabled and it's the new default, enable it.
      if (!$encrypt_config['enabled']) {
        db_update('encrypt_config')
          ->fields(array('enabled' => 1))
          ->condition('name', $encrypt_config['name'])
          ->execute();
        drupal_set_message(t('The configuration %label has been enabled.', $t_args));
        \Drupal::logger('node')->notice('Enabled encryption configuration %label.', []);
      }

      drupal_set_message(t('The configuration %label has been made the default.', $t_args));
      \Drupal::logger('encrypt')->notice('Made encryption configuration %label the default.', []);
    }
    else {
      drupal_set_message(t('The configuration %label could not be made the default.', $t_args), 'error');
      \Drupal::logger('encrypt')->error('Error when trying to make encryption configuration %label the default.', []);
    }

    return $this->redirect('encrypt.list');
  }
}
