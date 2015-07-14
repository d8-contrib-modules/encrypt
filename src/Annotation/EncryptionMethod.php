<?php

/**
 * @file
 * Contains \Drupal\Core\Block\Annotation\Block.
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