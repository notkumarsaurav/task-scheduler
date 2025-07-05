#!/bin/bash

# Get the absolute path to the cron.php file
CRON_PATH=$(realpath "$(dirname "$0")/cron.php")

# Add the cron job to run every hour
(crontab -l 2>/dev/null; echo "0 * * * * /usr/bin/php $CRON_PATH >/dev/null 2>&1") | crontab -

echo "Cron job has been set up to run every hour."