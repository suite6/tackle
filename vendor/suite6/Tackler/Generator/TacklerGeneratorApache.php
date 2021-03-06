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

namespace suite6\Tackler\Generator;

class TacklerGeneratorApache extends BaseTacklerGenerator {

    private $settings;
    private $default_policy;

    const config_file_name = '.htaccess';
    const order_allow = 'Order allow,deny';
    const order_deny = 'Order deny,allow';
    const allow_all = 'allow from all';
    const deny_all = 'deny from all';
    const deny_from = 'deny from';
    const redirect = 'Redirect';
    const redirect_match = 'RedirectMatch';
    const rewrite_condition = 'RewriteCond';
    const rewrite_rule = 'RewriteRule';
    const prevent_list = '-Indexes';
    const allow_list = '+Indexes';
    const follow_sym = '+FollowSymLinks';
    const unfollow_sym = '-FollowSymLinks';
    
    public function __construct(\suite6\Tackler\TacklerConfiguration $settings) {
        $this->settings = $settings;
    }

    
    public function generate_configs() {
        $result = array();
        $rootConfig = array();
        $result['[root]'] = array();

        //defining file name
        $rootConfig['name'] = '[root]' . self::config_file_name;
        $rootConfig['content'] = '';

        //set default policy configuration
        if ($this->settings->get_default_policy() == 'ALLOW') {
            $this->default_policy = self::order_allow;
            $rootConfig['content'] .= self::order_allow . PHP_EOL;
            $rootConfig['content'] .= self::allow_all . PHP_EOL;
        } else {
            $this->default_policy = self::order_deny;
            $rootConfig['content'] .= self::order_deny . PHP_EOL;
            $rootConfig['content'] .= self::deny_all . PHP_EOL;
        }

        //set default directory listing policy configuration
        if ($this->settings->get_default_directory_listing_policy() == 'ALLOW') {
            $rootConfig['content'] .= 'Options ' . self::allow_list . PHP_EOL;
        } else {
            $rootConfig['content'] .= 'Options ' . self::prevent_list . PHP_EOL;
        }

        //set symbolic link configuration
        if ($this->settings->get_sym_link() == 'FOLLOW') {
            $rootConfig['content'] .= 'Options ' . self::follow_sym . PHP_EOL;
        } elseif ($this->settings->get_sym_link() == 'UNFOLLOW') {
            $rootConfig['content'] .= 'Options ' . self::unfollow_sym . PHP_EOL;
        }

        //set banned ips
        foreach ($this->settings->get_banned_ips() as $value) {
            $rootConfig['content'] .= self::deny_from . ' ' . $value . PHP_EOL;
        }

        //set denied files list 403
        foreach ($this->settings->get_denied_files() as $value) {
            $rootConfig['content'] .= '<FilesMatch "' . $value . '">' . PHP_EOL;
            $rootConfig['content'] .= $this->default_policy . PHP_EOL;
            $rootConfig['content'] .= self::deny_all . PHP_EOL;
            $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL . PHP_EOL;
        }

        //set allowed files list 
        foreach ($this->settings->get_allowed_files() as $value) {
            $rootConfig['content'] .= '<FilesMatch "' . $value . '">' . PHP_EOL;
            $rootConfig['content'] .= $this->default_policy . PHP_EOL;
            $rootConfig['content'] .= self::allow_all . PHP_EOL;
            $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL . PHP_EOL;
        }

        //set denied directories list 403
        foreach ($this->settings->get_denied_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            $arrDir = array();
            $arrDir['name'] = '\'' . '[root]/' . $dir_name . '/' . self::config_file_name . '\'';
            $arrDir['content'] = $this->default_policy . PHP_EOL;
            $arrDir['content'] .= self::deny_all . PHP_EOL;
            $result['[root]/' . $dir_name] = $arrDir;
        }

        //set allowed directories list 
        foreach ($this->settings->get_allowed_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            $arrDir = array();
            $arrDir['name'] = '\'' . '[root]/' . $dir_name . '/' . self::config_file_name . '\'';
            $arrDir['content'] = $this->default_policy . PHP_EOL;
            $arrDir['content'] .= self::allow_all . PHP_EOL;
            $result['[root]/' . $dir_name] = $arrDir;
        }

        /* section: handling all types of redirects */
        if (count($this->settings->get_internal_redirects()) > 0
                || count($this->settings->get_temporary_redirects()) > 0
                || count($this->settings->get_permanent_redirects()) > 0
                || count($this->settings->get_domain_redirects()) > 0) {
            $rootConfig['content'] .= '<IfModule mod_rewrite.c>' . PHP_EOL;
            $rootConfig['content'] .= 'RewriteEngine on' . PHP_EOL;
            //handling internal redirects
            foreach ($this->settings->get_internal_redirects() as $from => $to) {
                if (is_string($to)) {
                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . PHP_EOL;
                } elseif (get_class($to) == 'suite6\Tackler\TacklerRule') {
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
                                        $all_conditions.= 'OR';
                                        break;
                                    case 'FILE':
                                        $all_conditions.= '-f';
                                        break;
                                    case 'DIR':
                                        $all_conditions.= '-d';
                                        break;
                                    case 'NOT_FILE':
                                        $all_conditions.= '!-f';
                                        break;
                                    case 'NOT_DIR':
                                        $all_conditions.= '!-d';
                                        break;
                                    case 'NOT_CASE':
                                        $all_conditions.= 'NC';
                                        break;
                                    default:
                                        break;
                                }
                            }
                            if ($all_conditions == '')
                                $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . PHP_EOL;
                            else
                                $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' [' . $all_conditions . ']' . PHP_EOL;
                        } else {
                            switch ($rule[$condition_counter]->get_condition_combine()) {
                                case 'OR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' [OR]' . PHP_EOL;
                                    break;
                                case 'FILE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' -f' . PHP_EOL;
                                    break;
                                case 'DIR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' -d' . PHP_EOL;
                                    break;
                                case 'NOT_FILE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' !-f' . PHP_EOL;
                                    break;
                                case 'NOT_DIR':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' !-d' . PHP_EOL;
                                    break;
                                case 'NOT_CASE':
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . ' [NC]' . PHP_EOL;
                                    break;
                                default:
                                    $rootConfig['content'] .= self::rewrite_condition . ' ' . \suite6\Tackler\TacklerVariable::get_variable($rule[$condition_counter]->get_match_value()) . ' ' . $rule[$condition_counter]->get_match_pattern() . PHP_EOL;
                            }
                        }
                    }
                    $rootConfig['content'] .= self::rewrite_rule . ' ' . $to->get_match_pattern() . ' ' . $to->get_action_pattern() . PHP_EOL;
                }
            }

            //handling temporary redirects 302
            foreach ($this->settings->get_temporary_redirects() as $from => $to) {
                $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' [R=302]' . PHP_EOL;
            }

            //handling permenent redirects 301
            foreach ($this->settings->get_permanent_redirects() as $from => $to) {
                $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' [R=301]' . PHP_EOL;
            }

            //handling domain redirects 301
            foreach ($this->settings->get_domain_redirects() as $from => $to) {
                $rootConfig['content'] .= self::rewrite_rule . ' ' . $from . ' ' . $to . ' [R=301]' . PHP_EOL;
            }

            //handling gzip ifmodule
            if (count($this->settings->get_gzip_serve_extensions()) > 0 || count($this->settings->get_gzip_per_file()) > 0) {
                $rootConfig['content'] .= '<IfModule mod_headers.c>' . PHP_EOL;
                //handling gzip extensions
                if (count($this->settings->get_gzip_serve_extensions()) > 0) {
                    foreach ($this->settings->get_gzip_serve_extensions() as $ext => $content_type) {
                        $rootConfig['content'] .= 'RewriteCond %{HTTP:Accept-encoding} gzip' . PHP_EOL;
                        $rootConfig['content'] .= 'RewriteCond %{REQUEST_FILENAME}\.gz -s' . PHP_EOL;
                        $rootConfig['content'] .= 'RewriteRule ^(.*)\\' . $ext . ' $1\\' . $ext . '\.gz [QSA]' . PHP_EOL;
                    }
                    $file_match_ext = '';
                    foreach ($this->settings->get_gzip_serve_extensions() as $ext => $content_type) {
                        if ($file_match_ext !== '')
                            $file_match_ext.='|';
                        $file_match_ext .='\\' . $ext . '\.gz';
                        $rootConfig['content'] .= 'RewriteRule \\' . $ext . '\.gz$ - [T=' . $content_type . ',E=no-gzip:1]' . PHP_EOL;
                    }
                    $rootConfig['content'] .= '<FilesMatch "(' . $file_match_ext . ')$">' . PHP_EOL;
                    $rootConfig['content'] .= 'Header set Content-Encoding gzip' . PHP_EOL;
                    $rootConfig['content'] .= 'Header append Vary Accept-Encoding' . PHP_EOL;
                    $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL;
                }

                //handling per file gzip extensions
                if (count($this->settings->get_gzip_per_file()) > 0) {
                    foreach ($this->settings->get_gzip_per_file() as $ext => $content_type) {
                        $rootConfig['content'] .= '<FilesMatch "\\' . $ext . '\.gz$">' . PHP_EOL;
                        $rootConfig['content'] .= 'ForceType ' . $content_type . PHP_EOL;
                        $rootConfig['content'] .= 'Header set Content-Encoding gzip' . PHP_EOL;
                        $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL;
                    }
                }
                $rootConfig['content'] .= '</IfModule>' . PHP_EOL;
            }

            $rootConfig['content'] .= '</IfModule>' . PHP_EOL;
            /* end section: handling all types of redirects */
        } //endif redirect section
        //handling listable directories
        foreach ($this->settings->get_listable_directories() as $value) {
            $dir_name = rtrim(ltrim($value, '/'), '/');
            if (array_key_exists('[root]/' . $dir_name, $result)) {
                $result['[root]/' . $dir_name]['content'] .= 'Options ' . self::allow_list . PHP_EOL;
            } else {
                $arrDir = array();
                $arrDir['name'] = '\'' . '[root]/' . $dir_name . '/' . self::config_file_name . '\'';
                $arrDir['content'] = 'Options ' . self::allow_list . PHP_EOL;
                $result['[root]/' . $dir_name] = $arrDir;
            }
        }

        //setting default directory handler/document root
        if ($this->settings->get_default_directory_handler() !== null && $this->settings->get_default_directory_handler() != '') {
            $rootConfig['content'] .= 'DirectoryIndex ' . $this->settings->get_default_directory_handler() . PHP_EOL;
        }

        //setting default 404
        if ($this->settings->get_default_404_handler() !== null && $this->settings->get_default_404_handler() != '') {
            $rootConfig['content'] .= 'ErrorDocument 404 ' . $this->settings->get_default_404_handler() . PHP_EOL;
        }

        //setting default 403
        if ($this->settings->get_default_403_handler() !== null && $this->settings->get_default_403_handler() != '') {
            $rootConfig['content'] .= 'ErrorDocument 403 ' . $this->settings->get_default_403_handler() . PHP_EOL;
        }

        //handling php configs
        if (count($this->settings->get_php_configs()) > 0 || count($this->settings->get_php_flags()) > 0) {
            $rootConfig['content'] .= '<IfModule mod_php5.c>' . PHP_EOL;
            //handling php values
            foreach ($this->settings->get_php_configs() as $key => $value) {
                $rootConfig['content'] .= 'php_value ' . $key . ' ' . $value . PHP_EOL;
            }
            //handling php flags
            foreach ($this->settings->get_php_flags() as $key => $value) {
                $rootConfig['content'] .= 'php_flag ' . $key . ' ' . $value . PHP_EOL;
            }
            $rootConfig['content'] .= '</IfModule>' . PHP_EOL;
        }

        //handling cache
        if ($this->settings->get_time_to_expiration() != '') {
            $rootConfig['content'] .= '<IfModule mod_expires.c>' . PHP_EOL;
            $rootConfig['content'] .= 'ExpiresActive On' . PHP_EOL;
            $rootConfig['content'] .= 'ExpiresDefault ' . $this->settings->get_time_to_expiration() . PHP_EOL;
            if (!$this->settings->get_cache_php_script()) {
                $rootConfig['content'] .= '<FilesMatch \.php$>' . PHP_EOL;
                $rootConfig['content'] .= 'ExpiresActive Off' . PHP_EOL;
                $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL;
            }
            $rootConfig['content'] .= '</IfModule>' . PHP_EOL;
        }

        //handling etag
        if ($this->settings->get_etag() !== null) {
            if ($this->settings->get_etag()->get_stat() == \suite6\Tackler\TacklerConfiguration::flag_on) {
                $rootConfig['content'] .= 'FileETag' . $this->concateFlags($this->settings->get_etag()->get_flags()) . PHP_EOL;
            } else {
                //if etag stat is off
                $rootConfig['content'] .= 'FileETag None' . PHP_EOL;
            }
        }

        //handling etag files
        foreach ($this->settings->get_etag_files() as $file => $etag) {
            $rootConfig['content'] .= '<FilesMatch "' . $file . '">' . PHP_EOL;
            if ($etag === null) {
                $etag_default = new \suite6\Tackler\TacklerEtag();
                $rootConfig['content'] .= 'FileETag ' . $this->concateFlags($etag_default->get_flags()) . PHP_EOL;
            }
            else
                $rootConfig['content'] .= 'FileETag ' . $this->concateFlags($etag->get_flags()) . PHP_EOL;

            $rootConfig['content'] .= '</FilesMatch>' . PHP_EOL . PHP_EOL;
        }

        $rootConfig['content'] .= '<IfModule mod_headers.c>' . PHP_EOL;
        //setting default directory handler/document root
        if ($this->settings->get_last_modified() !== null && $this->settings->get_last_modified() != '') {
            $rootConfig['content'] .= 'Header Set Last-Modified "' . $this->settings->get_last_modified() . '"' . PHP_EOL;
        }
        //setting default directory handler/document root
        if ($this->settings->get_cache_control() !== null && $this->settings->get_cache_control() != '') {
            $rootConfig['content'] .= 'Header set Cache-Control "' . $this->settings->get_cache_control() . '"' . PHP_EOL;
        }
        $rootConfig['content'] .= '</IfModule>' . PHP_EOL;
        $result['[root]'] = $rootConfig;

        return $result;
    }

    private function concateFlags($flags) {
        $flags_str = '';
        foreach ($flags as $flag) {
            switch ($flag) {
                case \suite6\Tackler\TacklerEtag::etag_all:
                    $flags_str.=' All';
                    break;
                case \suite6\Tackler\TacklerEtag::etag_inode:
                    $flags_str.=' INode';
                    break;
                case \suite6\Tackler\TacklerEtag::etag_mtime:
                    $flags_str.=' MTime';
                    break;
                case \suite6\Tackler\TacklerEtag::etag_size:
                    $flags_str.=' Size';
                    break;
                default:
                    break;
            }
        }
        return $flags_str;
    }

    public function generate_configs_file(){
        $filename = '';
        $content = '';
        $result = array();
        $configs = $this->generate_configs();
        
        if (count($configs) > 1) {
            //create the zip
            $zip = new \Zip();
            foreach ($configs as $key => $config) {
                if ($key == '[root]')
                    $filename = str_replace("'", '', str_replace('[root]', '', $config['name']));
                else
                    $filename = substr(str_replace("'", '', str_replace('[root]', '', $config['name'])), 1);

                $filecontents = $config['content'];
                //add files to the zip, passing file contents, not actual files
                $zip->addFile($filecontents, $filename);
            }
            $download_filename = 'apache.htaccess.zip';
            //prepare the proper content type
            $ctype = "Content-type: application/octet-stream";
            //get the zip content and send it back to the browser
            //$zip->closeStream();
            $content = $zip->getZipData();
        } else {
            $download_filename = $configs['[root]']['name'];
            $content = $configs['[root]']['content'];
            
            $ctype = 'content-type: text/plain';
        }
        
        $result['content'] = $content;
        $result['mime-type'] = $ctype;
        $result['file-name'] = $download_filename;
        return $result;
    }
}