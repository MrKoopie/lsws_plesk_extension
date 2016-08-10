<?php
/********************************************
* LiteSpeed Web Server Plugin for Plesk Panel
* @Author:   LiteSpeed Technologies, Inc. (https://www.litespeedtech.com)
* @Copyright: (c) 2013-2016
*********************************************/

class Modules_Litespeed_Util
{
	private $moduleCmd;

	public function __construct()
	{
		$this->Init();
	}

	public function Init()
	{
        $psabase = '/usr/local/psa';
        if (file_exists('/opt/psa/version') && !file_exists('usr/local/psa/version'))
            $psabase = '/opt/psa';

        define('PSA_BASE', $psabase);
		define('CGI_DIR', PSA_BASE . '/admin/bin/modules/litespeed/');
		$lswshome = defined('LSWS_HOME') ? LSWS_HOME : '/usr/local/lsws'; // default
		$this->moduleCmd = CGI_DIR . 'lsws_cmd ' . escapeshellarg($lswshome) . ' ';
	}

	public function ModuleInstalled()
	{
		if (!file_exists(PSA_BASE . '/admin/bin/modules/litespeed/lsws_cmd'))
			return 1 ; // not installed
		else if (file_exists(PSA_BASE . '/admin/plib/modules/litespeed/scripts/install_scripts'))
			return 2;
		else
			return 0;
	}

	public static function get_request_var($tag)
	{
		if (isset($_REQUEST[$tag]))
			return trim($_REQUEST[$tag]);
		else
			return NULL;
	}

	private function exec_cmd($cmd, array &$output = null, &$return_var = null)
	{
		exec($cmd, $output, $return_var);
	}

	public function IsLSRunning()
	{
		$cmd = $this->moduleCmd . 'CHECK_LSWS_RUNNING';
		$this->exec_cmd($cmd, $output, $return_var);
		return $output[0]; // pid
	}

	public function IsApacheRunning()
	{
		$cmd = $this->moduleCmd . 'CHECK_AP_RUNNING';
		$this->exec_cmd($cmd, $output);
		return $output[0]; // pid
	}

	public function IsNginxRunning()
	{
		$cmd = $this->moduleCmd . 'CHECK_NGINX_RUNNING';
		$this->exec_cmd($cmd, $output);
		return $output[0]; // ps output
	}

	public function GetCurrentSerialNo()
	{
		if (defined('LSWS_HOME')){
			$cmd = $this->moduleCmd . 'GET_SERIAL';
			$this->exec_cmd($cmd, $output);
			return $output[0];
		}
		return '';
	}

	public function GetCurrentVersion()
	{
		$cmd = $this->moduleCmd . 'GET_VERSION';
		$this->exec_cmd($cmd, $output);
		return $output[0];
	}

	public function GetNewVersion()
	{
		$cmd = $this->moduleCmd . 'GET_NEW_VERSION';
		$this->exec_cmd($cmd, $output, $return);
		$new_version = '';
		if ($return == 0) {
			$new_release = trim($output[0]);
			if (strpos($new_release, '-')) {
				if ( preg_match( '/^(.*)-/', $new_release, $matches ) ) {
					$new_version = $matches[1];
				}
			}
			else {
				$new_version = $new_release;
			}
		}
		return $new_version;
	}


	public function GetInstalledVersions()
	{
		$cmd = $this->moduleCmd . 'GET_INSTALLED_VERSIONS';
		$this->exec_cmd($cmd, $output, $return);

		$installed_releases = array();
		foreach($output as $o) {
			$m = array();
			if (preg_match('/\/lswsctrl\.(.*)$/', $o, $m)) {
				$installed_releases[] = $m[1];
			}
		}
		return $installed_releases;
	}

	public function GetAdminUrl()
	{
		$cmd = $this->moduleCmd . 'GET_ADMIN_CONF';
		$this->exec_cmd($cmd, $output, $return);
		$url = '';
		if ($return == 0) {
			//http://' . $_SERVER['SERVER_ADDR'] . ':' . $admin_port . '/
			$data = implode("\n", $output);
			// <address>*:7080</address>
			$port = "7080";
			if ($data != '' && preg_match("/<address>.*:(\d+)<\/address>/", $data, $matches) ) {
				$port = $matches[1];
			}

			// <secure>0</secure>
			$is_secure = '';
			if ($data != '' && preg_match("/<secure>(\d)<\/secure>/", $data, $matches) ) {
				if ($matches[1] == 1) {
					$is_secure = 's';
				}
			}
			$url = "http{$is_secure}://{$_SERVER['SERVER_ADDR']}:$port/";
		}
		return $url;
	}

	public function GetApachePortOffset()
	{
		$cmd = $this->moduleCmd . 'GET_PORT_OFFSET';
		$this->exec_cmd($cmd, $output, $return);
		$offset = 0;
		if ($return == 0) {
			$data = $output[0];
			if ($data != '' && preg_match("/<apachePortOffset>(.+)<\/apachePortOffset>/", $data, $matches) ) {
				$offset = (int)$matches[1];
			}
		}
		return $offset;
	}

	public function ChangePortOffset($new_port_offset, &$output)
	{
		$cmd = $this->moduleCmd . "CHANGE_PORT_OFFSET $new_port_offset";
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function ChangeLicense($serial, &$output)
	{
		$cmd = $this->moduleCmd . "CHANGE_LICENSE $serial";
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function RestartLSWS(&$output)
	{
		$cmd = $this->moduleCmd . 'RESTART_LSWS';
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function Switch2LSWS(&$output)
	{
		$cmd = $this->moduleCmd . 'SWITCH_TO_LSWS';
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function Switch2Apache(&$output)
	{
		$cmd = $this->moduleCmd . 'SWITCH_TO_APACHE';
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function GetCurrentLicenseStatus(&$output)
	{
		$cmd = $this->moduleCmd . ' CHECK_LICENSE';
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function UninstallLSWS($keepConf, $keepLog, &$output)
	{
		if ($this->IsLSRunning() > 0) {
			$cmd = $this->moduleCmd . 'STOP_LSWS';
			$this->exec_cmd($cmd, $output, $return_var);
		}

		$cmd = CGI_DIR . 'uninstall_lsws_plesk '
				. escapeshellarg(LSWS_HOME) . ' '
				. $keepConf . ' '
				. $keepLog;
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function DetectLSWS_HOME()
	{
		// testing possible locations
		$possible_loc = array('/usr/local/lsws', '/opt/lsws');
		foreach ($possible_loc as $path) {
			if (is_file("$path/bin/lshttpd")) {
				return $path;
			}
		}
		return "";
	}

	public function Validate_LSWS_HOME($lsws_home_input, $hasInstalled=false) {

		if ($lsws_home_input == '') {
			return 'Missing input!';
		}
		if ($hasInstalled) {
			if (!is_file("$lsws_home_input/bin/lshttpd")) {
				return "Invalid path: cannot find $lsws_home_input/bin/lshttpd!";
			}
		}
		else {
			if (strpos($lsws_home_input, ' ') !== FALSE) {
				return 'Do not allow space in the path!';
			}
			// new installation, prohibit certain path
			$forbiddenDirs = array('/etc', '/usr/sbin', '/usr/bin', '/usr/lib',
				'/usr/local/bin', '/usr/local/sbin', '/usr/local/lib');
			foreach ($forbiddenDirs as $dir) {
				if (strpos($lsws_home_input,$dir) !== FALSE) {
					return 'It is not safe to install under this system directory';
				}
			}
		}
		return NULL;
	}

	public function Validate_NewPortOffset($new_port_offset, $old_port_offset)
	{
		$err = $this->validate_port_offset($new_port_offset);

		if ($err == NULL && $new_port_offset == $old_port_offset) {
			$err = 'New value is same as current one';
		}

		return $err;
	}

	public function Validate_ChangeLicenseInput($input)
	{
		$errors = $this->validate_license_type($input);
		return $errors;
	}

	public function Validate_InstallInput($input)
	{
		$errors = $this->validate_license_type($input);

		$err = $this->Validate_LSWS_HOME($input['lsws_home_input']);
		if ($err != NULL) {
			$errors['lsws_home_input'] = $err;
		}

		$err = $this->validate_port_offset($input['port_offset']);
		if ($err != NULL) {
			$errors['port_offset'] = $err;
		}

		if ($input['admin_login'] == '') {
			$errors['admin_login'] = 'Missing login ID!';
		}
		elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $input['admin_login']) ) {
			$errors['admin_login'] = 'Accepted characters for login ID are [a-zA-Z0-9_\-]';
		}

		if ($input['admin_pass'] == '') {
			$errors['admin_pass'] = 'Missing login password';
		}
		elseif ($input['admin_pass1'] == '') {
			$errors['admin_pass1'] = 'Missing login password';
		}
		elseif ($input['admin_pass'] != $input['admin_pass1']) {
			$errors['admin_pass1'] = 'Passwords do not match!';
		}
		elseif (strlen($input['admin_pass']) < 6) {
			$errors['admin_pass'] = 'Password must be at least 6 characters!';
		}
		elseif (strlen($input['admin_pass']) > 64) {
			$errors['admin_pass'] = 'Password is too long, must be less than 64 characters!';
		}
		else if (strpos($admin_pass, ' ') !== FALSE) {
			$errors['admin_pass'] = 'Password cannot contain space!';
		}
		$input['admin_pass'] = $admin_pass;

		if ($input['admin_email'] == '') {
			$errors['admin_email'] = 'Missing Admin Email!';
		}
		else {
			$emails = preg_split("/, /", $input['admin_email'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($emails as $em) {
				if ( !preg_match("/^[[:alnum:]._-]+@[[:alnum:]._-]+$/", $em) ) {
					$errors['admin_email'] = "invalid email format - $em";
					break;
				}
			}
	    }
		return $errors;
	}

	public function InstallLSWS($input, &$output)
	{
		$install_cmd = CGI_DIR . 'install_lsws_plesk '
			. escapeshellarg($input['lsws_home_input']) . ' '
			. escapeshellarg($input['serial_no']) . ' '
			. $input['port_offset'] . ' '
			. $input['php_suexec'] . ' '
			. escapeshellarg($input['admin_login']) . ' '
			. escapeshellarg($input['admin_pass']) . ' '
			. escapeshellarg($input['admin_email']) ;

		$this->exec_cmd($install_cmd, $output, $return_var);

		return $return_var;
	}

	public function Validate_LicenseTransfer($info)
	{
		$error = '';

		if ($info['licstatus_return'] != 0) {
			$error = 'Current license can no longer be used, not valid for transfer.';
		}
		else {
			$buf = implode('<br/>', $info['licstatus_output']);
			if ( preg_match('/ -[0-9]+ /', $buf)) {
				// has been migrated
				$error[] = 'Current license has been transferred. You cannot transfer this license again!';
				$error[] = 'You can use the same serial number to register a new license on a new machine.';
				$error[] = 'If you want to reuse the same serial number on this machine, use "change license"
					option with same serial to get a new license key if it has not been used elsewhere.
					Otherwise, you need to release the serial first before you can activate on a different machine.';
			}
		}

		return $error;
	}

	public function TransferLicense(&$output)
	{
		$cmd = $this->moduleCmd . 'TRANSFER_LICENSE';
		$this->exec_cmd($cmd, $output, $return_var);
		return $return_var;
	}

	public function Validate_VersionManage($info)
	{
		if (!in_array($info['act'], array('download', 'switchTo', 'remove')))
			return 'Invalid action';
		if (!preg_match('/^[1-9]+\.[0-9RC\.]+$/', $info['actId']))
			return 'Invalid version';
	}

	public function VersionManage($act, $actId, &$output)
	{
		$cmd = $this->moduleCmd;
		if ($act == 'download') {
			$cmd .= "VER_UP $actId";
		}
		else if ($act == 'switchTo') {
			$cmd .= "VER_SWITCH $actId";
		}
		else if ( $act == 'remove' ) {
			$cmd .= "VER_DEL $actId";
		}
		else
			return -1;

		$this->exec_cmd($cmd, $output, $return_var);

		if ($act == 'switchTo' && $return_var == 0) {
			$this->RestartLSWS($output2);
		}
		return $return_var;
	}

	private function validate_serial_no($serial)
	{
		$error = '';

		if ($serial == "") {
			$error = 'Missing serial number!';
		}
		elseif (strlen($serial) != 19) {
			$error = 'Invalid serial number';
		}
		elseif (!preg_match('%^[a-zA-Z0-9/+]{4}-[a-zA-Z0-9/+]{4}-[a-zA-Z0-9/+]{4}-[a-zA-Z0-9/+]{4}$%', $serial)) {
			$error = 'Invalid serial number';
		}

		return $error;
	}

	private function validate_port_offset($port_offset)
	{
		if (!preg_match('/^[0-9]+$/', $port_offset)) {
			return 'Invalid number.';
		}

		$port = intval($port_offset);
		if ($port < 0 || $port > 65535) {
			return 'Number out of range (0~65535)';
		}
		return NULL;
	}

	private function validate_license_type($input)
	{
		// used in install & change license
		$errors = NULL;

		if ($input['license_agree'] != 'agree' ) {
			$errors['license_agree'] = 'Cannot proceed without agreement to EULA';
		}

		if ($input['install_type'] == '') {
			$errors['install_type'] = 'Please select one';
		}
		else if ($input['install_type'] == 'prod') {
			$err = $this->validate_serial_no($input['serial_no']);
			if ($err != NULL)
				$errors['serial_no'] = $err;
		}
		else if ($input['install_type'] == 'trial') {
			if ($input['serial_no'] != '')
				$errors['serial_no'] = 'Cannot select trial license with serial number.';
		}
		else {
			$errors['install_type'] = 'Invalid type';
		}

		return $errors;
	}
}
