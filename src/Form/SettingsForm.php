<?php

/**
 * @file
 * Contains Drupal\encrypt\Form\SettingsForm.
 */

namespace Drupal\encrypt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\encrypt\EncryptService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\key\KeyManager;

/**
 * Class SettingsForm.
 *
 * @package Drupal\encrypt\Form
 */
class EncryptSettingsForm extends ConfigFormBase {

  /**
   * Drupal\key\KeyManager definition.
   *
   * @var Drupal\key\KeyManager
   */
  protected $key_manager;

  /**
   * Drupal\encrypt\EncryptService definition.
   *
   * @var Drupal\encrypt\EncryptService
   */
  protected $encrypt_service;

  public function __construct(ConfigFactoryInterface $config_factory, KeyManager $key_manager, EncryptService $encrypt_service) {
    parent::__construct($config_factory);
    $this->key_manager = $key_manager;
    $this->encrypt_service = $encrypt_service;
  }

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
  protected function getEditableConfigNames() {
    return [
      'encrypt.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'encrypt_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('encrypt.settings');

    $keys = [];
    foreach ($this->key_manager->getKeys() as $key_id => $definition) {
      $keys[$key_id] = (string) $definition['title'];
    }
    $form['encryption_key'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Key'),
      '#description' => $this->t('Select the key used for encryption'),
      '#options' => $keys,
      '#default_value' => $config->get('encryption_key'),
    );

    $enc_methods = [];
    foreach ($this->encrypt_service->getDefinitions() as $plugin_id => $definition) {
      $enc_methods[$plugin_id] = (string) $definition['title'];
    }
    $form['encryption_method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Method'),
      '#description' => $this->t('Select the method used for encryption'),
      '#options' => $enc_methods,
      '#default_value' => $config->get('encryption_method'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('encrypt.settings')
      ->set('encryption_key', $form_state->getValue('encryption_key'))
      ->set('encryption_method', $form_state->getValue('encryption_method'))
      ->save();
  }

}
