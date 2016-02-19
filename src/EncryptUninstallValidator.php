<?php

/**
 * @file
 * Contains \Drupal\encrypt\EncryptUninstallValidator.
 */

namespace Drupal\encrypt;

use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents uninstallation of modules if an encryption method is still used.
 */
class EncryptUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The encrypt service.
   *
   * @var \Drupal\encrypt\EncryptService
   */
  protected $encryptService;

  /**
   * The encryption profile manager.
   *
   * @var \Drupal\encrypt\EncryptionProfileManagerInterface
   */
  protected $encryptionProfileManager;

  /**
   * Constructs a new RoleAssignUninstallValidator.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\encrypt\EncryptServiceInterface $encrypt_service
   *   The encryption service.
   * @param \Drupal\encrypt\EncryptionProfileManagerInterface $encryption_profile_manager
   *   The encryption profile manager.
   */
  public function __construct(TranslationInterface $string_translation, EncryptServiceInterface $encrypt_service, EncryptionProfileManagerInterface $encryption_profile_manager) {
    $this->stringTranslation = $string_translation;
    $this->encryptService = $encrypt_service;
    $this->encryptionProfileManager = $encryption_profile_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    $encryption_method_plugins = [];
    // Check if this module provides one or more EncryptionMethod plugins.
    $definitions = $this->encryptService->loadEncryptionMethods();
    foreach ($definitions as $definition) {
      if ($definition['provider'] == $module) {
        $encryption_method_plugins[] = $definition['id'];
      }
    }

    // If the module provides EncryptionMethod plugins, check if they are used.
    if (!empty($encryption_method_plugins)) {
      foreach ($encryption_method_plugins as $plugin_id) {
        if ($profiles = $this->encryptionProfileManager->getEncryptionProfilesByEncryptionMethod($plugin_id)) {
          $used_in = [];
          foreach ($profiles as $encryption_profile) {
            $used_in[] = $encryption_profile->label();
          }
          $reasons[] = $this->t('Provides an encryption method that is in use in the following encryption profiles: %profiles', ['%profiles' => implode(', ', $used_in)]);
        }
      }
    }

    return $reasons;
  }

}
