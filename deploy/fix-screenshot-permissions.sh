#!/bin/bash
  # Fix screenshot permissions after generation

  SCREENSHOTS_DIR="/var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/app/public/screenshots"
  SYSUSER="wp-templates.metanow_r6s2v1oe7wr"

  if [ -d "$SCREENSHOTS_DIR" ]; then
      echo "🔒 Fixing screenshot permissions..."

      # Set directory and file permissions
      find "$SCREENSHOTS_DIR" -type d -exec chmod 755 {} \;
      find "$SCREENSHOTS_DIR" -type f -exec chmod 644 {} \;

      # Set correct ownership
      chown -R "$SYSUSER:psacln" "$SCREENSHOTS_DIR"

      echo "✅ Screenshot permissions fixed"
  else
      echo "❌ Screenshots directory not found: $SCREENSHOTS_DIR"
  fi
