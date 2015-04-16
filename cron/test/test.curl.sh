#!/bin/bash
# Autor:		Oscar Maldonado
# Creación:		2015-04-16
# Descripción:	Prueba ejecución de archivo shell 
#-O3M-#

curl http://192.168.228.178/cronjobs/cron/test/crea.txt.php
echo "Ejecutado el: $(date +%Y%m%d-%H%M%S)"
#-O3M-#