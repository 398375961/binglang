#!/bin/bash
filename=`date +%Y%m%d`
mysqldump -uroot -p db>/data/db.sql
zip /data/db_autosale_$filename.sql.zip /data/db.sql
rm -f /data/db.sql
find /home/wwwroot/default/gate/log/ -mtime +30 -exec rm -f {} \;
