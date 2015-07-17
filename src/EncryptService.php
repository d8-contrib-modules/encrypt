<?php

/**
 * @file
 * Contains Drupal\encrypt\EncryptService.
 */

namespace Drupal\encrypt;

use Drupal\key\KeyManager;

/**
 * Class EncryptService.
 *
 * @package Drupal\encrypt
 */
class EncryptService {

  /**
   * Drupal\key\KeyManager definition.
   *
   * @var Drupal\key\KeyManager
   */
  protected $key_manager;
  /**
   * Constructor.
   */
  public function __construct(KeyManager $key_manager) {
    $this->key_manager = $key_manager;
  }

}
