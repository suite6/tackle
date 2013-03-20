<?php

/*
 * The MIT License (MIT)
 * Copyright (c) 2013 Louis-Eric Simard, Suite6
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 */

namespace suite6\Tackle\Generator;

class TackleGeneratorNginx {

    private $settings;
    private $default_policy;

    const config_file_name = 'nginx.conf';
    const order_allow = 'Order allow,deny';
    const order_deny = 'Order deny,allow';
    const allow_all = 'allow all';
    const deny_all = 'deny all';
    const deny_from = 'deny';
    const redirect = 'Redirect';
    const redirect_match = 'RedirectMatch';
    const rewrite_condition = 'if';
    const rewrite_rule = 'rewrite';
    const prevent_list = 'off';
    const allow_list = 'on';
    const follow_sym = 'off';
    const unfollow_sym = 'on';
    const root_folder = '/[root]/';

    public function __construct(\suite6\Tackle\TackleConfiguration $settings) {
        $this->settings = $settings;
    }

    public function generate_configs() {
        $result = array();
        $rootConfig = array();
        $result['[root]'] = array();

        //defining file name
        $rootConfig['name'] = self::config_file_name;
        $rootConfig['content'] = 'location ' . self::root_folder . ' {' . PHP_EOL;

        //set default policy configuration
        if ($this->settings->get_default_policy() == 'ALLOW') {
            //$this->default_policy=self::order_allow; //does not exists for nginx
            //$rootConfig['content'] .= self::order_allow . PHP_EOL; //does not exists for nginx
            $rootConfig['content'] .= self::allow_all . ';' . PHP_EOL;
        } else {
            //$this->default_policy=self::order_deny; //does not exists for nginx
            //$rootConfig['content'] .= self::order_deny . PHP_EOL; //does not exists for nginx
            $rootConfig['content'] .= self::deny_all . ';' . PHP_EOL;
        }

        //set default directory listing policy configuration
        if ($this->settings->get_default_directory_listing_policy() == 'ALLOW') {
            $rootConfig['content'] .= 'autoindex ' . self::allow_list . ';' . PHP_EOL;
        } else {
            $rootConfig['content'] .= 'autoindex ' . self::prevent_list . ';' . PHP_EOL;
        }

        //set symbolic link configuration
        if ($this->settings->get_sym_link() == 'FOLLOW') {
            $rootConfig['content'] .= 'disable_symlinks ' . self::follow_sym . ';' . PHP_EOL;
        } elseif ($this->settings->get_sym_link() == 'UNFOLLOW') {
            $rootConfig['content'] .= 'disable_symlinks ' . self::unfollow_sym . ';' . PHP_EOL;
        }

        //set banned ips
        foreach ($this->settings->get_banned_ips() as $value) {
            $rootConfig['content'] .= self::deny_from . ' ' . $value . ';' . PHP_EOL;
        }

        //set denied files list 403
        foreach ($this->settings->get_denied_files() as $value) {
            $file_name = ltrim($value, '/');
            $arrFile = array();
            $arrFile['name'] = self::config_file_name;
            $arrFile['content'] = 'location ' . self::root_folder . $file_name . '{' . PHP_EOL;
            //$rootConfig['content'] .= $this->default_policy . PHP_EOL; //does not exists for nginx
            $arrFile['content'] .= self::deny_all . ';' . PHP_EOL;
            $arrFile['content'] .= '}' . PHP_EOL . PHP_EOL;
            $result[self::root_folder . $file_name] = $arrFile;
        }

        //set allowed files list 
        foreach ($this->settings->get_allowed_files() as $value) {
            $file_name = ltrim($value, '/');
            $arrFile = array();
            $arrFile['name'] = self::config_file_name;
            $arrFile['content'] = 'location ' . self::root_folder . $file_name . '{' . PHP_EOL;
            //$rootConfig['content'] .= $this->default_policy . PHP_EOL; //does not exists for nginx
            $arrFile['content'] .= self::allow_all . ';' . PHP_EOL;
            $arrFile['content'] .= '}' . PHP_EOL . PHP_EOL;
            $result[self::root_folder . $file_name] = $arrFile;
        }

        //set denied directories list 403
        foreach ($this->settings->get_denied_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            $arrDir = array();
            $arrDir['name'] = self::config_file_name;
            //$arrDir['content'] = $this->default_policy . PHP_EOL; //does not exists for nginx
            $arrDir['content'] = 'location ' . self::root_folder . $dir_name . '{' . PHP_EOL;
            $arrDir['content'] .= self::deny_all . ';' . PHP_EOL;
            $arrDir['content'] .= '}';
            $result[self::root_folder . $dir_name] = $arrDir;
        }

        //set allowed directories list 
        foreach ($this->settings->get_allowed_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            $arrDir = array();
            $arrDir['name'] = self::config_file_name;
            //$arrDir['content'] = $this->default_policy . PHP_EOL; //does not exists for nginx
            $arrDir['content'] = 'location ' . self::root_folder . $dir_name . '{' . PHP_EOL;
            $arrDir['content'] .= self::allow_all . ';' . PHP_EOL;
            $arrDir['content'] .= '}';
            $result[self::root_folder . $dir_name] = $arrDir;
        }

        /* section: handling all types of redirects */
        //$rootConfig['content'] .= '<IfModule mod_rewrite.c>'. PHP_EOL; //does not exists for nginx
        //$rootConfig['content'] .= 'RewriteEngine on'. PHP_EOL; //does not exists for nginx
        //handling internal redirects
        foreach ($this->settings->get_internal_redirects() as $from => $to) {
            if (is_string($to)) {
                $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ';' . PHP_EOL;
            } elseif (get_class($to) == 'suite6\Tackle\TackleRule') {
                $rule = $to->get_rule_condition();
                for ($condition_counter = 0; $condition_counter < count($rule); $condition_counter++) {
                    //if multiple flags are provided
                    if (is_array($rule[$condition_counter]->get_condition_combine())) {
                        $all_conditions = '';
                        foreach ($rule[$condition_counter]->get_condition_combine() as $condition) {
                            if ($all_conditions != '')
                                $all_conditions.= ',';
                            switch ($condition) {
                                case 'OR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ' ~ ' . $rule[$condition_counter]->get_match_pattern() . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                case 'FILE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (-f ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                case 'DIR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (-d ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                case 'NOT_FILE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (!-f ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                case 'NOT_DIR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (!-d ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                case 'NOT_CASE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' (' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ' ~* ' . $rule[$condition_counter]->get_match_pattern() . ')' . PHP_EOL;
                                    $rootConfig['content'] .= '{' . PHP_EOL;
                                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                    $rootConfig['content'] .= '}' . PHP_EOL;
                                    break;
                                default:
                                    break;
                            }
                        }
                    } else {
                        switch ($rule[$condition_counter]->get_condition_combine()) {
                            case 'OR':
                                $rootConfig['content'] .= self::rewrite_condition . ' (' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ' ~ ' . $rule[$condition_counter]->get_match_pattern() . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            case 'FILE':
                                $rootConfig['content'] .= self::rewrite_condition . ' (-f ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            case 'DIR':
                                $rootConfig['content'] .= self::rewrite_condition . ' (-d ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            case 'NOT_FILE':
                                $rootConfig['content'] .= self::rewrite_condition . ' (!-f ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            case 'NOT_DIR':
                                $rootConfig['content'] .= self::rewrite_condition . ' (!-d ' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            case 'NOT_CASE':
                                $rootConfig['content'] .= self::rewrite_condition . ' (' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ' ~* ' . $rule[$condition_counter]->get_match_pattern() . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                                break;
                            default:
                                $rootConfig['content'] .= self::rewrite_condition . ' (' . \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_nginx) . ' ~ ' . $rule[$condition_counter]->get_match_pattern() . ')' . PHP_EOL;
                                $rootConfig['content'] .= '{' . PHP_EOL;
                                $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . ';' . PHP_EOL;
                                $rootConfig['content'] .= '}' . PHP_EOL;
                        }
                    }
                }
            }
        }

        //handling temporary redirects 302
        foreach ($this->settings->get_temporary_redirects() as $from => $to) {
            $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' redirect' . ';' . PHP_EOL;
        }

        //handling permenent redirects 301
        foreach ($this->settings->get_permanent_redirects() as $from => $to) {
            $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' permanent' . ';' . PHP_EOL;
        }

        //handling domain redirects 301
        foreach ($this->settings->get_domain_redirects() as $from => $to) {
            $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' permanent' . ';' . PHP_EOL;
        }

        //handling gzip extensions
        if (count($this->settings->get_gzip_serve_extensions()) > 0 || count($this->settings->get_gzip_per_file()) > 0) {
            $rootConfig['content'] .= 'gzip on;' . PHP_EOL;
            $content_types = '';
            foreach ($this->settings->get_gzip_serve_extensions() as $ext => $content_type) {
                if ($content_types !== '')
                    $content_types.=' ';
                $content_types .=$content_type;
            }
            //handling content type per file
            foreach ($this->settings->get_gzip_per_file() as $ext => $content_type) {
                if ($content_types !== '')
                    $content_types.=' ';
                $content_types .=$content_type;
            }
            $rootConfig['content'] .= 'gzip_types ' . $content_types . ';' . PHP_EOL;
        }
        //$rootConfig['content'] .= '</IfModule>'. PHP_EOL; //does not exists for nginx
        /* end section: handling all types of redirects */

        //handling listable directories
        foreach ($this->settings->get_listable_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            if (array_key_exists(self::root_folder . $dir_name, $result)) {
                $result[self::root_folder . $dir_name]['content'] = rtrim($result[self::root_folder . $dir_name]['content'], '}');
                $result[self::root_folder . $dir_name]['content'] .= 'autoindex ' . self::allow_list . ';' . PHP_EOL;
                $result[self::root_folder . $dir_name]['content'] .= '}';
            } else {
                $arrDir = array();
                $arrDir['name'] = self::config_file_name;
                $arrDir['content'] = 'location ' . self::root_folder . $dir_name . '{' . PHP_EOL;
                $arrDir['content'] .= 'autoindex ' . self::allow_list . ';' . PHP_EOL;
                $arrDir['content'] .= '}';
                $result[self::root_folder . $dir_name] = $arrDir;
            }
        }

        //setting default directory handler/document root
        if ($this->settings->get_default_directory_handler() !== null && $this->settings->get_default_directory_handler() != '') {
            $rootConfig['content'] .= 'index ' . $this->settings->get_default_directory_handler() . ';' . PHP_EOL;
        }

        //setting default 404
        if ($this->settings->get_default_404_handler() !== null && $this->settings->get_default_404_handler() != '') {
            $rootConfig['content'] .= 'error_page 404 ' . $this->settings->get_default_404_handler() . ';' . PHP_EOL;
        }

        //setting default 403
        if ($this->settings->get_default_403_handler() !== null && $this->settings->get_default_403_handler() != '') {
            $rootConfig['content'] .= 'error_page 403 ' . $this->settings->get_default_403_handler() . ';' . PHP_EOL;
        }

        //handling php configs
        foreach ($this->settings->get_php_configs() as $key => $value) {
            $rootConfig['content'] .= 'fastcgi_param PHP_VALUE ' . $key . ' = ' . $value . ';' . PHP_EOL;
        }

        //handling php flags
        foreach ($this->settings->get_php_flags() as $key => $value) {
            $rootConfig['content'] .= 'fastcgi_param PHP_VALUE ' . $key . ' = ' . $value . ';' . PHP_EOL;
        }

        //handling cache
        //below 2 lines must be written before any server declaration
        //proxy_cache_path  /cache levels=1:2 keys_zone=CUSTOM_ZONE:8m max_size=1000m inactive=600m;
        //proxy_temp_path /tmp;
        if ($this->settings->get_time_to_expiration() != '') {
            $rootConfig['content'] .= 'proxy_cache <ZONE_NAME_HERE>;' . PHP_EOL;
            $rootConfig['content'] .= 'proxy_cache_key $host$uri#is_args$args;' . PHP_EOL;
            $rootConfig['content'] .= 'expires ' . $this->settings->get_time_to_expiration() . PHP_EOL;
            if (!$this->settings->get_cache_php_script()) {
                $arrPHP = array();
                $arrPHP['name'] = self::config_file_name;
                $arrPHP['content'] = 'location ~ \.php {' . PHP_EOL;
                $arrPHP['content'] .= 'proxy_cache off;' . PHP_EOL;
                $arrPHP['content'] .= 'expires 0;' . PHP_EOL;
                $arrPHP['content'] .= '}';
                $result['~ \.php'] = $arrPHP;
            }
        }

        //handling etag
        if ($this->settings->get_etag() !== null) {
            //there is no support for etag
        }

        //handling etag files
        foreach ($this->settings->get_etag_files() as $file => $etag) {
            //there is no support for etag
        }

        //setting default directory handler/document root
        if ($this->settings->get_last_modified() !== null && $this->settings->get_last_modified() != '') {
            $rootConfig['content'] .= 'add_header Last-Modified "' . $this->settings->get_last_modified() . '";' . PHP_EOL;
        }

        //setting default directory handler/document root
        if ($this->settings->get_cache_control() !== null && $this->settings->get_cache_control() != '') {
            $rootConfig['content'] .= 'add_header Cache-Control "' . $this->settings->get_cache_control() . '";' . PHP_EOL;
        }

        $rootConfig['content'] .= '}';

        $result['[root]'] = $rootConfig;

        return $result;
    }

}