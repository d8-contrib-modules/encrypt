<?php

/**
 * @file
 * Contains \Drupal\Core\Block\Annotation\Block.
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