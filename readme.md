# Encrypt Module for Drupal 8

This module provides a global encryption service that can be invoked via the 
services interface.

## Architecture

Encrypt leverages the Drupal 8 Plugin API for Encryption Methods. It also 
leverages the Key module for maintenance of encryption Keys. 

Plugins allow for extensibility for customized needs. 

## Settings

The service is configured through the settings form, found at 
`/admin/config/system/encryption`.

It requires a key, which is provided by the Key module. To manage keys, visit 
`admin/config/system/key`.

## Best practices

In order to provide real security, it is highly recommended to follow these 
best practices:

### Encryption method

Use a high-quality, modern security library for encrypting your data.
The Real AES module (https://www.drupal.org/project/real_aes) provides 
integration with the recommended Defuse PHP Encryption library.

Read the README.txt document provided by the Real AES module for detailed 
security information and best practices, as well as further background 
information.

### Key

Be sure to use a key value with an appropriately secure size (at least 128 bits)
and decent quality (i.e. proper randomness).

Make sure to store your keys in an appropriately secure place. Keep your keys
out of the database, out of the web root and on a different server, if possible.

The "Configuration" key provider (as defined by the Key module) should only be
used for testing purposes. Never use this key provider in a production 
environment, or any environment where security is required. 


## Use of Services

After configuring the service, the service provides the ability to encrypt and 
decrypt using your encryption profile (machine name).

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
$encryption_profile = \Drupal::service('entity.manager')
  ->getStorage('encryption_profile')->load($instance_id);
```

## Writing your own EncryptionMethod plugin

In you want to write your own encryption method plugin, you should extend the
EncryptionMethodBase class and implement the methods defined by the 
EncryptionMethodInterface. See the TestEncryptionMethod class in the 
encrypt_test module bundled in the "tests" directory of this module.

Optionally, your encryption method plugin can provide a configuration form, that
will automatically be shown upon creation of an EncryptionProfile entity.
In this case you'll also need to implement EncryptionMethodPluginFormInterface
and create its required methods. See the ConfigTestEncryptionMethod class in the
encrypt_test module for a simple example.
