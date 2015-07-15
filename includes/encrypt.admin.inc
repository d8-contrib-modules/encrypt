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
 * Old settings callback.
 *
 * This form is no longer used, so if a user reaches it, display a notice
 * that an update is necessary.
 */
function encrypt_admin_settings($form, &$form_state) {
  _encrypt_display_update_message();
}


