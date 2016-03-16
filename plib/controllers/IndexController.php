<?php
/********************************************
* LiteSpeed Web Server Plugin for Plesk Panel
* @Author:   LiteSpeed Technologies, Inc. (https://www.litespeedtech.com)
* @Copyright: (c) 2013-2016
*********************************************/


class IndexController extends pm_Controller_Action
{
	private $util;

	public function init()
	{
		parent::init();
		$path = pm_Settings::get('LSWS_HOME');
		if ($path != NULL) {
			if (is_executable("$path/bin/lshttpd"))
		    	define( 'LSWS_HOME', $path);
		    else
		    	pm_Settings::set('LSWS_HOME', NULL);
		}
		define('MODULE_VERSION', '1.2.4');

		$this->view->dispatcher = new Modules_Litespeed_View($this->view);
		$this->util = new Modules_Litespeed_Util();

	}

	private function saveLSWS_HOME($path)
	{
		define('LSWS_HOME', $path);
		pm_Settings::set('LSWS_HOME', $path);
		$this->util->Init();
	}

	public function indexAction()
	{
        $do = $this->util->get_request_var('do');
        $step = $this->util->get_request_var('step');

        $support_actions = array('define_home', 'versionManager', 'check_current_license', 'restart_lsws', 'switch_lsws',
        	'switch_apache', 'change_port_offset', 'change_license', 'transfer_license', 'install', 'uninstall', '', 'module_error');
        $steps = array(0, 1, 2);

        if (!in_array($do, $support_actions) || !in_array($step, $steps)) {
        	echo '<h1>Invalid Entrance</h1>';
        	return;
        }

        $res = $this->util->ModuleInstalled();
	        if ($res > 0) {
	        	$do = 'module_error';
	        	$step = $res;
	        }

        $this->view->dispatcher->PageHeader($do);

		switch ($do) {
			case 'define_home':
				$this->define_home($step);
				break;
			case 'versionManager':
				$this->version_manager($step);
				break;
			case 'check_current_license':
				$this->check_license($step);
				break;
			case 'restart_lsws':
				$this->restart_lsws($step);
				break;
			case 'switch_lsws':
				$this->switch_lsws($step);
				break;
			case 'switch_apache':
				$this->switch_apache($step);
				break;
			case 'change_port_offset':
				$this->change_port_offset($step);
				break;
			case 'change_license':
				$this->change_license($step);
				break;
			case 'transfer_license':
				$this->transfer_license($step);
				break;
			case 'install':
				$this->install_lsws($step);
				break;
			case 'uninstall':
				$this->uninstall_lsws($step);
				break;
			case 'module_error':
				$this->view->dispatcher->ModuleError($step);
				break;
			default:
				// Show the main page
				$this->main_menu();
		}

        $this->view->dispatcher->PageFooter();

	}

	private function main_menu()
	{
		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
        $info['nginx_running'] = $this->util->IsNginxRunning();

		if ( defined('LSWS_HOME') ) {
			$info['is_installed'] = true;
			$info['lsws_version'] = $this->util->GetCurrentVersion();
			$info['new_version'] = $this->util->GetNewVersion();
			$info['port_offset'] = $this->util->GetApachePortOffset();
			$info['serial'] = $this->util->GetCurrentSerialNo();
			$info['admin_url'] = $this->util->GetAdminUrl();
		}
		else {
			if ($info['ls_pid'] > 0) {
				$this->define_home(2);
				return;
			}
			$info['is_installed'] = false;
		}

		$this->view->dispatcher->MainMenu($info);
	}

	private function restart_lsws($step)
	{
		if ($step == 1) {
			$info['return'] = $this->util->RestartLSWS($output);
			$info['output'] = $output;
		}

		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
		$info['port_offset'] = $this->util->GetApachePortOffset();
        $info['nginx_running'] = $this->util->IsNginxRunning();

		if ($step == 0) {
			$this->view->dispatcher->RestartLswsConfirm($info);
		}
		else {
			$this->view->dispatcher->RestartLsws($info);
		}

	}

	private function check_license()
	{
		$info['return'] = $this->util->GetCurrentLicenseStatus($output);
		$info['output'] = $output;
		$outstr = implode(' ' , $output);
		if (strpos($outstr, 'trial') > 0) {
			$info['lictype'] = 'trial';
		}
		else if ( preg_match('/ -[0-9]+ /', $outstr)) {
			$info['lictype'] = 'migrated';
		}

		$this->view->dispatcher->CheckLicense($info);
	}

	private function switch_lsws($step)
	{
		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
		$info['port_offset'] = $this->util->GetApachePortOffset();
		$info['nginx_running'] = $this->util->IsNginxRunning();

		//check if go ahead
		if ($info['nginx_running'] != '') {
			$info['stop_msg'][] = 'Reverse proxy server nginx must be stopped before starting LiteSpeed.';
			$info['stop_msg'][] = 'ps output for nginx:';
			$info['stop_msg'][] = $info['nginx_running'];
		}
		// otherwise always allow switch to add proper wrapper

		if ($step == 1 && $info['stop_msg'] == NULL) {
			$info['return'] = $this->util->Switch2LSWS($output);
			$info['output'] = $output;
			$info['ls_pid'] = $this->util->IsLSRunning();
			$info['ap_pid'] = $this->util->IsApacheRunning();
			$info['port_offset'] = $this->util->GetApachePortOffset();
		}

		if ($step == 0) {
			$this->view->dispatcher->Switch2LswsConfirm($info);
		}
		else {
			$this->view->dispatcher->Switch2Lsws($info);
		}

	}

	private function switch_apache($step)
	{
		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
        $info['nginx_running'] = $this->util->IsNginxRunning();

		if ($info['ap_pid'] > 0 && $info['ls_pid'] == 0 && $info['nginx_running'] != '') {
			$info['stop_msg'] = 'Apache is currently your main web server. <strong>No action needed.</strong>';
		}
		// if nginx not running, always allow switch to restore wrapper

		if ($step == 1 && $info['stop_msg'] == NULL) {
			$info['return'] = $this->util->Switch2Apache($output);
			$info['output'] = $output;
			$info['ls_pid'] = $this->util->IsLSRunning();
			$info['ap_pid'] = $this->util->IsApacheRunning();
		}

		if ($step == 0) {
			$this->view->dispatcher->Switch2ApacheConfirm($info);
		}
		else {
			$this->view->dispatcher->Switch2Apache($info);
		}

	}

	private function change_port_offset($step)
	{
		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
		$info['port_offset'] = $this->util->GetApachePortOffset();

		if ($step == 1) {
			$info['new_port_offset']  = Modules_Litespeed_Util::get_request_var('port_offset');
			$info['error'] = $this->util->Validate_NewPortOffset($info['new_port_offset'], $info['port_offset']);
			if ($info['error'] != NULL) {
				$step = 0;
			}
			else {
				$info['return'] = $this->util->ChangePortOffset($info['new_port_offset'], $output);
				$info['output'] = $output;
			}
		}

		if ($step == 0) {
			$this->view->dispatcher->ChangePortOffsetConfirm($info);
		}
		else {
			$this->view->dispatcher->ChangePortOffset($info);
		}
	}

	private function uninstall_lsws($step)
	{
		$info['ls_pid'] = $this->util->IsLSRunning();
		$info['ap_pid'] = $this->util->IsApacheRunning();
		$info['port_offset'] = $this->util->GetApachePortOffset();

		//check if go ahead
		if ( $info['ap_pid'] == 0 ) {
			$info['stop_msg'] = 'Apache is not running. Please use the <strong>Switch to Apache</strong> option before uninstalling LiteSpeed.';
		}

		if ($step == 1 && $info['stop_msg'] == NULL) {
			$keepConf = ( $this->util->get_request_var('keep_conf') == '1' ) ? 'Y' : 'N';
			$keepLog = ( $this->util->get_request_var('keep_log') == '1' ) ? 'Y' : 'N';
			$info['return'] = $this->util->UninstallLSWS($keepConf, $keepLog, $output);
			$info['output'] = $output;
		}

		if ($step == 0 || $info['stop_msg'] != NULL) {
			$this->view->dispatcher->UninstallLswsPrepare($info);
		}
		else {
			$this->view->dispatcher->UninstallLsws($info);
		}
	}

	private function define_home($step)
	{
		if ($step == 1) {
			$info['lsws_home_input'] = Modules_Litespeed_Util::get_request_var('lsws_home_input');
			$info['error'] = $this->util->Validate_LSWS_HOME($info['lsws_home_input'], TRUE);

			if ($info['error'] == NULL) {
				$this->saveLSWS_HOME($info['lsws_home_input']);
				$this->main_menu();
				return;
			}
		}
		elseif ($step == 2) {
			//redirect from mainmenu
			$info['do_action'] = 'define_home';
		}

		if ($info['lsws_home_input'] == NULL)
			$info['lsws_home_input'] = $this->util->DetectLSWS_HOME();
		$this->view->dispatcher->DefineHome($info);
	}

	private function install_lsws($step)
	{
		$info['rpm'] = $this->util->HasRPM();
   		$info['php_version'] = $this->util->GetDefaultLsphpVersion();
        if ($info['rpm'] != '') {
            $info['php_options'] = array('lsphp53' => '5.3', 'lsphp54' => '5.4', 'lsphp55' => '5.5', 'lsphp56' => '5.6');
        }

		if ($step == 0) {
			// populate default
			$info['license_agree'] = '';
			$info['install_type'] = '';
			$info['serial_no'] = '';
			$info['lsws_home_input'] = '/usr/local/lsws';
			$info['port_offset'] = '0';
			$info['php_suexec'] = '';
			$info['admin_email'] = 'root@localhost';
			$info['admin_login'] = 'admin';
			$info['admin_pass'] = '';
			$info['admin_pass1'] = '';
		}
		else {
			$info['license_agree'] = $this->util->get_request_var('license_agree');
			$info['install_type'] = $this->util->get_request_var('install_type');
			$info['serial_no'] = $this->util->get_request_var('serial_no');
			$info['lsws_home_input'] = $this->util->get_request_var('lsws_home_input');
			$info['port_offset'] = $this->util->get_request_var('port_offset');
			$info['php_suexec'] = ($this->util->get_request_var('php_suexec') == 'enable')? 1: 0;
			$email = $this->util->get_request_var('admin_email');
			$emails = preg_split("/, /", $email, -1, PREG_SPLIT_NO_EMPTY);
			$info['admin_email'] = implode(', ', $emails);
            if ($info['rpm'] != '') {
                $info['php_version'] = $this->util->get_request_var('php_version');
            }

			$info['admin_login'] = $this->util->get_request_var('admin_login');
			$info['admin_pass'] = $this->util->get_request_var('admin_pass');
			$info['admin_pass1'] = $this->util->get_request_var('admin_pass1');
			$info['error'] = $this->util->Validate_InstallInput($info);

			if ($info['error'] == NULL) {
				if ($info['install_type'] == 'trial')
					$info['serial_no'] = 'TRIAL';
				$info['return'] = $this->util->InstallLSWS($info, $output);
				$info['output'] = $output;
				if ($info['return'] == 0) {
					$this->saveLSWS_HOME($info['lsws_home_input'] );
					$info['ls_pid'] = $this->util->IsLSRunning();
					$info['ap_pid'] = $this->util->IsApacheRunning();
					$info['port_offset'] = $this->util->GetApachePortOffset();
				}
			}
		}

		if ($step == 0 || $info['error'] != NULL) {
			$this->view->dispatcher->InstallLswsPrepare($info);
		}
		else {
			$this->view->dispatcher->InstallLsws($info);
		}

	}

	private function change_license($step)
	{
		if ($step == 0) {
			// populate default
			$info['license_agree'] = '';
			$info['install_type'] = '';
			$info['serial_no'] = '';
		}
		else {
			$info['license_agree'] = $this->util->get_request_var('license_agree');
			$info['install_type'] = $this->util->get_request_var('install_type');
			$info['serial_no'] = $this->util->get_request_var('serial_no');

			$info['error'] = $this->util->Validate_ChangeLicenseInput($info);

			if ($info['error'] == NULL) {
				if ($info['install_type'] == 'trial')
					$info['serial_no'] = 'TRIAL';
				$info['return'] = $this->util->ChangeLicense($info['serial_no'], $output);
				$info['output'] = $output;
			}
		}

		if ($step == 0 || $info['error'] != NULL) {
			$this->view->dispatcher->ChangeLicensePrepare($info);
		}
		else {
			$info['ls_pid'] = $this->util->IsLSRunning();
			$info['ap_pid'] = $this->util->IsApacheRunning();
			$this->view->dispatcher->ChangeLicense($info);
		}

	}

	private function transfer_license($step)
	{
		if ($step == 0) {
			$info['licstatus_return'] = $this->util->GetCurrentLicenseStatus($output);
			$info['licstatus_output'] = $output;
			$info['error'] = $this->util->Validate_LicenseTransfer($info);
			$this->view->dispatcher->TransferLicenseConfirm($info);
		}
		else {
			$info['return'] = $this->util->TransferLicense();
			$info['output'] = $output;
			$this->view->dispatcher->TransferLicense($info);
		}
	}

	private function version_manager($step)
	{
		if ($step == 2) {
			$info['act'] = $this->util->get_request_var('act');
			$info['actId'] = $this->util->get_request_var('actId');
			$info['error'] = $this->util->Validate_VersionManage($info);

			if ($info['error'] == NULL) {
				$info['return'] = $this->util->VersionManage($info['act'], $info['actId'], $output);
				$info['output'] = $output;
			}
		}
		$info['lsws_version'] = $this->util->GetCurrentVersion();
		$info['new_version'] = $this->util->GetNewVersion();
		$info['installed'] = $this->util->GetInstalledVersions();

		$this->view->dispatcher->VersionManager($info);
	}

}
