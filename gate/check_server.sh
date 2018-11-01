#!/bin/bash
count=`ps -fe |grep "serv.php" | grep -v "grep" | grep "root" | wc -l`
echo $count
if [ $count -lt 1 ]; then
ps -eaf |grep "serv.php" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ulimit -c unlimited
php /home/wwwroot/default/gate/serv.php
echo "restart";
echo $(date +%Y-%m-%d_%H:%M:%S) >/home/wwwroot/default/gate/log/restart.log
fi