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

class TackleGeneratorIIS7 {

    private $settings;

    const config_file_name = 'web.config';
    const user_config_file = '.user.ini';

    public function __construct(\suite6\Tackle\TackleConfiguration $settings) {
        $this->settings = $settings;
    }

    public function generate_configs() {
        $result = array();
        $rootConfig = array();
        $result['[root]'] = array();

        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);

        $xmlWriter->startDocument('1.0', 'utf8');
        $xmlWriter->startElement('configuration');

        //all root directory setting must be written within this tag
        $xmlWriter->startElement('system.webServer');

        //setting default directory handler/document root
        if ($this->settings->get_default_directory_handler() !== null && $this->settings->get_default_directory_handler() != '') {
            $xmlWriter->startElement('defaultDocument');
            $xmlWriter->startElement('files');
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('value', $this->settings->get_default_directory_handler());
            $xmlWriter->endElement(); //end add
            $xmlWriter->endElement(); //end files
            $xmlWriter->endElement(); //end defaultDocument
        }

        $xmlWriter->startElement('security');

        $xmlWriter->startElement('authorization');
        //set default policy configuration
        if ($this->settings->get_default_policy() == 'ALLOW') {
            /* $xmlWriter->writeElement('clear');
              $xmlWriter->startElement('add');
              $xmlWriter->writeAttribute('users', '*');
              $xmlWriter->endElement(); // end allow
             */
        } else {
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('users', '*');
            $xmlWriter->endElement(); // end deny
        }
        $xmlWriter->endElement(); // end authorization

        $xmlWriter->startElement('ipSecurity');
        $xmlWriter->writeAttribute('allowUnlisted', 'true');
        $xmlWriter->writeElement('clear');
        //set banned ips
        foreach ($this->settings->get_banned_ips() as $value) {
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('ipAddress', $value);
            $xmlWriter->endElement(); //end add
        }
        $xmlWriter->endElement(); //end ipSecurity

        $xmlWriter->endElement(); //end security

        $xmlWriter->startElement('directoryBrowse');
        //set default directory listing policy configuration
        if ($this->settings->get_default_directory_listing_policy() == 'ALLOW') {
            $xmlWriter->writeAttribute('enabled', 'true');
        } else {
            $xmlWriter->writeAttribute('enabled', 'false');
        }
        $xmlWriter->endElement(); // end directoryBrowse
        //setting up custom error pages
        $xmlWriter->startElement('httpErrors');
        $xmlWriter->writeAttribute('errorMode', 'Custom');
        //setting default 404
        if ($this->settings->get_default_404_handler() !== null && $this->settings->get_default_404_handler() != '') {
            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('statusCode', '404');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->startElement('error');
            $xmlWriter->writeAttribute('statusCode', '404');
            $xmlWriter->writeAttribute('path', $this->settings->get_default_404_handler());
            $xmlWriter->writeAttribute('responseMode', 'ExecuteURL');
            $xmlWriter->endElement(); //end error
        }

        //setting default 403 in IIS7 it also throws 401 for access denied
        if ($this->settings->get_default_403_handler() !== null && $this->settings->get_default_403_handler() != '') {
            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('statusCode', '401');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->startElement('error');
            $xmlWriter->writeAttribute('statusCode', '401');
            $xmlWriter->writeAttribute('path', $this->settings->get_default_403_handler());
            $xmlWriter->writeAttribute('responseMode', 'ExecuteURL');
            $xmlWriter->endElement(); //end error

            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('statusCode', '403');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->startElement('error');
            $xmlWriter->writeAttribute('statusCode', '403');
            $xmlWriter->writeAttribute('path', $this->settings->get_default_403_handler());
            $xmlWriter->writeAttribute('responseMode', 'ExecuteURL');
            $xmlWriter->endElement(); //end error
        }
        $xmlWriter->endElement(); //end httpErrors

        /* section: handling all types of redirects */
        $xmlWriter->startElement('rewrite');
        $xmlWriter->startElement('rules');
        $ruleCounter = 1;
        //handling internal redirects
        foreach ($this->settings->get_internal_redirects() as $from => $to) {
            if (is_string($to)) {
                $xmlWriter->startElement('rule');
                $xmlWriter->writeAttribute('name', 'AutoGeneratedRule_' . $ruleCounter++);
                $xmlWriter->writeAttribute('stopProcessing', 'true');
                $xmlWriter->startElement('match');
                $xmlWriter->writeAttribute('url', $from);
                $xmlWriter->endElement(); //end match
                $xmlWriter->startElement('action');
                $xmlWriter->writeAttribute('type', 'Rewrite');
                $xmlWriter->writeAttribute('url', $to);
                $xmlWriter->endElement(); //end action
                $xmlWriter->endElement(); //end rule
            } elseif (get_class($to) == 'suite6\Tackle\TackleRule') {
                $rule = $to->get_rule_condition();
                $xmlWriter->startElement('rule');
                $xmlWriter->writeAttribute('name', 'AutoGeneratedRule_' . $ruleCounter++);
                $xmlWriter->writeAttribute('stopProcessing', 'true');
                $xmlWriter->startElement('match');
                $xmlWriter->writeAttribute('url', $to->get_match_pattern());
                $xmlWriter->endElement(); //end match
                $xmlWriter->startElement('action');
                $xmlWriter->writeAttribute('type', 'Rewrite');
                $xmlWriter->writeAttribute('url', $to->get_action_pattern());
                $xmlWriter->endElement(); //end action
                $xmlWriter->startElement('conditions');
                //find if there is any OR condition in all conditions
                //if found then change logicalgrouping to MatchAny
                for ($condition_counter = 0; $condition_counter < count($rule); $condition_counter++) {
                    if (is_array($rule[$condition_counter]->get_condition_combine())) {
                        if (in_array('OR', $rule[$condition_counter]->get_condition_combine())) {
                            $xmlWriter->writeAttribute('logicalGrouping', 'MatchAny');
                            break;
                        }
                    } else {
                        if ($rule[$condition_counter]->get_condition_combine() == 'OR') {
                            $xmlWriter->writeAttribute('logicalGrouping', 'MatchAny');
                            break;
                        }
                    }
                }
                for ($condition_counter = 0; $condition_counter < count($rule); $condition_counter++) {
                    //if multiple flags are provided
                    if (is_array($rule[$condition_counter]->get_condition_combine())) {
                        $all_conditions = '';
                        $xmlWriter->startElement('add');
                        $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                        $ignore_case = 'false';
                        foreach ($rule[$condition_counter]->get_condition_combine() as $condition) {
                            switch ($condition) {
                                case 'OR':
                                    //or is being treated as whole in iis7
                                    break;
                                case 'FILE':
                                    $xmlWriter->writeAttribute('matchType', 'IsFile');
                                    break;
                                case 'DIR':
                                    $xmlWriter->writeAttribute('matchType', 'IsDirectory');
                                    break;
                                case 'NOT_FILE':
                                    $xmlWriter->writeAttribute('matchType', 'IsFile');
                                    $xmlWriter->writeAttribute('negate', 'true');
                                    break;
                                case 'NOT_DIR':
                                    $xmlWriter->writeAttribute('matchType', 'IsDirectory');
                                    $xmlWriter->writeAttribute('negate', 'true');
                                    break;
                                case 'NOT_CASE':
                                    $ignore_case = 'true';
                                    break;
                                default:
                                    break;
                            }
                        }
                        if ($rule[$condition_counter]->get_match_pattern() != '') {
                            $xmlWriter->writeAttribute('pattern', $rule[$condition_counter]->get_match_pattern());
                            $xmlWriter->writeAttribute('ignoreCase', $ignore_case);
                        }
                        $xmlWriter->endElement(); //end add
                    } else {
                        switch ($rule[$condition_counter]->get_condition_combine()) {
                            case 'OR':
                                //or is being treated as whole in iis7
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('pattern', $rule[$condition_counter]->get_match_pattern());
                                $xmlWriter->writeAttribute('ignoreCase', 'false');
                                $xmlWriter->endElement(); //end add
                                break;
                            case 'FILE':
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('matchType', 'IsFile');
                                $xmlWriter->endElement(); //end add
                                break;
                            case 'DIR':
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('matchType', 'IsDirectory');
                                $xmlWriter->endElement(); //end add
                                break;
                            case 'NOT_FILE':
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('matchType', 'IsFile');
                                $xmlWriter->writeAttribute('negate', 'true');
                                $xmlWriter->endElement(); //end add
                                break;
                            case 'NOT_DIR':
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('matchType', 'IsDirectory');
                                $xmlWriter->writeAttribute('negate', 'true');
                                $xmlWriter->endElement(); //end add
                                break;
                            case 'NOT_CASE':
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('pattern', $rule[$condition_counter]->get_match_pattern());
                                $xmlWriter->writeAttribute('ignoreCase', 'true');
                                $xmlWriter->endElement(); //end add
                                break;
                            default:
                                $xmlWriter->startElement('add');
                                $xmlWriter->writeAttribute('input', \suite6\Tackle\TackleVariable::get_variable($rule[$condition_counter]->get_match_value(), \suite6\Tackle\TackleConfiguration::server_iis7));
                                $xmlWriter->writeAttribute('pattern', $rule[$condition_counter]->get_match_pattern());
                                $xmlWriter->writeAttribute('ignoreCase', 'false');
                                $xmlWriter->endElement(); //end add
                        }
                    }
                }
                $xmlWriter->endElement(); //end conditions
                $xmlWriter->endElement(); //end rule
            }
        }

        //handling temporary redirects 302
        foreach ($this->settings->get_temporary_redirects() as $from => $to) {
            $xmlWriter->startElement('rule');
            $xmlWriter->writeAttribute('name', 'AutoGeneratedRule_' . $ruleCounter++);
            $xmlWriter->writeAttribute('stopProcessing', 'true');
            $xmlWriter->startElement('match');
            $xmlWriter->writeAttribute('url', $from);
            $xmlWriter->endElement(); //end match
            $xmlWriter->startElement('action');
            $xmlWriter->writeAttribute('type', 'Redirect');
            $xmlWriter->writeAttribute('url', $to);
            $xmlWriter->writeAttribute('redirectType', 'Found');
            $xmlWriter->endElement(); //end action
            $xmlWriter->endElement(); //end rule
        }

        //handling permenent redirects 301
        foreach ($this->settings->get_permanent_redirects() as $from => $to) {
            $xmlWriter->startElement('rule');
            $xmlWriter->writeAttribute('name', 'AutoGeneratedRule_' . $ruleCounter++);
            $xmlWriter->writeAttribute('stopProcessing', 'true');
            $xmlWriter->startElement('match');
            $xmlWriter->writeAttribute('url', $from);
            $xmlWriter->endElement(); //end match
            $xmlWriter->startElement('action');
            $xmlWriter->writeAttribute('type', 'Redirect');
            $xmlWriter->writeAttribute('url', $to);
            $xmlWriter->writeAttribute('redirectType', 'Permanent');
            $xmlWriter->endElement(); //end action
            $xmlWriter->endElement(); //end rule
        }

        //handling domain redirects 301
        foreach ($this->settings->get_domain_redirects() as $from => $to) {
            $xmlWriter->startElement('rule');
            $xmlWriter->writeAttribute('name', 'AutoGeneratedRule_' . $ruleCounter++);
            $xmlWriter->writeAttribute('stopProcessing', 'true');
            $xmlWriter->startElement('match');
            $xmlWriter->writeAttribute('url', $from);
            $xmlWriter->endElement(); //end match
            $xmlWriter->startElement('action');
            $xmlWriter->writeAttribute('type', 'Redirect');
            $xmlWriter->writeAttribute('url', $to);
            $xmlWriter->writeAttribute('redirectType', 'Permanent');
            $xmlWriter->endElement(); //end action
            $xmlWriter->endElement(); //end rule
        }

        $xmlWriter->endElement(); //end rules
        //handling etag, etag are on by default and no other way to turn off
        //except the following tweak using an outbond rule to rewrite response
        if ($this->settings->get_etag() !== null) {
            if ($this->settings->get_etag()->get_stat() == \suite6\Tackle\TackleConfiguration::flag_off) {
                $xmlWriter->startElement('outboundRules');
                $xmlWriter->startElement('rule');
                $xmlWriter->writeAttribute('name', 'rule_to_remove_etag');
                $xmlWriter->startElement('match');
                $xmlWriter->writeAttribute('serverVariable', 'RESPONSE_ETag');
                $xmlWriter->writeAttribute('pattern', '.+');
                $xmlWriter->endElement(); //end match
                $xmlWriter->startElement('action');
                $xmlWriter->writeAttribute('type', 'Rewrite');
                $xmlWriter->endElement(); //end action
                $xmlWriter->endElement(); //end rule
                $xmlWriter->endElement(); //end outboundRules
            }
        }
        $xmlWriter->endElement(); //end rewrite
        /* end section: handling all types of redirects */

        //handling cache
        if ($this->settings->get_time_to_expiration() != '') {
            $xmlWriter->startElement('caching');
            $xmlWriter->startElement('profiles');
            //you can add multiple extension as per your requirement
            //add support for .html
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('extension', '.html');
            $xmlWriter->writeAttribute('policy', 'CacheForTimePeriod');
            $xmlWriter->writeAttribute('kernelCachePolicy', 'DontCache');
            $xmlWriter->writeAttribute('duration', $this->settings->get_time_to_expiration());
            $xmlWriter->endElement(); //end add
            //add support for .css
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('extension', '.css');
            $xmlWriter->writeAttribute('policy', 'CacheForTimePeriod');
            $xmlWriter->writeAttribute('kernelCachePolicy', 'DontCache');
            $xmlWriter->writeAttribute('duration', $this->settings->get_time_to_expiration());
            $xmlWriter->endElement(); //end add
            //disable cache for php script
            if (!$this->settings->get_cache_php_script()) {
                $xmlWriter->startElement('add');
                $xmlWriter->writeAttribute('extension', '.php');
                $xmlWriter->writeAttribute('policy', 'DisableCache');
                $xmlWriter->writeAttribute('kernelCachePolicy', 'DontCache');
                $xmlWriter->endElement(); //end add
            } else {
                $xmlWriter->startElement('add');
                $xmlWriter->writeAttribute('extension', '.php');
                $xmlWriter->writeAttribute('policy', 'CacheForTimePeriod');
                $xmlWriter->writeAttribute('kernelCachePolicy', 'DontCache');
                $xmlWriter->writeAttribute('duration', $this->settings->get_time_to_expiration());
                $xmlWriter->endElement(); //end add
            }
            $xmlWriter->endElement(); //end profiles
            $xmlWriter->endElement(); //end caching
        }

        //handling gzip extensions
        if (count($this->settings->get_gzip_serve_extensions()) > 0 || count($this->settings->get_gzip_per_file()) > 0) {
            $xmlWriter->startElement('urlCompression');
            $xmlWriter->writeAttribute('doStaticCompression', 'true');
            $xmlWriter->endElement(); //end urlCompression
        }

        //setting default directory handler/document root
        if ($this->settings->get_last_modified() !== null && $this->settings->get_last_modified() != '') {
            $xmlWriter->startElement('httpProtocol');
            $xmlWriter->startElement('customHeaders');
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('name', 'Last-Modified');
            $xmlWriter->writeAttribute('value', $this->settings->get_last_modified());
            $xmlWriter->endElement(); //end add
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('name', 'Cache-Control');
            $xmlWriter->writeAttribute('value', $this->settings->get_cache_control());
            $xmlWriter->endElement(); //end add
            $xmlWriter->endElement(); //end customHeaders
            $xmlWriter->endElement(); //end httpProtocol
        }

        $xmlWriter->endElement(); //end system.webServer
        //set denied files list 403
        foreach ($this->settings->get_denied_files() as $value) {
            $xmlWriter->startElement('location');
            $xmlWriter->writeAttribute('path', $value);
            $xmlWriter->startElement('system.webServer');
            $xmlWriter->startElement('security');
            $xmlWriter->startElement('authorization');
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('users', '*');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->endElement(); //end authorization
            $xmlWriter->endElement(); //end security
            $xmlWriter->endElement(); //end system.webServer
            $xmlWriter->endElement(); //end location
        }

        //set allowed files list 
        foreach ($this->settings->get_allowed_files() as $value) {
            $xmlWriter->startElement('location');
            $xmlWriter->writeAttribute('path', $value);
            $xmlWriter->startElement('system.webServer');
            $xmlWriter->startElement('security');
            $xmlWriter->startElement('authorization');
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('accessType', 'Allow');
            $xmlWriter->writeAttribute('users', '*');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->endElement(); //end authorization
            $xmlWriter->endElement(); //end security
            $xmlWriter->endElement(); //end system.webServer
            $xmlWriter->endElement(); //end location
        }

        //set denied directories list 403
        foreach ($this->settings->get_denied_directories() as $value) {
            $xmlWriter->startElement('location');
            $xmlWriter->writeAttribute('path', $value);
            $xmlWriter->startElement('system.webServer');
            $xmlWriter->startElement('security');
            $xmlWriter->startElement('authorization');
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('remove');
            $xmlWriter->writeAttribute('users', '*');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->endElement(); //end authorization
            $xmlWriter->endElement(); //end security
            $xmlWriter->endElement(); //end system.webServer
            $xmlWriter->endElement(); //end location
        }

        //set allowed directories list 
        foreach ($this->settings->get_allowed_directories() as $value) {
            $xmlWriter->startElement('location');
            $xmlWriter->writeAttribute('path', $value);
            $xmlWriter->startElement('system.webServer');
            $xmlWriter->startElement('security');
            $xmlWriter->startElement('authorization');
            $xmlWriter->writeElement('clear');
            $xmlWriter->startElement('add');
            $xmlWriter->writeAttribute('accessType', 'Allow');
            $xmlWriter->writeAttribute('users', '*');
            $xmlWriter->endElement(); //end remove
            $xmlWriter->endElement(); //end authorization
            $xmlWriter->endElement(); //end security
            $xmlWriter->endElement(); //end system.webServer
            $xmlWriter->endElement(); //end location
        }

        //handling listable directories
        foreach ($this->settings->get_listable_directories() as $value) {
            $xmlWriter->startElement('location');
            $xmlWriter->writeAttribute('path', $value);
            $xmlWriter->startElement('system.webServer');
            $xmlWriter->startElement('directoryBrowse');
            $xmlWriter->writeAttribute('enabled', 'true');
            $xmlWriter->endElement(); //end directoryBrowse
            $xmlWriter->endElement(); //end system.webServer
            $xmlWriter->endElement(); //end location
        }

        $xmlWriter->endElement(); // end configuration
        $xmlWriter->endDocument();

        //defining file name
        $rootConfig['name'] = '[root]' . self::config_file_name;
        $rootConfig['content'] = $xmlWriter->outputMemory();

        //handling php configs
        if (count($this->settings->get_php_configs()) > 0 || count($this->settings->get_php_flags()) > 0) {
            $arrUser = array();
            $arrUser['name'] = '\'' . '[root]/' . self::user_config_file . '\'';
            $arrUser['content'] = '';
            //handling php values
            foreach ($this->settings->get_php_configs() as $key => $value) {
                $arrUser['content'] .= $key . ' = ' . $value . PHP_EOL;
            }
            //handling php flags
            foreach ($this->settings->get_php_flags() as $key => $value) {
                $arrUser['content'] .= $key . ' = ' . $value . PHP_EOL;
            }
            $result['[root]/' . self::user_config_file] = $arrUser;
        }

        $result['[root]'] = $rootConfig;

        return $result;
    }

}
