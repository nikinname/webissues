FROM php:7.4-apache
RUN apt-get update \
  && apt-get install -y libonig-dev libpq-dev \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install mbstring \
  && a2enmod rewrite \
  && service apache2 restart
