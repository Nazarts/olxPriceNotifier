FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
  imagemagick \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmagickwand-dev --no-install-recommends \
  libpng-dev \
  && rm -rf /var/lib/apt/lists/* \
  && a2enmod rewrite \
  && docker-php-ext-install exif \
  && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd \
  && pecl install imagick && docker-php-ext-enable imagick \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install pdo pdo_mysql

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev libzip-dev sendmail

RUN apt-get update && apt-get -y install ssmtp cron wget

RUN wget -O phpunit https://phar.phpunit.de/phpunit-10.phar

RUN chmod +x phpunit

COPY ./conf/ssmtp.conf /etc/ssmtp/ssmtp.conf

RUN echo "sendmail_path=/usr/sbin/sendmail -t -i" >> /usr/local/etc/php/conf.d/sendmail.ini

RUN docker-php-ext-install zip

RUN sed -i '/#!\/bin\/sh/aservice sendmail restart' /usr/local/bin/docker-php-entrypoint

RUN sed -i '/#!\/bin\/sh/aecho "$(hostname -i)\t$(hostname) $(hostname).localhost" >> /etc/hosts' /usr/local/bin/docker-php-entrypoint

RUN service apache2 restart

# And clean up the image

RUN rm -rf /var/lib/apt/lists/*