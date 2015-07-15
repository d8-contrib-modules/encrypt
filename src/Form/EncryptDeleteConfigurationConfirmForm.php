<?php

namespace Drupal\encrypt\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class EncryptDeleteConfigurationConfirmForm extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the configuration %id?', array('%id' => $this->config['label']));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('encrypt.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Nevermind');
  }

  /**
   * {@inheritdoc}
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $encrypt_config = NULL) {
    $this->config = encrypt_get_config($encrypt_config);
    $default_config = \Drupal::config('encrypt.settings')->get('encrypt_default_config');

    if ($default_config == $this->config['name']) {
      drupal_set_message(t('The default configuration cannot be deleted.'), 'error');
      return $this->redirect('encrypt.list');
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_delete('encrypt_config')
      ->condition('name', $this->config['name'])
      ->execute();

    $t_args = array('%label' => $this->config['label']);
    drupal_set_message(t('The configuration %label has been deleted.', $t_args));
    \Drupal::logger('encrypt')->notice('Deleted encryption configuration %label.', []);

    $form_state->setRedirect('encrypt.list');
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'encrypt_delete_config_confirm';
  }
}