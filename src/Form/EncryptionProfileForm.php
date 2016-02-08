<?php

/**
 * @file
 * Contains Drupal\encrypt\Form\EncryptionProfileForm.
 */

namespace Drupal\encrypt\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\encrypt\EncryptService;
use Drupal\key\KeyRepository;
use Drupal\key\Plugin\KeyPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EncryptionProfileForm.
 *
 * @package Drupal\encrypt\Form
 */
class EncryptionProfileForm extends EntityForm {

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The available keys.
   *
   * @var \Drupal\key\KeyInterface[]
   */
  protected $keys;

  /**
   * The available encryption methods.
   *
   * @var \Drupal\encrypt\EncryptionMethodInterface[]
   */
  protected $encryptionMethods;

  /**
   * The Key plugin manager service.
   *
   * @var \Drupal\key\Plugin\KeyPluginManager
   */
  protected $keyManager;

  /**
   * Constructs a EncryptionProfileForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Key\KeyRepository $key_repository
   *   The ConditionManager for building the visibility UI.
   * @param \Drupal\Encrypt\EncryptService $encrypt_service
   *   The encrypt service.
   * @param \Drupal\key\Plugin\KeyPluginManager $key_manager
   *   The key manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyRepository $key_repository, EncryptService $encrypt_service, KeyPluginManager $key_manager) {
    $this->configFactory = $config_factory;
    $this->keys = $key_repository->getKeys();
    $this->encryptionMethods = $encrypt_service->loadEncryptionMethods();
    $this->keyManager = $key_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('key.repository'),
      $container->get('encryption'),
      $container->get('plugin.manager.key.key_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (empty($this->keys)) {
      drupal_set_message('No system keys (admin/config/system/key) are installed to manage encryption profiles.');
    }

    /** @var $encryption_profile \Drupal\encrypt\Entity\EncryptionProfile */
    $encryption_profile = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $encryption_profile->label(),
      '#description' => $this->t("Label for the encryption profile."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $encryption_profile->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\encrypt\Entity\EncryptionProfile::load',
      ),
      '#disabled' => !$encryption_profile->isNew(),
    );

    // This is the element that contains all of the dynamic parts of the form.
    $form['encryption'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="encrypt-settings">',
      '#suffix' => '</div>',
    );

    $encryption_methods = [];
    foreach ($this->encryptionMethods as $plugin_id => $definition) {
      $encryption_methods[$plugin_id] = (string) $definition['title'];
    }

    $current_encryption_method = $encryption_profile->getEncryptionMethod();
    if (!$current_encryption_method && !empty($encryption_methods)) {
      // Get the first one from the list.
      $current_encryption_method = array_shift(array_keys($encryption_methods));
    }

    $form['encryption']['encryption_method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Method'),
      '#description' => $this->t('Select the method used for encryption'),
      '#options' => $encryption_methods,
      '#default_value' => $current_encryption_method,
      '#ajax' => array(
        'callback' => [$this, 'ajaxUpdateSettings'],
        'event' => 'change',
        'wrapper' => 'encrypt-settings',
      ),
    );

    $keys = $this->getAllowedKeys($current_encryption_method);
    if ($profile_key = $encryption_profile->getEncryptionKey()) {
      $default_key = $profile_key;
    }

    $form['encryption']['encryption_key'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Key'),
      '#description' => $this->t('Select the key used for encryption. Only key types that are allowed for the selected encryption method are listed here.'),
      '#options' => $keys,
      '#default_value' => empty($default_key) ? NULL : $default_key,
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * Get a list of allowed keys for the given encryption method.
   *
   * @param string $encryption_method
   *   The selected encryption method.
   * @return array
   *   A list of allowed keys.
   */
  protected function getAllowedKeys($encryption_method) {
    $allowed_keys = [];
    $encryption_method_definition = $this->encryptionMethods[$encryption_method];

    /** @var $key \Drupal\key\KeyInterface */
    foreach ($this->keys as $key) {
      $key_type = $key->getKeyType();
      $key_type_definition = $this->keyManager->getDefinition($key_type->getPluginId());

      // @TODO: remove this check and only get Keys of type EncryptionKeyType or child classes.
      // This still needs to be implemented in Key module first.
      // Don't allow keys with key types other than encryption.
      if ($key_type_definition['group'] != "encryption") {
        continue;
      }

      // Don't allow keys with incorrect sizes.
      if (isset($encryption_method_definition['key_size'])) {
        $allowed_key_sizes = $encryption_method_definition['key_size'];
        $key_type_config = $key_type->getConfiguration();
        if (!isset($key_type_config['key_size']) || !$this->validKeySize($key_type_config['key_size'], $allowed_key_sizes)) {
          continue;
        }
      }

      // Don't allow keys with incorrect key_type, if defined in the encryption
      // method definition.
      if (isset($encryption_method_definition['key_type']) && !empty($encryption_method_definition['key_type'])) {
        if (!in_array($key_type->getPluginId(), $encryption_method_definition['key_type'])) {
          continue;
        }
      }

      $key_id = $key->id();
      $key_title = $key->label();
      $allowed_keys[$key_id] = (string) $key_title;
    }
    return $allowed_keys;
  }

  /**
   * Validates if key size matches the allowed values of the encryption method.
   *
   * @param mixed $key_size
   *   The key size as defined by the Key type.
   * @param array $allowed_key_sizes
   *   The allowed key sizes as defined by the encryption method.
   * @return bool
   *   Whether or not the key size is valid.
   */
  protected function validKeySize($key_size, array $allowed_key_sizes) {
    $valid = FALSE;
    // Make sure we're dealing with an integer.
    // Strips out optional "_bits" suffix from Key module.
    $key_size = (int) $key_size;

    if (!empty($allowed_key_sizes)) {
      foreach ($allowed_key_sizes as $allowed) {
        // Check if allowed key size is a range or not.
        if (strpos($allowed, '-') !== FALSE) {
          list($min, $max) = explode('-', $allowed, 2);
          if (($min <= $key_size) && ($key_size <= $max)) {
            $valid = TRUE;
          }
        }
        else {
          if ($allowed == $key_size) {
            $valid = TRUE;
          }
        }
      }
    }

    return $valid;
  }

  /**
   * AJAX callback to update the dynamic settings on the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState object.
   *
   * @return array
   *   The element to update in the form.
   */
  public function ajaxUpdateSettings(array &$form, FormStateInterface $form_state) {
    return $form['encryption'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $encryption_profile = $this->entity;
    $status = $encryption_profile->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label encryption profile.', array(
        '%label' => $encryption_profile->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label encryption profile was not saved.', array(
        '%label' => $encryption_profile->label(),
      )));
    }
    $form_state->setRedirectUrl($encryption_profile->urlInfo('collection'));
  }

}
