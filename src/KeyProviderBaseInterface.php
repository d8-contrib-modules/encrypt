<?php

namespace Drupal\encrypt;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface KeyProviderBaseInterface
 * @package Drupal\encrypt
 */
interface KeyProviderBaseInterface extends PluginInspectionInterface {
    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @return mixed
     */
    public function getDependencies();
}