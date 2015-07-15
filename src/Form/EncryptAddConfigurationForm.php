<?php
/**
 * @file
 * Contains \Drupal\encrypt\Form\EncryptAddConfigurationForm.
 */
namespace Drupal\encrypt\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\encrypt\EncryptionMethodManager;
use Drupal\encrypt\KeyProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for book routes.
 */
class EncryptAddConfigurationForm extends FormBase {
  protected $encryptionMethodManager;
  protected $keyProviderManager;

  /**
   * Constructs a \Drupal\system\FormBase object.
   *
   */
  public function __construct(EncryptionMethodManager $encryption_method_manager, KeyProviderManager $key_provider_manager) {
    $this->encryptionMethodManager = $encryption_method_manager;
    $this->keyProviderManager = $key_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.encrypt.encryption_methods'),
      $container->get('plugin.manager.encrypt.key_providers')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, $encrypt_config = NULL) {
    $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');
    $config = encrypt_get_config($encrypt_config);

    $method_options = array();
    foreach ($this->encryptionMethodManager->getDefinitions() as $encryptionMethod) {
      $method_options[$encryptionMethod['id']] = $encryptionMethod['title'];
    }

    // Create a list of provider titles to be used for radio buttons.
    $provider_options = array();
    foreach ($this->keyProviderManager->getDefinitions() as $keyProvider) {
      $provider_options[$keyProvider['id']] = $keyProvider['title'];
    }

    $form['label'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $config['label'],
      '#description' => t('The human-readable name of the configuration.'),
      '#required' => TRUE,
      '#size' => 30,
    );
    $form['name'] = array(
      '#type' => 'machine_name',
      '#default_value' => $config['name'],
      '#maxlength' => 32,
      '#disabled' => isset($config['name']),
      '#machine_name' => array(
        'exists' => 'encrypt_config_load',
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for the configuration. It must only contain lowercase letters, numbers, and underscores.'),
    );
    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $config['description'],
      '#description' => t('A short description of the configuration.'),
    );

    $form['general_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('General settings'),
      '#collapsible' => TRUE,
    );
    $form['general_settings']['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => $config['enabled'],
      '#description' => t('If checked, this configuration will be available for encryption. The default configuration must be enabled.'),
    );

    // If this is the default configuration, disable the enabled checkbox.
    if ($config['name'] == $default_config) {
      $form['general_settings']['enabled']['#disabled'] = TRUE;
    }

    $form['method_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Encryption method settings'),
      '#collapsible' => TRUE,
    );
    $form['method_settings']['encrypt_encryption_method'] = array(
      '#type' => 'radios',
      '#title' => t('Encryption Method'),
      '#description' => t('Define the default encryption method for the site. Since encryption methods are stored with the data, this can be changed even after you have stored encrypted data.'),
      '#required' => TRUE,
      '#options' => $method_options,
      '#default_value' => $config['method'],
      '#ajax' => array(
        'method' => 'replace',
        'callback' => 'encrypt_encryption_methods_additional_settings_ajax',
        'wrapper' => 'encrypt-encryption-methods-additional-settings',
      ),
    );
    // Disable any method with dependency errors.
    //$this->_encrypt_admin_form_add_options($methods, $form['method_settings']['encrypt_encryption_method']);

    $form['method_settings']['method_settings_wrapper'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="encrypt-encryption-methods-additional-settings">',
      '#suffix' => '</div>',
    );

    $method = \Drupal::config('encrypt.settings')->get('encrypt_encryption_method');

    /*if ($method) {
      if ($method_settings_form = ctools_plugin_get_function($methods[$method], 'settings form')) {
        $form['method_settings']['method_settings_wrapper']['method_settings'] = array(
          '#type' => 'fieldset',
          '#title' => t('Additional Encryption Method Settings'),
        );

        $form['method_settings']['method_settings_wrapper']['method_settings']['encrypt_encryption_methods_' . $method . '_settings'] = array('#tree' => TRUE) + call_user_func($method_settings_form, $config['method_settings']);
      }
    }*/

    $form['provider_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Key provider settings'),
      '#collapsible' => TRUE,
    );
    $form['provider_settings']['encrypt_key_provider'] = array(
      '#type' => 'radios',
      '#title' => t('Key Provider'),
      '#description' => t('Select the method by which encrypt will retrieve an encryption key. NOTE: Once this is set, it is not a good idea to change it. All of your encrypted data may be lost if the encryption key changes.'),
      '#required' => TRUE,
      '#options' => $provider_options,
      '#default_value' => $config['provider'],
      '#ajax' => array(
        'method' => 'replace',
        'callback' => 'encrypt_key_providers_additional_settings_ajax',
        'wrapper' => 'encrypt-key-providers-additional-settings',
      ),
    );
    // Disable any provider with dependency errors.
    //$this->_encrypt_admin_form_add_options($providers, $form['provider_settings']['encrypt_key_provider']);

    $form['provider_settings']['key_settings_wrapper'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="encrypt-key-providers-additional-settings">',
      '#suffix' => '</div>',
    );

    $provider = \Drupal::config('encrypt.settings')->get('encrypt_key_provider');

    /*if ($provider) {
      if ($provider_settings_form = ctools_plugin_get_function($providers[$provider], 'settings form')) {
        $form['provider_settings']['key_settings_wrapper']['key_settings'] = array(
          '#type' => 'fieldset',
          '#title' => t('Additional Key Provider Settings'),
        );

        $form['provider_settings']['key_settings_wrapper']['key_settings']['encrypt_key_providers_' . $provider . '_settings'] = array('#tree' => TRUE) + call_user_func($provider_settings_form, $config['provider_settings']);
      }
    }*/

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
      '#weight' => 40,
    );
    if (isset($config['name'])) {
      $form['actions']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete configuration'),
        '#submit' => array('::encrypt_config_form_delete_submit'),
        '#weight' => 45,
      );
    }

    return $form;
  }

  /**
   * Add other elements to forms.
   */
  function _encrypt_admin_form_add_options($items, &$element) {
    foreach ($items as $id => $item) {
      $element[$id] = array(
        '#description' => $item['description'],
        '#disabled' => !empty($item['dependency errors']),
      );

      // Add a list of dependency errors (if there are any).
      if (!empty($item['dependency errors'])) {
        $element[$id]['#description'] .= _theme('item_list', array(
          'items' => $item['dependency errors'],
          'attributes' => array('class' => 'encrypt-dependency-errors'),
        ));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    $method = $form_state->getValue('encrypt_encryption_method');
    $key_provider = $form_state->getValue('encrypt_key_provider');

    $methodPlugin = $this->encryptionMethodManager->createInstance($method);
    $providerPlugin = $this->keyProviderManager->createInstance($key_provider);


    foreach (array($methodPlugin, $providerPlugin) as $plugin) {
      if (method_exists($plugin, 'configurationSaveHandler')) {
        $form_state_copy = $form_state;
        $plugin->configurationSaveHandler($form, $form_state_copy);
      }
    }

    $config_exists = (bool) db_query_range('SELECT 1 FROM {encrypt_config} WHERE name = :name', 0, 1, array(':name' => $form_state->getValue('name')))->fetchField();

    $fields = array(
      'name' => (string) $form_state->getValue('name'),
      'label' => (string) $form_state->getValue('label'),
      'description' => (string) $form_state->getValue('description'),
      'method' => (string) $form_state->getValue('encrypt_encryption_method'),
      'provider' => (string) $form_state->getValue('encrypt_key_provider'),
      'enabled' => (int) $form_state->getValue('enabled'),
      'changed' => REQUEST_TIME,
    );

    $methodSettings = $form_state->getValue('encrypt_encryption_methods_' . $fields['method'] . '_settings');
    if (empty($methodSettings)) {
      $fields['method_settings'] = '';
    }
    else {
      $fields['method_settings'] = serialize($methodSettings);
    }

    $keySettings = $form_state->getValue('encrypt_key_providers_' . $fields['provider'] . '_settings');
    if ($keySettings) {
      $fields['provider_settings'] = '';
    }
    else {
      $fields['provider_settings'] = serialize($keySettings);
    }

    $t_args = array('%label' => $fields['label']);

    if ($config_exists) {
      db_update('encrypt_config')
        ->fields($fields)
        ->condition('name', $fields['name'])
        ->execute();
      drupal_set_message(t('The configuration %label has been updated.', $t_args));
      \Drupal::logger('encrypt')->notice('Updated encryption configuration %label.', $t_args, WATCHDOG_NOTICE, \Drupal::l(t('view'), Url::fromRoute('encrypt.list')));

    }
    else {
      $fields['created'] = REQUEST_TIME;
      db_insert('encrypt_config')
        ->fields($fields)
        ->execute();
      drupal_set_message(t('The configuration %label has been added.', $t_args));
      \Drupal::logger('encrypt')->notice('Added encryption configuration %label.', $t_args, WATCHDOG_NOTICE, \Drupal::l(t('view'), Url::fromRoute('encrypt.list')));
    }

    $form_state->setRedirect('encrypt.list');
  }

  /**
   * Callback for AJAX form re-rendering for method additional settings.
   */
  function encrypt_encryption_methods_additional_settings_ajax(array &$form, FormStateInterface $form_state) {
    return $form['method_settings']['method_settings_wrapper'];
  }

  /**
   * Callback for AJAX form re-rendering for provider additional settings.
   */
  function encrypt_key_providers_additional_settings_ajax(array &$form, FormStateInterface $form_state) {
    return $form['provider_settings']['key_settings_wrapper'];
  }

  /**
   * Form submission handler for encrypt_config_form().
   *
   * Handles the 'Delete' button on the encryption configuration form.
   */
  function encrypt_config_form_delete_submit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('encrypt.delete',array('encrypt_config' => $form_state->getValue('name')));
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'encrypt_add';
  }

}