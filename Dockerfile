FROM php:7.4-cli as php-cli

ENV ATTACHMENTS_MAILBOX {imap.gmail.com:993/imap/ssl}INBOX
ENV ATTACHMENTS_USER email@gmail.com
ENV ATTACHMENTS_PASS your-generated-password
ENV ATTACHMENTS_QUERY UNSEEN

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils libzip-dev libssl-dev \
    ## Zip extension
    && apt-get install -y --no-install-recommends zlib1g-dev \
    && docker-php-ext-install zip \
    ## Imap extension
    && apt-get install -y --no-install-recommends libc-client-dev libkrb5-dev \
    && PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

WORKDIR /var/www/html

CMD [ "php", "./src/download-attachments.php" ]

#####################################################################################
#####################################################################################

FROM php-cli as app

COPY ./src /var/www/html/src
COPY ./dwn /var/www/html/dwn

RUN chmod -R 777 /var/www/html/dwn