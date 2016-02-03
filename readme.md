# Encrypt Module for Drupal 8

This module provides a global encryption service that can be invoked via the services interface.

## Architecture

Encrypt leverages the Drupal 8 Plugin API for Encryption Methods. It also leverages the Key module for maintenance of
encryption Keys. 

Plugins allow for extensibility for customized needs. 

## Settings

The service is configured through the settings form, found at `admin/config/security/encryption`.

It requires a key, which is provided by the Key module. To manage keys, visit `admin/config/system/key`.

## Use of Services

After configuring the service, the service provides the ability to encrypt and decrypt.

### Encrypt
`$encryption_profile = \Drupal::service('entity.manager')->getStorage('encryption_profile')->load($instance_id);`
`Drupal::service('encryption')->encrypt($string, $encryption_profile);`


### Decrypt
`$encryption_profile = \Drupal::service('entity.manager')->getStorage('encryption_profile')->load($instance_id);`
`Drupal::service('encryption')->decrypt($string, $encryption_profile);`
