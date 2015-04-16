#!/bin/bash
# Autor:		Oscar Maldonado
# Creación:		2015-04-16
# Descripción:	Prueba ejecución de archivo shell 
#-O3M-#

LOG_FILE=/www/cronjobs/cron/test/test.sh.$(date +%Y%m%d-%H%M%S).log
echo "Ejecutado el: $(date +%Y%m%d-%H%M%S)" >> $LOG_FILE
echo "Ejecutado el: $(date +%Y%m%d-%H%M%S)"
#-O3M-#