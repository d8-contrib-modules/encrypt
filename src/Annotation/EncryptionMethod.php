<?php

/**
 * @file
 * Contains \Drupal\encrypt\Annotation\EncryptionMethod.
 */

namespace  Drupal\encrypt\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a EncryptionMethod annotation object.
 *
 * @ingroup encrypt
 *
 * @Annotation
 */
class EncryptionMethod extends Plugin {
  public $id;
  public $title;
  public $description = '';
}