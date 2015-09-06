<?php

/**
 * @file
 * Contains Drupal\encrypt\Form\EncryptionConfigurationForm.
 */

namespace Drupal\encrypt\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\encrypt\EncryptService;
use Drupal\key\KeyManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EncryptionConfigurationForm.
 *
 * @package Drupal\encrypt\Form
 */
class EncryptionConfigurationForm extends EntityForm {

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
   * Constructs a EncryptionConfigurationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Key\KeyManager $key_manager
   *   The ConditionManager for building the visibility UI.
   * @param \Drupal\Encrypt\EncryptService $encrypt_service
   *   The lazy context repository service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, KeyManager $key_manager, EncryptService $encrypt_service) {
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

    /** @var $encryption_configuration \Drupal\encrypt\Entity\EncryptionConfiguration */
    $encryption_configuration = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $encryption_configuration->label(),
      '#description' => $this->t("Label for the encryption configuration."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $encryption_configuration->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\encrypt\Entity\EncryptionConfiguration::load',
      ),
      '#disabled' => !$encryption_configuration->isNew(),
    );

    $keys = [];
    foreach ($this->key_manager->getKeys() as $key) {
      $key_id = $key->id();
      $key_title = $key->label();
      $keys[$key_id] = (string) $key_title;
    }

    $form['encryption_key'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Key'),
      '#description' => $this->t('Select the key used for encryption'),
      '#options' => $keys,
      '#default_value' => $encryption_configuration->getEncryptionKey(),
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
      '#default_value' => $encryption_configuration->getEncryptionMethod(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $encryption_configuration = $this->entity;
    $status = $encryption_configuration->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label encryption configuration.', array(
        '%label' => $encryption_configuration->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label encryption configuration was not saved.', array(
        '%label' => $encryption_configuration->label(),
      )));
    }
    $form_state->setRedirectUrl($encryption_configuration->urlInfo('collection'));
  }

}
