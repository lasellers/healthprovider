# docker-compose up --build
# docker exec -it healthprovider_database_1 /bin/bash
# docker ps --all
# docker images
# docker volume ls
# docker volume inspect healthprovider_dbdata
# docker-compose exec app php artisan key:generate
# docker-compose exec app php artisan optimize
# docker-compose exec app php artisan migrate --seed
FROM php:7.2.2-fpm

RUN apt-get update 
RUN apt-get install -y nano
RUN apt-get install -y libmcrypt-dev \
    mysql-client libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install mcrypt pdo_mysql
#RUN apt-get install -y libapache2-mod-php5
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd
RUN apt-get install -y git
# sudo apt-get install php-mbstring

#RUN php composer.phar self-update
#RUN php composer.phar update
#RUN php composer.phar diagnose
#RUN php artisan route:list

WORKDIR /var/www

RUN "/bin/bash"