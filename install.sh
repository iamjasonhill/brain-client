#!/bin/bash

# Brain Nucleus Client Installation Script
# This script fetches the latest client files from the Brain Nucleus repository

set -e

REPO_URL="https://github.com/iamjasonhill/thebrain.git"
TEMP_DIR=$(mktemp -d)
CLIENT_DIR="brain-client"

echo "🚀 Installing Brain Nucleus Client..."
echo ""

# Clone the repository
echo "📥 Fetching latest files from repository..."
git clone --depth 1 --filter=blob:none --sparse "$REPO_URL" "$TEMP_DIR"
cd "$TEMP_DIR"
git sparse-checkout set "$CLIENT_DIR"

# Copy files to current directory
echo "📋 Copying client files..."
cp -r "$CLIENT_DIR"/* .

# Cleanup
cd - > /dev/null
rm -rf "$TEMP_DIR"

echo ""
echo "✅ Brain Nucleus Client installed successfully!"
echo ""
echo "Next steps:"
echo "1. Copy BrainEventClient.php to your app (e.g., app/Services/BrainEventClient.php)"
echo "2. Update the namespace if needed"
echo "3. See INSTALLATION.md for configuration instructions"
echo ""

