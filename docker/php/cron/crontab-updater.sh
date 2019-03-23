#!/bin/sh
# Вставляем все задания из папочки и перезагружаем задание
cat /crontab/crontab.* | /usr/bin/crontab
/usr/bin/supervisorctl restart cron