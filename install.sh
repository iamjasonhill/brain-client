#!/bin/bash

# Brain Nucleus Client Installation Script
# This script fetches the latest client files from the standalone brain-client repository

set -e

REPO_URL="https://github.com/iamjasonhill/brain-client.git"
TEMP_DIR=$(mktemp -d)

echo "🚀 Installing Brain Nucleus Client..."
echo ""

# Clone the repository
echo "📥 Fetching latest files from repository..."
git clone --depth 1 "$REPO_URL" "$TEMP_DIR"

# Copy files to current directory without the git metadata
echo "📋 Copying client files..."
rsync -a --exclude='.git' "$TEMP_DIR"/ ./

# Cleanup
rm -rf "$TEMP_DIR"

echo ""
echo "✅ Brain Nucleus Client installed successfully!"
echo ""
echo "Next steps:"
echo "1. Copy BrainEventClient.php to your app (e.g., app/Services/BrainEventClient.php)"
echo "2. Update the namespace if needed"
echo "3. See INSTALLATION.md for configuration instructions"
echo ""
