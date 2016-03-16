<?php
/********************************************
 * LiteSpeed Web Server Plugin for Plesk Panel
* @Author:   LiteSpeed Technologies, Inc. (http://www.litespeedtech.com)
* @Copyright: (c) 2013-2015
*********************************************/

if (!method_exists('pm_ApiCli', 'callSbin')) {

    $psabase = '/usr/local/psa';
    if (file_exists('/opt/psa/version') && !file_exists('usr/local/psa/version'))
        $psabase = '/opt/psa';

    $dir1 = $psabase . '/admin/bin/modules/litespeed';
	$dir2 = $psabase . '/admin/sbin/modules/litespeed';

	if (file_exists($dir1)) {
		// can only check dir1, no access to dir2

		echo "<pre>For security reasons, please login via ssh as root user to mannually run the command

		rm -rf $dir1 $dir2

	then come back here to remove the rest of the package.</pre>";

		exit(1);
	}

	echo "Successfully uninstalled LiteSpeed";
}
