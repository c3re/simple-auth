FROM php:8.3-apache

RUN apt-get update -y && apt-get install -y  libpng-dev

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 604800/g" /usr/local/etc/php/php.ini
RUN sed -i "s/session.cookie_lifetime = 0/session.cookie_lifetime = 604800/g" /usr/local/etc/php/php.ini


COPY src/ /var/www/html/
