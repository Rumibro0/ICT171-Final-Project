#!/bin/bash
# Run this once on the server to protect status.html from server-status.sh
# Make the file immutable so server-status.sh can't overwrite it
sudo chattr +i /var/www/html/status.html
echo "status.html is now protected from overwrite"
