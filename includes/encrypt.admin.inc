<?php

/**
 * @file
 * This file holds the functions for the Encrypt admin settings.
 *
 * @ingroup encrypt
 */

/**
 * Menu callback; displays the list of configurations.
 */


/**
 * Form validation handler for encrypt_config_form().
 */
function encrypt_config_form_validate($form, &$form_state) {
  // Don't allow the default configuration to be disabled.
  $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');

  if ($default_config == $form_state['values']['name'] && !$form_state['values']['enabled']) {
    form_set_error('general_settings][enabled', t('The default configuration must be enabled.'));
  }
}



/**
 * Form submission handler for encrypt_config_form().
 *
 * Handles the 'Delete' button on the encryption configuration form.
 */
function encrypt_config_form_delete_submit($form, &$form_state) {
  $form_state['redirect'] = ENCRYPT_MENU_PATH . '/delete/' . str_replace('_', '-', $form['name']['#default_value']);
}

/**
 * Menu callback to delete an encryption configuration.
 *
 * @ingroup forms
 */
function encrypt_config_delete_confirm($form, &$form_state, $config) {
  $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');

  if ($default_config == $config['name']) {
    drupal_set_message(t('The default configuration cannot be deleted.'), 'error');
    drupal_goto(ENCRYPT_MENU_PATH);
  }

  $form['name'] = array('#type' => 'value', '#value' => $config['name']);
  $form['label'] = array('#type' => 'value', '#value' => $config['label']);

  $message = t('Are you sure you want to delete the configuration %label?', array('%label' => $config['label']));
  $caption = '<p>' . t('This action cannot be undone.') . '</p>';

  return confirm_form($form, filter_xss_admin($message), ENCRYPT_MENU_PATH, filter_xss_admin($caption), t('Delete'));
}

/**
 * Submit handler for encrypt_config_delete_confirm.
 *
 * @see encrypt_config_delete_confirm()
 */
function encrypt_config_delete_confirm_submit($form, &$form_state) {
  db_delete('encrypt_config')
    ->condition('name', $form_state['values']['name'])
    ->execute();

  $t_args = array('%label' => $form_state['values']['label']);
  drupal_set_message(t('The configuration %label has been deleted.', $t_args));
  \Drupal::logger('encrypt')->notice('Deleted encryption configuration %label.', []);

  $form_state['redirect'] = ENCRYPT_MENU_PATH;
}

/**
 * Old settings callback.
 *
 * This form is no longer used, so if a user reaches it, display a notice
 * that an update is necessary.
 */
function encrypt_admin_settings($form, &$form_state) {
  _encrypt_display_update_message();
}


