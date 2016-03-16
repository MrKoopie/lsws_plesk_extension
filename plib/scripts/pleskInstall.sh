#!/bin/sh

##########################################################
# LiteSpeed Web Server Plugin for Plesk Panel
# @Author:   LiteSpeed Technologies, Inc. (http://www.litespeedtech.com)
# @Copyright: (c) 2013-2015
##########################################################

cd `dirname "$0"`
export PATH="/usr/sbin:/sbin:$PATH"
source ./functions.sh 2>/dev/null
if [ $? != 0 ]; then
    . ./functions.sh
    if [ $? != 0 ]; then
        echo [ERROR] Can not include 'functions.sh'.
        exit 1
    fi
fi


test_license()
{
    if [ -f "$LSINSTALL_DIR/serial.no" ]; then
	echo "Serial number is available. "
	cat "$LSINSTALL_DIR/serial.no"
	echo "Contacting licensing server ..."

	echo ""
	$LSINSTALL_DIR/bin/lshttpd -r
 
	if [ $? -eq 0 ]; then
	    echo "[OK] License key received."
	    $LSINSTALL_DIR/bin/lshttpd -t

      	    if [ $? -eq 0 ]; then
       		LICENSE_OK=1
            else
		echo "The license key received does not work."
	    fi
	fi
    fi

    if [ "x$LICENSE_OK" = "x" ]; then
	if [ -f "$LSINSTALL_DIR/trial.key" ]; then
	    $LSINSTALL_DIR/bin/lshttpd -t
	    if [ $? -ne 0 ]; then
		exit 1
	    fi
	else
	    cat <<EOF
[ERROR] Sorry, installation will abort without a valid license key.
 
For evaluation purpose, please obtain a trial license key from our web 
site http://www.litespeedtech.com, copy it to this directory 
and run Installer again.

If a production license has been purchased, please copy the serial number
from your confirmation email to this directory and run Installer again.

NOTE:
Please remember to set ftp to BINARY mode when you ftp trial.key from 
another machine.

EOF
	    exit 1
	fi

    fi

}

installLicense()
{
	if [ -f $LSINSTALL_DIR/serial.no ]; then
		cp -f $LSINSTALL_DIR/serial.no $LSWS_HOME/conf
		chown "$CONF_OWN" $LSWS_HOME/conf/serial.no
		chmod "$CONF_MOD" $LSWS_HOME/conf/serial.no
	fi

	if [ -f $LSINSTALL_DIR/license.key ]; then
		cp -f $LSINSTALL_DIR/license.key $LSWS_HOME/conf
		chown "$CONF_OWN" $LSWS_HOME/conf/license.key
		chmod "$CONF_MOD" $LSWS_HOME/conf/license.key
	fi

	if [ -f $LSINSTALL_DIR/trial.key ]; then
		cp -f $LSINSTALL_DIR/trial.key $LSWS_HOME/conf
		chown "$CONF_OWN" $LSWS_HOME/conf/trial.key
		chmod "$CONF_MOD" $LSWS_HOME/conf/trial.key
	fi
}



LSINSTALL_DIR=`dirname "$0"`
cd $LSINSTALL_DIR

init

INSTALL_TYPE="reinstall"
LSWS_HOME=$1
AP_PORT_OFFSET=$2 
PHP_SUEXEC=$3 # 1 or 0
PHP_SUFFIX=php
ADMIN_USER=$4
PASS_ONE=$5
ADMIN_EMAIL=$6

SETUP_PHP=1
ADMIN_PORT=7088
DEFAULT_PORT=8088

HOST_PANEL="plesk"
USER_INFO=`id apache 2>/dev/null`
TST_USER=`expr "$USER_INFO" : 'uid=.*(\(.*\)) gid=.*'`
if [ "x$TST_USER" = "xapache" ]; then
    WS_USER=apache
    WS_GROUP=apache
    PANEL_VARY=""
else
    WS_USER=www-data
    WS_GROUP=www-data
    PANEL_VARY=".debian"
    source /etc/apache2/envvars 2>/dev/null
    if [ $? != 0 ]; then
        . /etc/apache2/envvars
    fi
fi
DIR_OWN=$WS_USER:$WS_GROUP
CONF_OWN=$WS_USER:$WS_GROUP
 
if [ 'x$ADMIN_USER' != 'x' ] && [ 'x$PASS_ONE' != 'x' ]; then 
    if [ -e "$LSINSTALL_DIR/admin/fcgi-bin/admin_php5" ] ; then
        ADMIN_PHP=${LSINSTALL_DIR}/admin/fcgi-bin/admin_php5
    else
        ADMIN_PHP=${LSINSTALL_DIR}/admin/fcgi-bin/admin_php
    fi

    ENCRYPT_PASS=`${ADMIN_PHP} -q "$LSINSTALL_DIR/admin/misc/htpasswd.php" $PASS_ONE`
    echo "$ADMIN_USER:$ENCRYPT_PASS" > "$LSINSTALL_DIR/admin/conf/htpasswd"
fi

configRuby

if [ ! -e "$LSWS_HOME" ]; then
    mkdir  "$LSWS_HOME"
fi

test_license


cat <<EOF

Installing LiteSpeed web server, please wait... 

EOF

buildApConfigFiles

installation

installLicense

echo ""
$LSWS_HOME/admin/misc/rc-inst.sh

