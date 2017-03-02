# HealthProvider

This is a set of long running PHP/Laravel data mining console commands to generate csvs of phone, address, etc information of health companies. 

Rational to Release: It was the sole code in a Ubuntu VM that took up 40GB. The code, the last time run, yes cached just slightly less than 40GB of data. (Written circa 2015)

## Version Notes

* 1.1 Feb 2017 - Recast it as a PHP 7.1/Laravel 5.4 app setup to use Laravel/Homestead VM. I love the Vagrant integration of homestead because this means I could change all the caching folders to point to /tmp, and so do a vagrant destroy homestead-7 if the file caching got excessive, then vagrant up a clean copy. Excellent. Would have saved me 40GB of space and a lot of hassle if it had been around when I actually needed the code. :) 

* 1.0 Aug 2015 - Quick build out of PHP 5.x/Laravel 4 version that ran in it's own Ubuntu 14 VM.

## Operations Notes 

php artisan migrate
php artisan db:seed

### php artisan nursinghomecompare

generates public\nhc.csv

### php artisan homehealthcompare

generates public\hhc.csv

