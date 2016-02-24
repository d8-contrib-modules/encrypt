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
use Drupal\encrypt\Plugin\EncryptionMethodPluginFormInterface;
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
   * The original encryption profile.
   *
   * @var \Drupal\encrypt\Entity\EncryptionProfile|NULL
   *   The original EncryptionProfile entity or NULL if this is a new one.
   */
  protected $originalProfile = NULL;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    // If the form is rebuilding.
    if ($form_state->isRebuilding()) {

      // If an encryption method change triggered the rebuild.
      if ($form_state->getTriggeringElement()['#name'] == 'encryption_method') {
        // Update the encryption method plugin.
        $this->updateEncryptionMethod($form_state);
      }
    }
    elseif ($this->operation == "edit") {
      // Only when the form is first built.
      /* @var $encryption_profile \Drupal\encrypt\Entity\EncryptionProfile */
      $encryption_profile = $this->entity;
      $this->originalProfile = clone $encryption_profile;
    }

    return parent::buildForm($form, $form_state);
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

    $form['encryption']['encryption_method_configuration'] = array(
      '#type' => 'container',
      '#title' => $this->t('Encryption method settings'),
      '#title_display' => FALSE,
      '#tree' => TRUE,
    );
    if ($encryption_profile->getEncryptionMethod() instanceof EncryptionMethodPluginFormInterface) {
      $plugin_form_state = $this->createPluginFormState($form_state);
      $form['encryption']['encryption_method_configuration'] += $encryption_profile->getEncryptionMethod()->buildConfigurationForm([], $plugin_form_state);
      $form_state->setValue('encryption_method_configuration', $plugin_form_state->getValues());
    }

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
   * Creates a FormStateInterface object for a plugin.
   *
   * @param FormStateInterface $form_state
   *   The form state to copy values from.
   *
   * @return FormStateInterface
   *   A clone of the form state object with values from the plugin.
   */
  protected function createPluginFormState(FormStateInterface $form_state) {
    // Clone the form state.
    $plugin_form_state = clone $form_state;

    // Clear the values, except for this plugin type's settings.
    $plugin_form_state->setValues($form_state->getValue('encryption_method_configuration', []));

    return $plugin_form_state;
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
   * Update the EncryptionMethod plugin.
   */
  protected function updateEncryptionMethod(FormStateInterface $form_state) {
    /* @var $encryption_profile \Drupal\encrypt\Entity\EncryptionProfile */
    $encryption_profile = $this->entity;

    /* @var $plugin \Drupal\encrypt\EncryptionMethodInterface */
    $plugin = $encryption_profile->getEncryptionMethod();

    $encryption_profile->setEncryptionMethod($plugin);

    // If an original profile exists and the plugin ID matches the existing one.
    if ($this->originalProfile && $this->originalProfile->getEncryptionMethod()->getPluginId() == $plugin->getPluginId()) {
      // Use the configuration from the original profile's plugin.
      $configuration = $this->originalProfile->getEncryptionMethod()->getConfiguration();
    }
    else {
      // Use the plugin's default configuration.
      $configuration = $plugin->defaultConfiguration();
    }

    $plugin->setConfiguration($configuration);
    $form_state->setValue('encryption_method_configuration', []);
    $form_state->getUserInput()['encryption_method_configuration'] = [];
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

    if ($plugin = $this->entity->getEncryptionMethod()) {
      if ($plugin instanceof EncryptionMethodPluginFormInterface) {
        $plugin_form_state = $this->createPluginFormState($form_state);
        $plugin->validateConfigurationForm($form, $plugin_form_state);
        $form_state->setValue('encryption_method_configuration', $plugin_form_state->getValues());
        $this->moveFormStateErrors($plugin_form_state, $form_state);
        $this->moveFormStateStorage($plugin_form_state, $form_state);
      }
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit plugin configuration if available.
    if ($plugin = $this->entity->getEncryptionMethod()) {
      if ($plugin instanceof EncryptionMethodPluginFormInterface) {
        $plugin_form_state = $this->createPluginFormState($form_state);
        $plugin->submitConfigurationForm($form, $plugin_form_state);
        $form_state->setValue('encryption_method_configuration', $plugin_form_state->getValues());
      }
    }

    parent::submitForm($form, $form_state);
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

  /**
   * Moves form errors from one form state to another.
   *
   * @param \Drupal\Core\Form\FormStateInterface $from
   *   The form state object to move from.
   * @param \Drupal\Core\Form\FormStateInterface $to
   *   The form state object to move to.
   */
  protected function moveFormStateErrors(FormStateInterface $from, FormStateInterface $to) {
    foreach ($from->getErrors() as $name => $error) {
      $to->setErrorByName($name, $error);
    }
  }

  /**
   * Moves storage variables from one form state to another.
   *
   * @param \Drupal\Core\Form\FormStateInterface $from
   *   The form state object to move from.
   * @param \Drupal\Core\Form\FormStateInterface $to
   *   The form state object to move to.
   */
  protected function moveFormStateStorage(FormStateInterface $from, FormStateInterface $to) {
    foreach ($from->getStorage() as $index => $value) {
      $to->set($index, $value);
    }
  }

}
