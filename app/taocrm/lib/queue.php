<?php
class taocrm_queue {
	public function sendSms(&$cursor_id, $params) {
       taocrm_sms::sendMany($params['msgs']);
	}
}