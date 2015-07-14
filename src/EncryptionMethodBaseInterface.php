<?php

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface EncryptionMethodBaseInterface
 * @package Drupal\encrypt
 */
interface EncryptionMethodBaseInterface extends PluginInspectionInterface {

    /**
     * @return mixed
     */
    public function getDependencies();

    /**
     * @return mixed
     */
    public function encrypt($text, $key, $options = array());

    /**
     * @return mixed
     */
    public function decrypt($text, $key, $options = array());
}