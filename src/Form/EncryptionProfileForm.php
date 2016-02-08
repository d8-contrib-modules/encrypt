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
   * Constructs a EncryptionProfileForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Key\KeyRepository $key_repository
   *   The ConditionManager for building the visibility UI.
   * @param \Drupal\Encrypt\EncryptService $encrypt_service
   *   The encrypt service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyRepository $key_repository, EncryptService $encrypt_service) {
    $this->configFactory = $config_factory;
    $this->keys = $key_repository->getKeys();
    $this->encryptionMethods = $encrypt_service->loadEncryptionMethods();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('key.repository'),
      $container->get('encryption')
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

    $keys = $encryption_profile->getAllowedKeys($current_encryption_method);
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $form_state->cleanValues();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $this->entity = $this->buildEntity($form, $form_state);

    $errors = $this->entity->validate();
    if ($errors) {
      $form_state->setErrorByName('encryption_key', implode(';', $errors));
    }
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
