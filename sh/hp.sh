# sh sh/hp.sh
# cd ..

tar -zcvf ../healthproviders.tar.gz ./ --exclude="cache/*" --exclude="data/*" --exclude="*.csv" --exclude="*.zip" --exclude="*.xml" --exclude="*.xls" --exclude="*.xlsx"

# cd sh
