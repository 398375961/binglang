#!/bin/bash
ps -eaf |grep "serv.php" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ps -eaf |grep "serv.php" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ulimit -c unlimited
php /home/wwwroot/autosale.qubinglang.com/gate/serv.php
echo "restart";
echo $(date +%Y-%m-%d_%H:%M:%S) >/home/wwwroot/autosale.qubinglang.com/gate/log/restart.log