#!/bin/bash

# Exit on error
set -e

# Store the current directory
PLUGIN_DIR="$(pwd)"

# Build the EuroClimateCheck project
cd EuroClimateCheck
pnpm install
pnpm build

# Create a temporary directory for packaging
cd ..
TEMP_DIR="$(mktemp -d)"
PLUGIN_NAME="WP-ClaimReview-EE24"

# Copy the main plugin file
cp euroclimatecheck-plugin.php "$TEMP_DIR/"

# Copy the inc directory
cp -r inc "$TEMP_DIR/"

# Copy the js directory
cp -r js "$TEMP_DIR/"

# Copy only the dist folder from EuroClimateCheck
mkdir -p "$TEMP_DIR/EuroClimateCheck"
cp -r EuroClimateCheck/dist "$TEMP_DIR/EuroClimateCheck/"

# Create the zip file
cd "$TEMP_DIR"
zip -r "$PLUGIN_DIR/$PLUGIN_NAME.zip" .

# Clean up
cd "$PLUGIN_DIR"
rm -rf "$TEMP_DIR"

echo "Package created successfully: $PLUGIN_NAME.zip" 