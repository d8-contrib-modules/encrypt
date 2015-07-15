<?php

/**
 * @file
 * Contains \Drupal\encrypt\Annotation\KeyProvider.
 */

namespace  Drupal\encrypt\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a KeyProvider annotation object.
 *
 * @ingroup encrypt
 *
 * @Annotation
 */
class KeyProvider extends Plugin {
  public $id;
  public $title;
  public $description = '';
  public $staticKey;
}