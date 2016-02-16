#!/bin/bash

# Add an optional statement to see that this is running in Travis CI.
echo "running drupal_ti/after/before_script.sh"

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# The first time this is run, it will install Drupal.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Change to the Drupal directory
cd "$DRUPAL_TI_DRUPAL_DIR"

# We need to enable seclib so it shows up
drush en encrypt_seclib -y

# Change to the Drupal directory
cd "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH/composer_manager"
echo "running composer manager init script"
php scripts/init.php

cd "$DRUPAL_TI_DRUPAL_DIR"
# list out commands for debugging
# composer
composer drupal-rebuild
# https://github.com/composer/composer/issues/1314
#composer drupal-update --no-interaction --prefer-source
composer update -n --lock --verbose
