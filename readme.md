# Encrypt Module for Drupal 8

This module provides a global encryption service that can be invoked via the services interface.

## Architecture

Encrypt leverages the Drupal 8 Plugin API for Encryption Methods. It also leverages the Key module for maintenance of
encryption Keys. 

Plugins allow for extensibility for customized needs. 

## Settings

The service is configured through the settings form, found at `/admin/config/system/encryption`.

It requires a key, which is provided by the Key module. To manage keys, visit `admin/config/system/key`.

## Use of Services

After configuring the service, the service provides the ability to encrypt and decrypt using your encryption profile (machine name).

### Encrypt
```
use Drupal\encrypt\Entity\EncryptionProfile;
$encryption_profile = EncryptionProfile::load($instance_id);
Drupal::service('encryption')->encrypt($string, $encryption_profile);
```

### Decrypt
```
use Drupal\encrypt\Entity\EncryptionProfile;
$encryption_profile = EncryptionProfile::load($instance_id);
Drupal::service('encryption')->decrypt($string, $encryption_profile);
```

### Note
If you don't want to use the "use" statement in the examples above, you can
use the following code to retrieve the encryption profile:

```
$encryption_profile = \Drupal::service('entity.manager')->getStorage('encryption_profile')->load($instance_id);
```
