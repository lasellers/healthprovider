# docker build --tag lasellers/healthprovider .
# docker stop hp
# docker rm hp
## docker run -it --rm -v %cd%:/var/www/ lasellers/healthprovider
# docker run -d -v %cd%:/var/www --rm --name hp lasellers/healthprovider
## docker run -v "C:\Users\Lenovo\Dropbox\projects\PHP\healthprovider\:/var/www/" -d --name hp lasellers/healthprovider
# docker ps --all
# docker exec -it hp /bin/bash
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
EXPOSE 80
#VOLUME ["/var/www"]
#, "/var/log/apache2", "/etc/apache2"]
RUN "/bin/bash"
#ENTRYPOINT /usr/sbin/httpd -D FOREGROUND

# https://getcomposer.org/download/
#  php artisan healthprovider:status