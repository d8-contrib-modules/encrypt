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

drush en composer_manager -y
cd "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH/composer_manager"
php scripts/init.php
cd "$DRUPAL_TI_DRUPAL_DIR"
composer drupal-install

