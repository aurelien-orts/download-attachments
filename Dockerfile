FROM php:7.4-cli

# RUN touch /usr/local/etc/php/php.ini

# RUN echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini
# RUN echo "upload_max_filesize = 128M" >> /usr/local/etc/php/php.ini
# RUN echo "post_max_size = 128M" >> /usr/local/etc/php/php.ini

# RUN apk add --no-cache ffmpeg

ENV ATTACHMENTS_MAILBOX email@gmail.com
ENV ATTACHMENTS_USER email@gmail.com
ENV ATTACHMENTS_PASS your-generated-password
ENV ATTACHMENTS_QUERY UNSEEN

#COPY ./src /var/www/html/src
#COPY ./dwn /var/www/html/dwn

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils libzip-dev libssl-dev \
    ## Zip extension
    && apt-get install -y --no-install-recommends zlib1g-dev \
    && docker-php-ext-install zip \
    ## Imap extension
    && apt-get install -y --no-install-recommends libc-client-dev libkrb5-dev \
    && PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

WORKDIR /var/www/html

# RUN chmod -R 777 storage

CMD [ "php", "./src/download-attachments.php" ]