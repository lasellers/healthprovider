# HealthProvider

This is a set of long running PHP/Laravel 5 data mining console commands to generate csvs containing phone, address, etc information of health companies. 

As this was a quick one-off project that was never meant to run on production -- the raw csv output being the goal -- the original code was irrelevant.
As such, from time to I use the original code-base for various experiments such as:
1) Learning to upgrade from Laravel 4.x to 5.x
2) Learning how to write Dockerfile/Docker-compose files for Laravel projects.
3) Using 1+2 as an excuse to cleanup the code. :)

Rational to Release: 
It was the sole code in a Ubuntu VM that took up 40GB. 
That is, the code, the last time run to completion, cached just slightly less than 40GB of data. 
Breaking the code out of the original Vagrant VM allowed me to delete the 40GB VM and keep the code for experimentation purposes.

Note: The artisan CLI commands generate csv, html and txt files that are stored directly to the public/ folder.


## Version Notes

* 1.2 Feb 2018 - Created Dockerfile and docker-compose.yml as an experiment to bootup the project in docker and leave Vagrant, etc behind.

* 1.1 Feb 2017 - Recast it as a PHP 7.1/Laravel 5.4 app setup to use Laravel/Homestead VM. I love the Vagrant integration of homestead because this means I could change all the caching folders to point to /tmp, and so do a vagrant destroy homestead-7 if the file caching got excessive, then vagrant up a clean copy. Excellent. Would have saved me 40GB of space and a lot of hassle if it had been around when I actually needed the code. :) 

* 1.0 Aug 2015 - Quick build out of PHP 5.x/Laravel 4 version that ran in it's own Ubuntu 14 VM.


## Starting up locally with Docker

To start up everything from the CLI:

```docker-compose up --build```

To bring up a CLI inside the running app:

```docker exec -it healthprovider_app_1 /bin/bash```

To bring up the MySql CLI inside the DB container:

```
docker exec -it healthprovider_database_1 /bin/bash
mysql -u root -p
(secret)
```

Browse locally at [http://127.0.0.1:8080/](http://127.0.0.1:8080/)


## Operations Notes 

Change .env db to your local system. By default uses db name "healthprovider" with Laravel Homestead.

php artisan migrate
php artisan db:seed

`php artisan healthprovider:nhc`

Generates public\nhc.csv

`php artisan healthprovider:hhc`

Generates public\hhc.csv

`php artisan healthprovider:status`

Shows status info.


## Install References

https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx

https://composer.github.io/pubkeys.html

https://phpunit.de/getting-started/phpunit-6.html

    php composer.phar require --dev phpunit/phpunit ^5.7
    ./vendor/bin/phpunit --version

    php composer.phar require --dev symfony/browser-kit
    php composer.phar require --dev symfony/css-selector
     
    ./vendor/bin/phpunit

https://www.rosehosting.com/blog/install-npm-on-ubuntu-16-04/

    curl -sL https://deb.nodesource.com/setup_7.x | bash -
    sudo apt-get install -y nodejs


    php -v
    node -v
    npm -v
    ./vendor/bin/phpunit --version
    php composer.phar --version
    php composer.phar info
    

## TODO:

* Generally clean up everything
* Write to DB instead of just the *.csv files so the new Status command will display data.
* Add xdebug to docker and hook it up to PHPStorm
* Add a "clear" command to remove all data from file cache.
* Explore composer looking for modules that handle caching and move to using those as an experiment.
