<?php

/**
 * @file
 * Contains Drupal\encrypt\Form\EncryptionProfileDefaultForm.
 */

namespace Drupal\encrypt\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to make encryption profile entities default.
 */
class EncryptionProfileDefaultForm extends EntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to make %name the default encryption?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.encryption_profile.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Set Default');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->setServiceDefault();

    drupal_set_message(
      $this->t('content @type: @label is now default.',
        [
          '@type' => $this->entity->bundle(),
          '@label' => $this->entity->label()
        ]
        )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
