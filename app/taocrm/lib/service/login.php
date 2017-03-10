<?php
class taocrm_service_login{
    public function signErrorReturn($params) {
        if ($params['visitor_role'] == 'taobao') {
            header("location: http://fuwu.taobao.com/service/my_service.htm");
            exit;
        } else {
            return false;
        }
    }

    public function realLogin($params, $type) {
        $account_id = $this->check_name($params['visitor_nick']);

        if (!$account_id) {
            $account_id = $this->insert_user($params, $type);
        }

        if ($account_id) {
            kernel::single('base_session')->start();
            $_SESSION['account'][$type] = $account_id;
            $_SESSION['login_time'] = time();

            if ($params['visitor_role'] == 'taobao') {
                app::get('taocrm')->setconf('tb_session', $params['top_session']);
                app::get('taocrm')->setconf('tb_nick', $params['visitor_nick']);
                app::get('taocrm')->setconf('tb_uid', $params['visitor_id']);
            }

            $users = app::get('desktop')->model('users');
            $aUser = $users->dump($account_id, '*');
            $sdf['lastlogin'] = $_SESSION['login_time'] ? $_SESSION['login_time'] : time();
            $sdf['logincount'] = $aUser['logincount'] + 1;
            $users->update($sdf, array('user_id' => $account_id));

            return true;
        }

        return false;
    }

    public function check_name($login_name=null) {
        $account = app::get('pam')->model('account');
        $row = $account->getList('account_id', array('login_name' => $login_name));

        if ($row)
            return $row[0]['account_id'];
        else
            return false;
    }

    public function insert_user($params, $type) {
        if (!$params) return false;

        $account = array(
            'pam_account' => array(
                'login_name' => $params['visitor_nick'],
                'login_password' => md5(DB_PASSWORD),
                'account_type' => $type,
            ),
            'name' => $params['visitor_nick'],
            'super' => 1,
            'status' => 1
        );

        if (app::get('desktop')->model('users')->save($account)) {
            return $account['pam_account']['account_id'];
        } else {
            return false;
        }
    }

    public function login($params=null) {
        if (!$params) return false;
        $sign = strtoupper(md5(SASS_APP_KEY . $params['saas_params'] . $params['saas_ts'] . SAAS_SECRE_KEY));
        $saasParams = base64_decode($params['saas_params']);
        $saasParams = @split('&', $saasParams);

        foreach ((array) $saasParams as $param) {
            if (strpos($param, '=') === false) {
                $key = $param;
                $value = '';
            } else {
                $pos = strpos($param, '=');
                $key = substr($param, 0, $pos);
                $value = substr($param, $pos + 1, strlen($param) - $pos);
            }

            $sParams[$key] = $value;
        }

        if ($sign != $params['saas_sign']) {
            return $this->signErrorReturn($sParams);
        } else {
            if (is_array($saasParams) && !empty($saasParams)) {
                if (abs(time() - $params['saas_ts']) > 86400) {
                    //检查时间，已经过了有效期
                    return $this->signErrorReturn($sParams);
                } else {
                    if (trim($sParams['server_name']) != trim($_SERVER['SERVER_NAME'])) {
                        return $this->signErrorReturn($sParams);
                    } else {
                        return $this->realLogin($sParams, $params['type']);
                    }
                }
            } else {
                if (trim($sParams['server_name']) != trim($_SERVER['SERVER_NAME'])) {
                    return $this->signErrorReturn($sParams);
                } else {
                    $sParams['visitor_nick'] = 'admin';
                    return $this->realLogin($sParams, $params['type']);
                }
            }
        }
    }
}
