<?php

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface EncryptionMethodInterface
 * @package Drupal\encrypt
 */
interface EncryptionMethodInterface extends PluginInspectionInterface {

    /**
     * @return mixed
     */
    public function encrypt($text, $key);

    /**
     * @return mixed
     */
    public function decrypt($text, $key);

    /**
     * @return mixed
     */
    public function checkDependencies($text = NULL, $key = NULL);
}