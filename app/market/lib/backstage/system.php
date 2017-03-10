<?php
class market_backstage_system{


	function upgrade(){
		kernel::single('base_shell_webproxy')->exec_command("update --ignore-download");
		ilog('upgrade:Update '.$_SERVER['SERVER_NAME'].' Ok.');
		return array('status'=>'succ');
	}

}

