## HealthProvider

This is a set of long running PHP/Laravel data mining commands to generate csvs of phone, address, etc information of health companies. 

Rational to Release: It was the sole code in a Ubuntu VM that took up 40GB. The code, the last time run, yes cached just slightly less than 40GB of data. (Written circa 2015)


php artisan migrate
php artisan db:seed

## php artisan nursinghomecompare

generates public\nhc.csv

## php artisan homehealthcompare

generates public\hhc.csv

