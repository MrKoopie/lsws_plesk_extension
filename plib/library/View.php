<?php
/********************************************
* LiteSpeed Web Server Plugin for Plesk Panel
* @Author:   LiteSpeed Technologies, Inc. (https://www.litespeedtech.com)
* @Copyright: (c) 2013-2015
*********************************************/


class Modules_Litespeed_View
{
	public $icons;
	private $bufs = array();
	private $_parentview;

	public function __construct($view)
	{
		define('SUBMIT', 'submit');
		$this->_parentview = $view;
		define('MODULE_URL', pm_Context::getBaseUrl());

		$this->icons = array(
			'm_logo_lsws' => 'images/lsws.gif',
			'm_server_version' => '/theme/icons/32/plesk/web-servers.png',
			'm_server_install' => '/theme/icons/32/plesk/web-servers.png',
			'm_server_definehome' => '/theme/icons/32/plesk/configure-app.png',
			'm_server_uninstall' => '/theme/icons/32/plesk/file-delete.png',
            'm_server_php' => '/theme/icons/32/plesk/php.png',
			'm_control_webadmin' =>  '/theme/icons/32/plesk/web-users.png',
			'm_control_restart' => '/theme/icons/32/plesk/refresh.png',
			'm_license_check' => '/theme/icons/32/plesk/license-management.png',
			'm_license_change' => '/theme/icons/32/plesk/change-passwd.png',
			'm_license_transfer' => '/theme/icons/32/plesk/key-update.png',
			'm_switch_apache' => '/theme/icons/32/plesk/key-revert.png',
			'm_switch_lsws' => '/theme/icons/32/plesk/file-publish.png',
			'm_switch_port' => '/theme/icons/32/plesk/rule-add.png',
			'v_upgrade' => '/theme/icons/16/plesk/install.png',
			'v_active' => '/theme/icons/16/plesk/on-state.png',
			'v_reinstall' => '/theme/icons/16/plesk/refresh.png',
			'v_switchto' => '/theme/icons/16/plesk/start.png',
			'v_remove' => '/theme/icons/16/plesk/delete.png',
			'ico_info' => '/theme/icons/32/plesk/server-info.png',
			'ico_error' => '/theme/icons/32/plesk/file-error.png',
			'ico_warning' => '/theme/icons/32/plesk/file-alert.png'
		);

	}

	public function dispatch()
	{
		echo implode("\n", $this->bufs);
	}

	public function PageHeader($do)
	{
		$buf = <<<EEN
			<div class="formArea"><form name="lswsform"><input type="hidden" name="step" value="1"/><input type="hidden" name="do" value="$do"/>
			<div class="form-box">
EEN;
		$this->bufs[] = $buf;
	}

	public function PageFooter()
	{
		$this->bufs[] = "</div></form></div>";
	}

	public function ModuleError($step)
	{
		$buf = $this->screen_title('Complete LiteSpeed Extension Installation');
		$script = PSA_BASE . '/admin/plib/modules/litespeed/scripts/install_scripts';

		if ($step == 1) {
			$err = "<pre>For security reasons, please login as root user via ssh to manually run the following command:

	sh $script

If successful, remove the installation script:

	rm $script	</pre>";
			$title = 'Please manually run the script to finish installation';
		}
		elseif ($step == 2) {
			$err = "Please remove the install script before using this extension <br/> rm $script \n";
			$title = 'Install script needs to be removed after installation.';
		}
		else {
			$err = 'Please download and reinstall the extension from zip file.';
			$title = 'Module is not installed properly';
		}
		$buf .= $this->error_panel_mesg($title, $err);
		$this->bufs[] = $buf;
	}

	private function tool_list($list)
	{
		$buf = '<div class="content-area"><div class="content-wrapper"><ul class="tools-list">';
		foreach ($list as $li) {
			$buf .= '<li class="tools-item"><div class="tool-block">'
				. '<span class="tool-icon">';
			if ( $li['icon'] != '')
				$buf .= '<img src="' . $li['icon'] . '"></img>';

			$buf .= '</span><span class="tool-name">' . $li['name']
				. '</span><span class="tool-info">' . $li['info']
				. '</span></li>';
		}

		$buf .= '</ul></div></div>' . "\n";

		return $buf;
	}

	public function MainMenu($info)
	{
		$this->screen_title("LiteSpeed Extension", FALSE);

		$buf = '<div style="margin:15px"><center><img alt="LiteSpeed Web Server" src="'
			. $this->icons['m_logo_lsws']
			. '"/ onclick="window.open(\'https://www.litespeedtech.com\')" ></center></div>';

		$buf .= $this->show_running_status($info);

		$buf .= '<div id="main" class="clearfix">';

		if ($info['is_installed']) {

			$buf .= $this->section_title('Install LiteSpeed Web Server');
			$list = array();

			$li_version = array('icon' => $this->icons['m_server_version'], 'info' =>'Version Management');
			$li_version['name'] = 'Current Version: <a href="?do=versionManager">' . $info['lsws_version'] . '</a>';
			if ($info['new_version'] != '' && $info['new_version'] != $info['lsws_version']) {
				$li_version[info] .= " (new release available: {$info['new_version']})";
			}
			$list[] = $li_version;

			$li = array('icon' => $this->icons['m_server_uninstall'],
						'name' => '<a href="?do=uninstall">Uninstall</a>',
						'info' => 'Uninstall LiteSpeed Web Server');
			$list[] = $li;

            $li = array('icon' => $this->icons['m_server_php'],
            'name' => '<a href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:plesk:php_guide" target="_blank">LSPHP</a>',
            'info' => 'Guide to PHP for LiteSpeed');
			$list[] = $li;

			$buf .= $this->tool_list($list);

			$buf .= $this->section_title('Control LiteSpeed Web Server');
			$list = array();

			$li = array('icon' => $this->icons['m_control_webadmin']);
			if ($info['ls_pid'] > 0) {
				$li['name'] = '<a href="' . $info['admin_url'] . '" target="_blank">WebAdmin</a> Console';
				$li['info'] = 'Configurations | Real-time status';
			} else {
				$li['name'] = 'WebAdmin Console';
				$li['info'] = 'Please start LiteSpeed first to access WebAdmin console';
			}
			$list[] = $li;

			$li = array('icon' => $this->icons['m_control_restart']);
			$li['name'] = '<a href="?do=restart_lsws">Restart</a> LiteSpeed';
			$li['info'] = '';
			$list[] = $li;

			$buf .= $this->tool_list($list);

			$buf .= $this->section_title('License Management');
			$list = array();

			$li = array('icon' => $this->icons['m_license_check']);
			$li['name'] = 'License: ' . $info['serial'];
			$li['info'] = '<a href="?do=check_current_license">Check/Refresh</a> current license status';
			$list[] = $li;

			$li = array('icon' => $this->icons['m_license_change']);
			$li['name'] = '<a href="?do=change_license">Change</a> License';
			$li['info'] = 'Switch to another license';
			$list[] = $li;

			if ( $info['serial'] != 'TRIAL') {
				$li = array('icon' => $this->icons['m_license_transfer']);
				$li['name'] = '<a href="?do=transfer_license">Transfer</a> License';
				$li['info'] = 'Start license migration. Frees license for registration on another server';
				$list[] = $li;
			}
			$buf .= $this->tool_list($list);

			$buf .= $this->section_title('Switch between Apache and LiteSpeed');
			$list = array();

			$li = array('icon' => $this->icons['m_switch_apache']);
			$li['name'] = 'Switch to <a href="?do=switch_apache">Apache</a>';
			$li['info'] = 'Use Apache as main web server. This will update rc scripts.';
			$list[] = $li;

			$li = array('icon' => $this->icons['m_switch_lsws']);
			$li['name'] = 'Switch to <a href="?do=switch_lsws">LiteSpeed</a>';
			$li['info'] = 'Use LiteSpeed as main web server. This will update rc scripts.';
			$list[] = $li;

			$li = array('icon' => $this->icons['m_switch_port']);
			$li['name'] = 'LiteSpeed Port Offset is ' . $info['port_offset'] . '. <a href="?do=change_port_offset">Change</a>';
			$li['info'] = 'Allow LiteSpeed and Apache to run in parallel';
			$list[] = $li;

			$buf .= $this->tool_list($list);
		}

		else {
			$buf .= $this->section_title('Install LiteSpeed Web Server');
			$list = array();

			$li_version = array('icon' => $this->icons['m_server_install'],
								'name' => '<a href="?do=install">Install</a> LiteSpeed Web Server',
								'info' =>'Download and install the latest stable release.');
			$list[] = $li_version;

			$li = array('icon' => $this->icons['m_server_definehome'],
						'name' => '<a href="?do=define_home">Define LSWS_HOME</a>',
						'info' => 'If you installed LiteSpeed Web Server before using this extension, please specify your LSWS_HOME before using the extension.');
			$list[] = $li;

			$buf .= $this->tool_list($list);

		}
		$buf .= '<p></p>
<p style="margin-top:30px;color:#a0a0a0;text-align:right;font-size:11px">This extension is developed by LiteSpeed Technologies. Odin is not responsible for
support.<br/>Please contact LiteSpeed at litespeedtech.com for all related questions and issues.<br/><br/>LiteSpeed Web Server Extension for Plesk v'
		. MODULE_VERSION . ' </p>

</div>';
		$this->bufs[] = $buf;
	}

	public function RestartLswsConfirm($info)
	{
		$buf = $this->screen_title('Confirm Operation... Restart LiteSpeed');
		$buf .= $this->show_running_status($info);
		$goNext = 'Restart';

		if ($info['port_offset'] != 0) {
			$msg[] = 'Apache port offset is ' . $info['port_offset'] . '.';
			$msg[] = 'LiteSpeed will be running in parallel with Apache. When you are ready to replace Apache with LiteSpeed, use the <b>Switch to LiteSpeed</b> option.';
		}

		if ($info['ap_pid'] > 0 && $info['port_offset'] == 0) {
			// use switch no matter lsws run or not
			$msg[] = 'Apache port offset is 0. If you wish to use LiteSpeed as your main web server, please use the <b>Switch to LiteSpeed</b> option.';
			$msg[] = 'If you need to run LiteSpeed in parallel with Apache, please use the <b>Change Port Offset</b> option.';
			$goNext = NULL;
		}

        if ($info['nginx_running'] != '') {
            $msg[] = 'Reverse proxy server nginx must be stopped before starting LiteSpeed.';
            $goNext = NULL;
        }

		if ($goNext == 'Restart') {
			$msg[] = 'This will do a graceful restart of LiteSpeed Web Server.';
		}

		$buf .= $this->info_panel_mesg(NULL, $msg);
		$buf .= $this->button_panel_cancel_next('Cancel', $goNext);
		$this->bufs[] = $buf;
	}

	public function RestartLsws($info)
	{
		$buf = $this->screen_title('Restart LiteSpeed');
		$buf .= $this->show_running_status($info);

		if ( $info['ls_pid'] > 0 ) {
			$buf .= $this->info_panel_mesg('LiteSpeed restarted successfully', $info['output']);
		}
		else {
			$buf .=  $this->error_panel_mesg('LiteSpeed is not running! Please check the web server log file for errors.', $info['output']);
		}

		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function Switch2LswsConfirm($info)
	{
		$buf = $this->screen_title('Confirm Operation... Switch to LiteSpeed');
		$buf .= $this->show_running_status($info);

		if ($info['stop_msg'] != NULL) {
			$buf .= $this->info_panel_mesg(NULL, $info['stop_msg']);
			$buf .= $this->button_panel_back('OK');
		}
		else {
			if ($info['ap_pid'] > 0) {
				$msg[] = 'This action will stop Apache and restart LiteSpeed as the main web server. It may take a little while for Apache to stop completely.';
			}

			if ($info['port_offset'] != 0) {
				$warn = "Apache port offset is {$info['port_offset']}. This action will change port offset to 0.";
				$buf.= $this->warning_mesg($warn);
			}

			$msg[] = 'This will restart <strong>LiteSpeed as main web server</strong>!';

			$buf .= $this->info_panel_mesg(NULL, $msg);
			$buf .= $this->button_panel_cancel_next('Cancel', 'Switch to LiteSpeed');
		}

		$this->bufs[] = $buf;
	}

	public function Switch2Lsws($info)
	{
		$buf = $this->screen_title('Switch To LiteSpeed');
		$buf .= $this->show_running_status($info);

		if ($info['stop_msg'] != NULL) {
			$buf .= $this->info_panel_mesg(NULL, $info['stop_msg']);
		}
		else {
			$out = $info['output'];

			if ($info['port_offset'] != 0)
				$out[] = 'Failed to set Apache port offset to 0. Please check config file permissions.';
			else
				$out[] = 'Apache port offset has been set to 0.';

			if ($info['ls_pid'] > 0)
				$buf .= $this->info_panel_mesg('Switched to LiteSpeed successfully', $out);
			else
				$buf .= $this->error_panel_mesg('Failed to bring up LiteSpeed', $out);
		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function Switch2ApacheConfirm($info)
	{
		$buf = $this->screen_title('Confirm Operation... Switch to Apache');
		$buf .= $this->show_running_status($info);

		if ($info['stop_msg'] != NULL) {
			$buf .= $this->info_panel_mesg(NULL, $info['stop_msg']);
			$buf .= $this->button_panel_back('OK');
		}
		else {
			if ($info['ls_pid'] > 0) {
				$msg[] = 'This action will stop LiteSpeed and restart Apache as the main web server. It may take a little while for LiteSpeed to stop completely.';
			}
			$msg[] = 'This will restart <strong>Apache as main web server</strong>!';
			$buf .= $this->info_panel_mesg(NULL, $msg);
			$buf .= $this->button_panel_cancel_next('Cancel', 'Switch to Apache');
		}
		$this->bufs[] = $buf;
	}

	public function Switch2Apache($info)
	{
		$buf = $this->screen_title('Switch To Apache');
		$buf .= $this->show_running_status($info);

		if ($info['stop_msg'] != NULL) {
			$buf .= $this->info_panel_mesg(NULL, $info['stop_msg']);
		}
		else {
			if ($info['return'] != 0) {
				$buf .= $this->info_panel_mesg(NULL, $info['output']);

				$msg[] = 'Failed to switch to Apache!';
				$msg[] = 'This may be due to a configuration error. To manually check this problem, please ssh to your server.';
				$msg[] = 'Use the following steps to manually switch to Apache:';
				$msg[] = 'Stop LiteSpeed if lshttpd still running: <code>killall -9 lshttpd </code>';

				$msg[] = 'Try stop LiteSpeed if lshttpd still running: <code>killall -9 lshttpd </code>';
				$msg[] = 'Restore Apache httpd if /usr/sbin/httpd_ls_bak exists: <code>mv -f /usr/sbin/httpd_ls_bak /usr/sbin/httpd</code>';
				$msg[] = 'Run the Apache restart command manually: <code>service httpd restart</code> and check for errors.';
				$buf .= $this->error_panel_mesg('Failed to switch to Apache', $msg);
			}
			else {
				$buf .= $this->info_panel_mesg('Switched to Apache successfully', $info['output']);
			}
		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function ChangePortOffsetConfirm($info)
	{
		$buf = $this->screen_title('Confirm Operation... Change LiteSpeed Port Offset');
		$buf .= $this->show_running_status($info);

		$buf .='<div class="indent-box"><div class="hint"><p>Port offset allows you to run Apache and LiteSpeed in parallel by running LiteSpeed on a separate port.</p>
			<p>For example, if Apache is running on port 80 and the LiteSpeed port offset is 2000, then you will be able to access LiteSpeed-powered web pages on port 2080.</p></div></div>';

		$warn[] = 'This action will only change the LiteSpeed configuration file. You need to restart LiteSpeed to activate the change.';

		if ($info['port_offset'] == 0 && $info['ap_pid'] == 0) {
			$warn[] = 'Apache is currently not running. We suggest your first <strong>switch to Apache </strong> to avoid server down time.';
		}
		$buf .= $this->warning_mesg($warn);

		$buf .= $this->section_title('Change Port Offset');

		$hint = "Current Port Offset is {$info['port_offset']}.";
		$input = $this->input_text('port_offset', $info['new_port_offset']);
		$buf .= $this->form_row('Set new port offset', $input, $info['error'], $hint);

		$buf .= $this->button_panel_cancel_next('Cancel', 'Change');
		$this->bufs[] = $buf;
	}

	public function ChangePortOffset($info)
	{
		$buf = $this->screen_title('Change LiteSpeed Port Offset');

		if ($info['return'] != 0) {
			$title = 'Failed to change port offset';
			$buf .= $this->error_panel_mesg($title, $info['output']);
		}
		else {
			$title = 'Saved new port offset successfully';
			$mesg = "Port offset has been changed to {$info['new_port_offset']}. Please restart LiteSpeed to activate your change.";
			$buf .= $this->info_panel_mesg($title, $mesg);
		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function CheckLicense($info)
	{
		$buf = $this->screen_title('Current License Status');

		if ($info['return'] != 0) {
			$title = 'Error when checking license status';
			$buf .= $this->error_panel_mesg($title, $info['output']);
		}
		else {
			$title = 'Check against license server';
			$buf .= $this->info_panel_mesg($title, $info['output']);
		}

		if ($info['lictype'] == 'trial') {
			$mesg = 'Note: For trial licenses, the expiration date above is based on the most recent trial license you have downloaded. All trial licenses are valid for 15 days from the day you apply. Each IP address, though, may only use trial licenses for 30 days from the date of the first application. The expiration date above does not reflect how much longer your IP may use trial licenses. If you are on your second or third trial license, your actual trial period may end earlier than the above date.';
			$buf .= $this->info_mesg($mesg);
		}
		elseif ($info['lictype'] == 'migrated') {
			$mesg = 'Note: You have started the license migration process. You can now use the same serial number to register on a new machine. If you decide you want to continue using the license on this machine instead, you must re-register the license here. Use the Change License function with the serial number to re-register.';
			$buf .= $this->warning_mesg($mesg);
		}

		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function UninstallLswsPrepare($info)
	{
		$buf = $this->screen_title('Confirm Operation... Uninstall LiteSpeed Web Server');
		$buf .= $this->show_running_status($info);

		if ($info['stop_msg'] != NULL) {
			$buf .= $this->error_mesg($info['stop_msg']);
			$buf .= $this->button_panel_back('OK');
		}
		else {
			$buf .= $this->section_title('Uninstall Options');

			if ($info['ls_pid'] > 0) {
				$mesg[] = 'LiteSpeed is currently running on port offset ' . $info['port_offset'] . ' and will be stopped first.';
			}
			$mesg[] = 'All subdirectories created under ' . LSWS_HOME . ' during installation will be removed! The conf/ and logs/ subdirectories can be preserved using the check boxes below.';
			$mesg[] = 'If you want to preserve any files under other subdirectories created by the installation script, please manually back them up before proceeding.';
			$buf .= $this->warning_mesg($mesg);

			$input = $this->input_checkbox('keep_conf', 1);
			$buf .= $this->form_row('Keep conf/ directory', $input, NULL, NULL, TRUE);

			$input = $this->input_checkbox('keep_log', 1);
			$buf .= $this->form_row('Keep logs/ directory', $input, NULL, NULL, TRUE);

			$buf .= $this->button_panel_cancel_next('Cancel', 'Uninstall');
		}

		$this->bufs[] = $buf;
	}

	public function UninstallLsws($info)
	{
		$buf = $this->screen_title('Uninstall LiteSpeed Web Server');
		$buf .= $this->show_running_status($info);

		if ($info['return'] != 0) {
			$title = 'Error when uninstalling LiteSpeed';
			$buf .= $this->error_panel_mesg($title, $info['output']);
		}
		else {
			$title = 'Uninstalled successfully';
			$buf .= $this->info_panel_mesg($title, $info['output']);
		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	private function show_choose_license($info)
	{
		$buf = '<div><iframe src="LICENSE.html" width="650" height="400"></iframe></div>';

		$input = $this->input_checkbox('license_agree', 'agree', ($info['license_agree']=='agree'));
		$buf .= $this->form_row('I agree', $input, $info['error']['license_agree'], NULL, TRUE);

		$buf .= $this->section_title('Choose a License Type');

		$input = $this->input_radio('install_type', 'prod', ($info['install_type']=='prod'));
		$buf .= $this->form_row('Use an Enterprise license', $input, $info['error']['install_type'], NULL, TRUE);

		$input = $this->input_text('serial_no', $info['serial_no'], 1);
		$hints = array('Your serial number is sent via email when you purchase a LiteSpeed Web Server license. You can also copy it from your service details in our client area (store.litespeedtech.com).');
		$buf .= $this->form_row('Input serial number:', $input, $info['error']['serial_no'], $hints);

		$buf .= $this->warning_mesg('If your license is currently running on another server, you will need to transfer the license (using the Transfer License function) before registering it on this server.');

		$input = $this->input_radio('install_type', 'trial', ($info['install_type']=='trial'));
		$hints = array('This will retrieve a trial license from the LiteSpeed license server.',
						'Each trial license is valid for 15 days from the date you apply.',
						'Each IP address can only use trial licenses for 30 days from the date of your first application.',
						'If you need to extend your trial period, please contact the sales department at litespeedtech.com.');
		$buf .= $this->form_row('Request a trial license', $input, $info['error']['install_type'], NULL, TRUE);
		$buf .= $this->form_row('', '', NULL, $hints);
		return $buf;
	}

	public function InstallLswsPrepare($info)
	{
		$buf = $this->screen_title('Installing LiteSpeed Web Server');

		$buf .= $this->show_choose_license($info);

		$buf .= $this->section_title('Installation Options');

		$input = $this->input_text('lsws_home_input', $info['lsws_home_input'], 1);
		$buf .= $this->form_row('Installation directory (define LSWS_HOME):', $input, $info['error']['lsws_home_input']);

		$input = $this->input_text('port_offset', $info['port_offset']);
		$hints = array('Setting a port offset allows you to run LiteSpeed on a different port in parallel with Apache. The port offset will be added to your Apache port number to determine your LiteSpeed port.',
					'It is recommended that you run LiteSpeed in parallel first, so you can fully test it before switching to LiteSpeed.');
		$buf .= $this->form_row('Port offset: ', $input, $info['error']['port_offset'], $hints);

		$input = $this->input_checkbox('php_suexec', 'enable', ($info['php_suexec']=='enable'));
		$hints = 'Recommended for shared hosting.';
		$buf .= $this->form_row('Enable PHP SuEXEC:', $input, NULL, $hints);

		$input = $this->input_text('admin_email', $info['admin_email'], 2);
		$hints = array('(Use commas to separate multiple email addresses.)',
						'Email addresses specified will receive messages about important events, such as server crashes or license expirations.');
		$buf .= $this->form_row('Administrator email(s):', $input, $info['error']['admin_email'], $hints);

		$buf .= $this->section_title('WebAdmin Console Login');

		$input = $this->input_text('admin_login', $info['admin_login']);
		$buf .= $this->form_row('User name:', $input, $info['error']['admin_login']);

		$input = $this->input_password('admin_pass', $info['admin_pass']);
		$buf .= $this->form_row('Password:', $input, $info['error']['admin_pass']);

		$input = $this->input_password('admin_pass1', $info['admin_pass1']);
		$buf .= $this->form_row('Retype password:', $input, $info['error']['admin_pass1']);

		$buf .= $this->section_title('After Installation Notes (Important)');
		$warn[] = '<p>Need to install a matching PHP with LSAPI.</p>';

		if ($info['rpm'] != NULL) {
		$warn[] = '<p>For CentOS 5/6/7, a basic RPM package for is available. This package contains all commonly used options and <strong>will be installed automatically</strong>.
            This package is enough for most CentOS users, but if your applications require special options, you will need to install a new PHP via RPM or compile a custom build of the PHP binary.</p>';
            $input = $this->input_select('php_version', $info['php_options'], $info['php_version']);
            $buf .= $this->form_row('Select your PHP version:', $input);
		}
		else {
			$warn[] = '<p>You will need to install a new PHP via RPM or compile a custom build of the PHP binary.</p>';
		}

        $warn[] = '<p class="hint"><a href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:php:rpm" target="_blank">Guide</a> to installing via RPM &nbsp;&nbsp;&nbsp;
                <a href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:php:lsapi" target="_blank">Guide</a> to compiling PHP via WebAdmin console</p>';

		$buf .= $this->warning_panel_mesg('Install PHP for LiteSpeed Web Server (PHP with LSAPI)', $warn);

		$buf .= $this->button_panel_cancel_next('Cancel', 'Install');
		$this->bufs[] = $buf;
	}

	public function InstallLsws($info)
	{
		$buf = $this->screen_title('Install LiteSpeed Web Server');
		$buf .= $this->show_running_status($info);

		if ($info['return'] != 0) {
			$title = 'Error when installing LiteSpeed';
			$buf .= $this->error_panel_mesg($title, $info['output']);
		}
		else {
			$title = 'LiteSpeed installed successfully';
			$buf .= $this->info_panel_mesg($title, $info['output']);

			if ($info['rpm'] != NULL) {
				$warn[] = '<p><strong>For CentOS 5/6/7, an RPM package for ' . $info['php_version'] . ' is available.
                    This package contains all commonly used options and was installed automatically.</strong> This package is enough for most CentOS users,
                    but if your applications require special options, you will need to install a new PHP via RPM or compile a custom build of the PHP binary.</p>';
			}
			else {
				$warn[] = '<p>You will need to install a new PHP via RPM or compile a custom build of the PHP binary.</p>';
			}

            $warn[] = '<p class="hint"><a href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:php:rpm" target="_blank">Guide</a> to installing via RPM &nbsp;&nbsp;&nbsp;
                <a href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:php:lsapi" target="_blank">Guide</a> to compiling PHP via WebAdmin console</p>';

			$buf .= $this->warning_panel_mesg('Install PHP for LiteSpeed Web Server (PHP with LSAPI)', $warn);

		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function ChangeLicensePrepare($info)
	{
		$buf = $this->screen_title('Changing Software License for LiteSpeed Web Server');
		$buf .= $this->show_choose_license($info);
		$buf .= $this->button_panel_cancel_next('Cancel', 'Switch');
		$this->bufs[] = $buf;
	}

	public function ChangeLicense($info)
	{
		$buf = $this->screen_title('Changing Software License for LiteSpeed Web Server');
		$buf .= $this->show_running_status($info);

		if ($info['return'] != 0) {
			$title = 'Error when activating new license';
			$buf .= $this->error_panel_mesg($title, $info['output']);
		}
		else {
			$title = 'New license activated successfully';
			$buf .= $this->info_panel_mesg($title, $info['output']);
		}
		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function TransferLicenseConfirm($info)
	{
		$buf = $this->screen_title('LiteSpeed Web Server License Migration Confirm');

		$mesg = 'You can transfer your license from one server to another. This migration process will allow you to continue to use your current server for 3 days while you migrate to your new server. If, after 3 days, you still need more time to use LiteSpeed on this server, please download a 15-day trial license. (You will need to contact LiteSpeed Technologies to reset your trial record if this server has previously used trial licenses.)';

		$buf .= $this->info_panel_mesg(NULL, $mesg);

		$buf .= $this->info_panel_mesg('Current license status', $info['licstatus_output']);

		if ($info['error'] != NULL) {
			$buf .= $this->error_mesg($info['error']);
			$buf .= $this->button_panel_back('OK');
		}
		else {
			$mesg = 'Click Transfer if you are ready to go ahead and transfer your current license. You can continue using this server for up to 3 days.';
			$buf .= $this->warning_mesg($mesg);

			$buf .= $this->button_panel_cancel_next('Cancel', 'Transfer');
		}
		$this->bufs[] = $buf;
	}

	public function TransferLicense($info)
	{
		$buf = $this->screen_title('LiteSpeed Web Server License Migration');

		if ($info['return'] == 0)
			$buf .= $this->info_panel_mesg('Successfully migrated your license', $info['output']);
		else
			$buf .= $this->error_panel_mesg('Failed to migrate your license', $info['output']);

		$buf .= $this->button_panel_back('OK');
		$this->bufs[] = $buf;
	}

	public function DefineHome($info)
	{
		$buf = $this->screen_title('Define LSWS_HOME Location for Existing LiteSpeed Installation');

		$buf .= $this->info_mesg('If LiteSpeed is already installed on this server, please specify the LSWS_HOME location in order for this extension to work properly.');

		$buf .= $this->section_title('Define $LSWS_HOME');

		if (isset($info['do_action'])) {
			$buf .= $this->input_hidden('do', $info['do_action']);
		}

		$hints[] = 'Your LiteSpeed binary is located at $LSWS_HOME/bin/lshttpd.';
		$hints[] = 'Common locations for LSWS_HOME include /usr/local/lsws, /opt/lsws';
		$input = $this->input_text('lsws_home_input', $info['lsws_home_input'], 1);
		$buf .= $this->form_row('$LSWS_HOME location', $input, $info['error'], $hints);

		$buf .= $this->button_panel_cancel_next('Cancel', 'Save');
		$this->bufs[] = $buf;
	}

	public function VersionManager($info)
	{
		$buf =<<<EOS
<input type="hidden" name="act"/>
<input type="hidden" name="actId"/>

<SCRIPT type="text/javascript">
function vermgr(act, actId)
{
	document.lswsform.act.value = act;
	document.lswsform.actId.value = actId;
	t = "";
	if ( act == 'download' ) {
		t = "download and upgrade to the latest build of version " + actId;
	}
	else if ( act == 'switchTo' ) {
		t = "switch to release " + actId;
	}
	else if ( act == 'remove' )
		t = "remove release " + actId;

	if (t == "" || confirm("Are you sure you want to " + t + "?") ) {
		document.lswsform.step.value = 2;
		document.lswsform.submit();
	}
}
</SCRIPT>
EOS;


		$buf .= $this->screen_title('Version Management');

		if ($info['error'] != NULL)
			$buf .= $this->error_mesg($info['error']);

		if ( isset($info['output']) ) {
			if ($info['return'] == 0)
				$buf .= $this->info_panel_mesg('Successfully switched the version.');
			elseif ($info['return'] == 2)
				$buf .= $this->info_panel_mesg('Successfully removed the selected version.', $info['output']);
			else
				$buf .= $this->error_panel_mesg('Error occurred.', $info['output']);
		}

		$buf .= '<div class="box-area"><div class="list"><table><tbody>';

		if ($info['new_version'] != '' && !in_array($info['new_version'], $info['installed'])) {
			$buf .= '<tr><th>Latest Release</th><th>Action</th></tr>';
			$buf .= '<tr class="objects-toolbar"><td style="vertical-align: middle;">'
				. $info['new_version'] . '</td><td class="objects-toolbar">';
			$buf .= '<a class="s-btn" href="javascript:vermgr(\'download\',\''
				. $info['new_version'] . '\')"><img src="' . $this->icons['v_upgrade'] . '"></img> Upgrade</a></td></tr>';
		}
		$buf .= '<tr><th>Installed Versions</th><th>Action</th></tr>'; $this->section_title('Installed Versions');

		$installed = $info['installed'];
		natsort($installed);
		$installed = array_reverse($installed);

		foreach( $installed as $rel ) {
			$buf .= '<tr class="objects-toolbar"><td style="vertical-align: middle;">' . $rel;
			if ( $info['lsws_version'] == $rel )
				$buf .= ' <img title="Current Active Version" src="' . $this->icons['v_active']. '" alt="Active"></img>';

			$buf .= '</td><td><a class="s-btn" title="update to latest build" href="javascript:vermgr(\'download\',\''
				. $rel . '\')"><img src="' . $this->icons['v_reinstall'] . '"></img> Force Reinstall</a> &nbsp;&nbsp;';
			if ( $info['lsws_version'] != $rel ) {
				$buf .= '<a class="s-btn" href="javascript:vermgr(\'switchTo\',\'' . $rel . '\')"><img src="'
					. $this->icons['v_switchto'] . '"></img> Switch To</a> &nbsp;&nbsp;';
				$buf .= '<a class="s-btn" href="javascript:vermgr(\'remove\',\'' . $rel . '\')"><img src="'
					. $this->icons['v_remove'] .'"></img> Remove</a>';
			}
			$buf .= "</td></tr>\n";
		}
		$buf .= '</tbody></table></div></div>' . "\n";

		$this->bufs[] = $buf;
	}

	private function div_msg_box($mesg, $subtype='')
	{
		$style = 'msg-box';
		if ($subtype != '')
			$style .= " $subtype";

		$div = '<div class="' . $style . '"><div class="msg-content">';
		if (is_array($mesg)) {
			$div .= '<ul><li>';
			$div .= implode('</li><li>', $mesg);
			$div .= '</li></ul>';
		}
		else
			$div .= $mesg;

		$div .= '</div></div>';
		return $div;
	}

	private function info_mesg($mesg)
	{
		return $this->div_msg_box($mesg, 'msg-info');
	}

	private function error_mesg($mesg)
	{
		return $this->div_msg_box($mesg, 'msg-error');
	}

	private function warning_mesg($mesg)
	{
		return $this->div_msg_box($mesg, 'msg-warning');
	}

	private function info_panel_mesg($title, $mesg)
	{
		return $this->div_mesg_panel($title, $mesg, $this->icons['ico_info']);
	}

	private function error_panel_mesg($title, $mesg)
	{
		return $this->div_mesg_panel($title, $mesg, $this->icons['ico_error']);
	}

	private function warning_panel_mesg($title, $mesg)
	{
		return $this->div_mesg_panel($title, $mesg, $this->icons['ico_warning']);
	}

	private function div_mesg_panel($title, $mesg, $icon)
	{
		$box = '<div class="p-box"><div class="p-box-content">';
		if ($title != NULL) {
			$box .= '<div class="title"><div class="title-area"><h4><img src="' . $icon . '" alt=""></img> ' . $title . '</h4><p></p></div></div>';
		}
		$box .= '<div class="content"><div class="content-area"><p>';

		if (is_array($mesg))
			$box .= implode('</p><p>', $mesg);
		else
			$box .= $mesg;

		$box .= '</p></div></div></div></div>' . "\n";
		return $box;
	}

	private function show_running_status($info)
	{
		if ($info['ls_pid'] > 0) {
			$msg = 'LiteSpeed is running (PID = ' . $info['ls_pid'];
			if (isset($info['port_offset']))
				$msg .= ', Apache_Port_Offset = ' . $info['port_offset'];
			$msg .= ').';
		} else {
			$msg = 'LiteSpeed is not running.';
		}

		if ($info['ap_pid'] > 0) {
			$msg .= ' Apache is running (PID = ' . $info['ap_pid'] . ').';
		} else {
			$msg .= ' Apache is not running.';
		}

		$output = $this->info_mesg($msg);

        if ($info['nginx_running'] != '') {
            $output .= $this->error_mesg('Nginx reverse proxy server is currently running and must be stopped. Please go to "Server Management > Tools & Settings > Services Management" and stop nginx.');
		}

        return $output;

	}

	private function screen_title($title, $uplinkself=TRUE)
	{
		$this->_parentview->pageTitle = $title;
		if ($uplinkself)
			$this->_parentview->uplevelLink = MODULE_URL;
		return '';
	}

	private function section_title($title)
	{
		//$div = '<div class="title"><div class="title-area" style="margin:15px 0 10px 0"><h3>' . $title . '</h3></div></div>' . "\n";
		$div = "<div style=\"margin-top:10px\"><fieldset><legend>$title</legend></fieldset></div>\n";
		return $div;
	}

	private function input_text($name, $value, $size_class=0)
	{
		//size 0 : default, size 1: f-middle-size, 2: long
		$iclass = 'input-text';
		if ($size_class == 1)
			$iclass = 'f-middle-size ' . $iclass;
		elseif ($size_class == 2)
			$iclass = '" size="90';
		$input = '<input type="text" class="' . $iclass . '" name="' . $name . '" value="'. $value . '"/>';
		return $input;
	}

    private function input_select($name, $options, $default)
    {
        $input = '<select name="' . $name . '">';
        foreach ($options as $key => $val) {
            $input .= '<option value="' . $key . '" label="' . $val . '"';
            if ($default == $key)
                $input .= ' selected="selected"';
            $input .= '>' . $val . '</option>';
        }
        $input .= '</select>';
        return $input;
    }

	private function input_password($name, $value)
	{
		$input = '<input type="password" name="' . $name . '" value="' . $value . '"/>';
		return $input;
	}

	private function input_checkbox($name, $value, $ischecked)
	{
		$checked = $ischecked ? 'checked="checked"' : '';
		$input = '<input type="checkbox" class="checkbox" name="' . $name . '" value="' . $value . '"'. " $checked />";
		return $input;
	}

	private function input_radio($name, $value, $ischecked)
	{
		$checked = $ischecked ? 'checked="checked"' : '';
		$input = '<input type="radio" class="radiobox" name="' . $name . '" value="' . $value . '"'. " $checked />";
		return $input;
	}

	private function input_hidden($name, $value)
	{
		$input = '<input type="hidden" name="' . $name . '" value="' . $value . '"/>';
		return $input;
	}

	private function form_row($label, $field, $err, $hints=NULL, $is_single=FALSE)
	{
		$divclass = 'form-row';
		$errspan = '';
		$hintspan = '';
		if ($err != NULL) {
			$divclass .= ' error';
			$errspan = '<span class="error-hint">' . $err . '</span>';
		}
		if ($hints != NULL) {
			if (is_array($hints))
				$hintspan = '<span class="hint">' . implode('<br>', $hints) . '</span>';
			else
				$hintspan = '<span class="hint">' . $hints . '</span>';
		}
		$div = '<div class="' . $divclass . '">';

		if ($is_single) {
			$div .= '<div class="single-row">' . $field . "<label>&nbsp;$label</label>";
		}
		else {
			$div .= '<div class="field-name"><label>' . $label . '&nbsp;</label></div><div class="field-value">' . $field;
		}
		$div .= $errspan . $hintspan . '</div></div>' . "\n";

		return $div;
	}

	private function script_button($url, $name, $title, $disabled='false')
	{
		if ($url != SUBMIT) {
			$buf = '<span class="btn" onclick="window.location.href=\'' . $url . '\'"><button type="button" value="" name="' .
					$name . '">' . $title . '</button></span>';
		}
		else {
			$buf = '<span id="btn-' . $name . '" class="btn action"><button type="button" value="" name="'
					. $name . '" onclick="Jsw.submit(this)">' . $title . '</button></span>';
		}

		return $buf;
	}

	private function button_panel_cancel_next($cancel_title, $next_title)
	{
		$buf = '<div class="btns-box"><div class="box-area"><div class="form-row"><div class="field-name"> </div>';
		if ($cancel_title != NULL)
			$buf .= $this->script_button(MODULE_URL, 'cancel', $cancel_title);
		if ($next_title != NULL)
			$buf .= $this->script_button(SUBMIT, 'next', $next_title);
		$buf .= '</div></div></div>';
		return $buf;
	}

	private function button_panel_back($back_title)
	{
		$buf = '<div class="btns-box"><div class="box-area"><div class="form-row"><div class="field-name"> </div>';
		$buf .= $this->script_button(MODULE_URL, 'back', $back_title);
		$buf .= '</div></div></div>';
		return $buf;
	}


}