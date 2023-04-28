# Download-attachments

This script allows you to download attachments from your GMAIL 

It's inspired by 
https://www.dunnies-it.com/php/a-php-imap-script-to-download-gmail-attachments.php

## How to run

1) Define your personal token https://devanswe.rs/enable-2-step-verification-google-account
2) `docker run -e ATTACHMENTS_USER='your@gmail.com' -e ATTACHMENTS_PASS='your-token' -v ./your-path/:/var/www/html/dwn ortys4/download-attachments:latest`

## Builder le projet

### Mode dev
`docker compose build`

### Mode app (déploeiemnt)
`bash build.sh && docker push docker.io/ortys4/download-attachments:latest`