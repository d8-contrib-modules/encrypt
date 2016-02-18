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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EncryptionProfileForm.
 *
 * @package Drupal\encrypt\Form
 */
class EncryptionProfileForm extends EntityForm {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The EncryptService definition.
   *
   * @var \Drupal\encrypt\EncryptService
   */
  protected $encryptService;

  /**
   * Keeps track of extra confirmation step on profile edit.
   *
   * @var bool
   */
  protected $edit_confirmed = FALSE;

  /**
   * Constructs a EncryptionProfileForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Encrypt\EncryptService $encrypt_service
   *   The lazy context repository service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EncryptService $encrypt_service) {
    $this->configFactory = $config_factory;
    $this->encryptService = $encrypt_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('encryption')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var $encryption_profile \Drupal\encrypt\Entity\EncryptionProfile */
    $encryption_profile = $this->entity;

    $disabled = FALSE;
    if ($this->operation == "edit" && !$this->edit_confirmed) {
      $disabled = TRUE;
    }

    if ($disabled) {
      $form['confirm_edit'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('I understand the risks of editing this encryption profile.'),
        '#description' => $this->t('Please acknowledge that you understand editing this encryption profile will make data that was previously encrypted with this profile <strong>unencryptable</strong>. After you checked this box and pressed the "Save" button, you will be able to edit this existing profile.'),
      );
    }

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $encryption_profile->label(),
      '#description' => $this->t("Label for the encryption profile."),
      '#required' => TRUE,
      '#disabled' => $disabled,
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

    $encryption_methods = $this->encryptService->loadEncryptionMethods();
    $method_options = [];
    foreach ($encryption_methods as $plugin_id => $definition) {
      $method_options[$plugin_id] = (string) $definition['title'];
    }
    $form['encryption']['encryption_method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Encryption Method'),
      '#description' => $this->t('Select the method used for encryption'),
      '#options' => $method_options,
      '#required' => TRUE,
      '#default_value' => $encryption_profile->getEncryptionMethodId(),
      '#ajax' => array(
        'callback' => [$this, 'ajaxUpdateSettings'],
        'event' => 'change',
        'wrapper' => 'encrypt-settings',
      ),
      '#disabled' => $disabled,
    );

    $form['encryption']['encryption_key'] = array(
      '#type' => 'key_select',
      '#title' => $this->t('Encryption Key'),
      '#required' => TRUE,
      '#default_value' => $encryption_profile->getEncryptionKeyId(),
      '#disabled' => $disabled,
    );

    if ($current_encryption_method = $encryption_profile->getEncryptionMethodId()) {
      $key_type_filter = $encryption_methods[$current_encryption_method]['key_type'];
      if (!empty($key_type_filter)) {
        $form['encryption']['encryption_key']['#key_filters'] = ['type' => $key_type_filter];
      }
    }

    return $form;
  }

  /**
   * AJAX callback to update the dynamic settings on the form.
   *
   * @param array $form
   *   The form definition array for the encryption profile form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Only validate when submitting the form, not on AJAX rebuild.
    if (!$form_state->isSubmitted()) {
      return;
    }

    // Check if we can enable full profile editing,
    // after explicit user confirmation.
    if ($this->operation == "edit" && $this->edit_confirmed != TRUE) {
      $form_state->setRebuild();
      if ($form_state->getValue('confirm_edit') == TRUE) {
        $this->edit_confirmed = TRUE;
        return;
      }
    }

    $form_state->cleanValues();
    /** @var \Drupal\encrypt\Entity\EncryptionConfiguration $entity */
    $this->entity = $this->buildEntity($form, $form_state);

    $errors = $this->entity->validate();
    if ($errors) {
      $form_state->setErrorByName('encryption_key', implode(';', $errors));
    }
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
