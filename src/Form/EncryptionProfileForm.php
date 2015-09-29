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
use Drupal\key\KeyManager;
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
  protected $config_factory;

  /**
   * KeyManager definition.
   *
   * @var \Drupal\key\KeyManager
   */
  protected $key_manager;

  /**
   * EncryptService definition.
   *
   * @var \Drupal\encrypt\EncryptService
   */
  protected $encrypt_service;

  /**
   * Constructs a EncryptionProfileForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Key\KeyManager $key_manager
   *   The ConditionManager for building the visibility UI.
   * @param \Drupal\Encrypt\EncryptService $encrypt_service
   *   The lazy context repository service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyManager $key_manager, EncryptService $encrypt_service) {
    $this->config_factory = $config_factory;
    $this->key_manager = $key_manager;
    $this->encrypt_service = $encrypt_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('key_manager'),
      $container->get('encryption')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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

    $keys = ['default' => 'System Default'];
    /** @var $key \Drupal\key\Entity\KeyInterface */
    foreach ($this->key_manager->getKeys() as $key) {
      $key_id = $key->id();
      $key_title = $key->label();
      $keys[$key_id] = (string) $key_title;
    }

    $default_key = 'default';
    if ($profile_key = $encryption_profile->getEncryptionKey()) {
      $default_key = $profile_key;
    }

    $form['encryption_key'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Key'),
      '#description' => $this->t('Select the key used for encryption.'),
      '#options' => $keys,
      '#default_value' => $default_key,
    );

    $enc_methods = [];
    foreach ($this->encrypt_service->loadEncryptionMethods() as $plugin_id => $definition) {
      $enc_methods[$plugin_id] = (string) $definition['title'];
    }
    $form['encryption_method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Method'),
      '#description' => $this->t('Select the method used for encryption'),
      '#options' => $enc_methods,
      '#default_value' => $encryption_profile->getEncryptionMethod(),
    );

    return $form;
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
