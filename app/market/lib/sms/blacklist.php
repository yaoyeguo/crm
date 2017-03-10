<?php
class sms_command_blacklist extends base_shell_prototype {
	public $command_update = 'Update Blacklist';
	public function command_update() {
		sms_utils::update_blacklist();
	}
}