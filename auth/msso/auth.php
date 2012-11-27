<?php

require_once $CFG->libdir.'/authlib.php';

class auth_plugin_msso extends auth_plugin_base {

    function __construct() {
        $this->authtype = 'msso';
        $this->config = get_config('auth/msso');
    }

    /* Configuration */

    function config_form($config, $err, $user_fields) {
        global $CFG, $SESSION;

        if (!isset($config->idp)) $config->idp = '';
        if (!isset($config->sp)) $config->sp = '';
        if (!isset($config->key)) $config->key = '';
        if (!isset($config->ttl)) $config->ttl = '';

        echo '<table cellspacing="0" cellpadding="5" border="0">';
        $this->config_form_field('idp', $config->idp);
        $this->config_form_field('sp', $config->sp);
        $this->config_form_field('key', $config->key);
        $this->config_form_field('ttl', $config->ttl);
        echo '</table>';
        echo '<table cellspacing="0" cellpadding="5" border="0">';
        print_auth_lock_options($this->authtype, $user_fields,
                                get_string('auth_fieldlocks_help', 'auth'),
                                false, false);
        echo '</table>';
    }

    function is_internal() {
        return false;
    }

    function prevent_local_passwords() {
        return true;
    }

    function process_config($config) {
        $config = stripslashes_recursive($config);
        set_config('idp', trim($config->idp), 'auth/msso');
        set_config('sp', trim($config->sp), 'auth/msso');
        set_config('key', trim($config->key), 'auth/msso');
        set_config('ttl', (int) $config->ttl ?: 30, 'auth/msso');
        return true;
    }

    private function config_form_field($name, $value) {
        $strkey = get_string("config_$name", 'auth_msso');
        echo '<tr valign="top">';
        echo '<td align="right"><label for="'.$name.'">'.$strkey.'</label></td>';
        echo '<td><input size="50" id="'.$name.'" name="'.$name.'" value="'.$value.'"/></td>';
        echo '</tr>';
    }

    /* Hooks */

    function loginpage_hook() {
        if (!empty($this->config->idp)) {
            redirect($this->create_request('idplogin'));
        }
    }

    function logoutpage_hook() {
        global $redirect;

        if (!empty($this->config->sp)) {
            redirect($this->create_request('splogout'));
        } elseif (!empty($this->config->idp)) {
            $redirect = $this->create_request('idplogout');
        }
    }

    /* Requests */

    function create_request($req, $user=false) {
        if (empty($this->config->key)) {
            print_error('error_configkey', 'auth_msso');
        }

        if ($req == 'idplogin' or $req == 'idplogout') {
            if (empty($this->config->idp)) {
                print_error('error_configkey', 'auth_msso');
            }
            $url = $this->config->idp;
        }

        if ($req == 'splogin' or $req == 'splogout') {
            if (empty($this->config->sp)) {
                print_error('error_configsp', 'auth_msso');
            }
            $url = $this->config->sp;
        }

        $url = new moodle_url($url);
        $url->param('req', $req);

        if ($user) {
            $url->param('user', $user);
        }

        if ($req != 'idplogin') {
            $now = time();
            $hash = hash_hmac('sha1', "$req|$user|$now", $this->config->key);
            $url->param('time', $now);
            $url->param('hash', $hash);
        }

        return $url->out();
    }

    function process_request($req, $user, $time, $hash) {
        global $CFG, $SESSION, $USER;

        if (!is_enabled_auth('msso')) {
            print_error('pluginnotenabled', 'auth', '', 'msso');
        }

        if (empty($this->config->key)) {
            print_error('error_configkey', 'auth_msso');
        }

        if ($req != 'idplogin') {
            $this->validate_request($req, $user, $time, $hash);
        }

        if ($req == 'idplogin') {
            require_login(null, false);

            if (!empty($USER->realuser)) {
                print_error('notpermittedtojumpas', 'mnet');
            }

            redirect($this->create_request('splogin', $USER->username));
        }

        if ($req == 'splogin') {
            if (!$user = get_complete_user_data('username', $user, $CFG->mnet_localhost_id)) {
                print_error('error_invaliduser');
            }

            $USER = complete_user_login($user);

            if (!empty($SESSION->wantsurl)) {
                redirect($SESSION->wantsurl);
            } else {
                redirect($CFG->wwwroot.'/');
            }
        }

        if ($req == 'idplogout') {
            require_logout();

            if (!empty($this->config->idp)) {
                redirect($this->create_request('idplogout'));
            } else {
                redirect($CFG->wwwroot.'/');
            }
        }

        if ($req == 'splogout') {
            if (!empty($this->config->sp)) {
                redirect($this->create_request('splogout'));
            }

            require_logout();

            redirect($this->create_request('idplogout'));
        }

        print_error('invalidrequest', 'auth_msso');
    }

    function validate_request($req, $user, $time, $hash) {
        if ($hash != hash_hmac('sha1', "$req|$user|$time", $this->config->key)) {
            print_error('error_invalidhash', 'auth_msso');
        }

        if ($time + (int) $this->config->ttl <= time()) {
            print_object($time);
            print_object($this->config->ttl);
            print_error('error_expiredrequest', 'auth_msso');
        }
    }
}
