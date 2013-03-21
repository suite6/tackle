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

namespace suite6\Tackle\Communicator;

class TackleImporter {

    private $xml;

    public function __construct($xml) {
        $this->xml = $xml;
    }

    public function __get($name) {
        if (method_exists($this, ($method = 'get_' . $name))) {
            return $this->$method();
        }
        else
            return;
    }

    public function __isset($name) {
        if (method_exists($this, ($method = 'isset_' . $name))) {
            return $this->$method();
        }
        else
            return;
    }

    public function __set($name, $value) {
        if (method_exists($this, ($method = 'set_' . $name))) {
            $this->$method($value);
        }
    }

    public function __unset($name) {
        if (method_exists($this, ($method = 'unset_' . $name))) {
            $this->$method();
        }
    }

    public function get_xml_string() {
        return $this->xml;
    }

    /**
     * Returns the identified Role
     *
     * @throws Exception
     * @return suite6\Tackle\TackleConfiguration
     */
    public function get_configuration_object() {
        $config = new \suite6\Tackle\TackleConfiguration();

        $xmlReader = new \XMLReader();
        $xmlReader->XML($this->xml);
        
        //get xml version information
//        $xml_ver=null;
//        preg_match_all('/version=("|\')(?P<version>.+?)("|\')/', $this->xml, $xml_info);
//        if(isset($xml_info['version'])) $xml_ver = $xml_info['version'][0];
        
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLREADER::ELEMENT) {
                switch ($xmlReader->name) {
                    case 'meta':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $lib_ver=null;
                            $schema_ver=null;
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT) {
                                    if ($xmlNodeReader->name == 'LibraryVersion') {
                                        $lib_ver = $xmlNodeReader->readString();
                                    }
                                    if ($xmlNodeReader->name == 'SchemaVersion') {
                                        $schema_ver = $xmlNodeReader->readString();
                                    }
                                }
                            }
                            //if the Schema version is more recent than the 
                            //Library's default schema value
                            if(version_compare($schema_ver, $config->get_schema_version())==1){
                                throw new \Exception('Tackle: Provided Schema version '. $schema_ver .' is newer than the library\'s supported version '. $config->get_schema_version());
                            }
                            unset($xmlNodeReader);
                        }
                        break;
                    case 'DefaultPolicy':
                        $config->set_default_policy($xmlReader->readString());
                        break;
                    case 'DefaultDirectoryListingPolicy':
                        $config->set_default_directory_listing_policy($xmlReader->readString());
                        break;
                    case 'SymbolicLink':
                        $config->set_sym_link($xmlReader->readString());
                        break;
                    case 'DefaultDirectoryHandler':
                        $config->set_default_directory_handler($xmlReader->readString());
                        break;
                    case 'Default404Handler':
                        $config->set_default_404_handler($xmlReader->readString());
                        break;
                    case 'Default403Handler':
                        $config->set_default_403_handler($xmlReader->readString());
                        break;
                    case 'TimeToExpiration':
                        $config->set_time_to_expiration($xmlReader->readString());
                        break;
                    case 'CachePHPScript':
                        if ($xmlReader->readString() !== '')
                            $config->set_cache_php_script($xmlReader->readString());
                        break;
                    case 'BannedIps':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'IP') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_banned_ips($list);
                        }
                        break;
                    case 'DeniedFile':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'File') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_denied_files($list);
                        }
                        break;
                    case 'AllowedFile':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'File') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_allowed_files($list);
                        }
                        break;
                    case 'DeniedDirectory':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'Directory') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_denied_directories($list);
                        }
                        break;
                    case 'AllowedDirectory':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'Directory') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_allowed_directories($list);
                        }
                        break;
                    case 'InternalRedirect':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Redirect') {
                                    $xmlRedirectNodeReader = new \XMLReader();
                                    $xmlRedirectNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $pattern = null;
                                    $action = null;
                                    $rule = null;
                                    while ($xmlRedirectNodeReader->read()) {
                                        if ($xmlRedirectNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlRedirectNodeReader->name != 'Redirect') {
                                            if ($xmlRedirectNodeReader->name == 'Pattern') {
                                                $pattern = $xmlRedirectNodeReader->readString();
                                            }
                                            if ($xmlRedirectNodeReader->name == 'Action') {
                                                $action = $xmlRedirectNodeReader->readString();
                                            }
                                            if ($xmlRedirectNodeReader->name == 'Conditions') {
                                                if (!$xmlRedirectNodeReader->isEmptyElement) {
                                                    $xmlConditionNodeReader = new \XMLReader();
                                                    $xmlConditionNodeReader->XML($xmlRedirectNodeReader->readOuterXml());
                                                    $condition_list = array();
                                                    while ($xmlConditionNodeReader->read()) {
                                                        if ($xmlConditionNodeReader->name == 'Expression') {
                                                            $xmlExprNodeReader = new \XMLReader();
                                                            $xmlExprNodeReader->XML($xmlConditionNodeReader->readOuterXml());
                                                            $condition_comparison = null;
                                                            $condition_pattern = null;
                                                            $condition_flags = null;
                                                            while ($xmlExprNodeReader->read()) {
                                                                if ($xmlExprNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlExprNodeReader->name != 'Expression') {
                                                                    if ($xmlExprNodeReader->name == 'Comparison') {
                                                                        $condition_flags = $xmlExprNodeReader->getAttribute('Flags');
                                                                        $condition_comparison = $xmlExprNodeReader->readString();
                                                                    }
                                                                    if ($xmlExprNodeReader->name == 'Value') {
                                                                        $condition_pattern = $xmlExprNodeReader->readString();
                                                                    }
                                                                }
                                                            }
                                                            if ($condition_comparison !== null && $condition_pattern !== null) {
                                                                $condition_list[] = new \suite6\Tackle\TackleCondition($condition_comparison, $condition_pattern, explode(',', $condition_flags));
                                                            }
                                                        }
                                                    }
                                                }
                                                $rule = new \suite6\Tackle\TackleRule();
                                                $rule->set_match_pattern($pattern);
                                                $rule->set_action_pattern($action);
                                                $rule->set_rule_condition($condition_list);
                                            }
                                        }
                                    }
                                    if ($pattern !== null && $action !== null && $rule == null)
                                        $list[$pattern] = $action;
                                    elseif ($pattern !== null && $action !== null && $rule !== null)
                                        $list[] = $rule;
                                }
                            }
                            unset($xmlRedirectNodeReader);
                            unset($xmlNodeReader);
                            $config->set_internal_redirects($list);
                        }
                        break;
                    case 'TemporaryRedirect':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Redirect') {
                                    $xmlRedirectNodeReader = new \XMLReader();
                                    $xmlRedirectNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $pattern = null;
                                    $action = null;
                                    while ($xmlRedirectNodeReader->read()) {
                                        if ($xmlRedirectNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlRedirectNodeReader->name != 'Redirect') {
                                            if ($xmlRedirectNodeReader->name == 'Pattern') {
                                                $pattern = $xmlRedirectNodeReader->readString();
                                            }
                                            if ($xmlRedirectNodeReader->name == 'Action') {
                                                $action = $xmlRedirectNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($pattern !== null && $action !== null)
                                        $list[$pattern] = $action;
                                }
                            }
                            unset($xmlRedirectNodeReader);
                            unset($xmlNodeReader);
                            $config->set_temporary_redirects($list);
                        }
                        break;
                    case 'PermanentRedirect':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Redirect') {
                                    $xmlRedirectNodeReader = new \XMLReader();
                                    $xmlRedirectNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $pattern = null;
                                    $action = null;
                                    while ($xmlRedirectNodeReader->read()) {
                                        if ($xmlRedirectNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlRedirectNodeReader->name != 'Redirect') {
                                            if ($xmlRedirectNodeReader->name == 'Pattern') {
                                                $pattern = $xmlRedirectNodeReader->readString();
                                            }
                                            if ($xmlRedirectNodeReader->name == 'Action') {
                                                $action = $xmlRedirectNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($pattern !== null && $action !== null)
                                        $list[$pattern] = $action;
                                }
                            }
                            unset($xmlRedirectNodeReader);
                            unset($xmlNodeReader);
                            $config->set_permanent_redirects($list);
                        }
                        break;
                    case 'DomainRedirect':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Redirect') {
                                    $xmlRedirectNodeReader = new \XMLReader();
                                    $xmlRedirectNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $pattern = null;
                                    $action = null;
                                    while ($xmlRedirectNodeReader->read()) {
                                        if ($xmlRedirectNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlRedirectNodeReader->name != 'Redirect') {
                                            if ($xmlRedirectNodeReader->name == 'Pattern') {
                                                $pattern = $xmlRedirectNodeReader->readString();
                                            }
                                            if ($xmlRedirectNodeReader->name == 'Action') {
                                                $action = $xmlRedirectNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($pattern !== null && $action !== null)
                                        $list[$pattern] = $action;
                                }
                            }
                            unset($xmlRedirectNodeReader);
                            unset($xmlNodeReader);
                            $config->set_domain_redirects($list);
                        }
                        break;
                    case 'GzipCompression':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'File') {
                                    $xmlFileNodeReader = new \XMLReader();
                                    $xmlFileNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $ext = null;
                                    $ctype = null;
                                    while ($xmlFileNodeReader->read()) {
                                        if ($xmlFileNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlFileNodeReader->name != 'File') {
                                            if ($xmlFileNodeReader->name == 'Extension') {
                                                $ext = $xmlFileNodeReader->readString();
                                            }
                                            if ($xmlFileNodeReader->name == 'ContentType') {
                                                $ctype = $xmlFileNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($ext !== null && $ctype !== null)
                                        $list[$ext] = $ctype;
                                }
                            }
                            unset($xmlFileNodeReader);
                            unset($xmlNodeReader);
                            $config->set_gzip_serve_extensions($list);
                        }
                        break;
                    case 'GzipPerFile':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'File') {
                                    $xmlFileNodeReader = new \XMLReader();
                                    $xmlFileNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $ext = null;
                                    $ctype = null;
                                    while ($xmlFileNodeReader->read()) {
                                        if ($xmlFileNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlFileNodeReader->name != 'File') {
                                            if ($xmlFileNodeReader->name == 'Extension') {
                                                $ext = $xmlFileNodeReader->readString();
                                            }
                                            if ($xmlFileNodeReader->name == 'ContentType') {
                                                $ctype = $xmlFileNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($ext !== null && $ctype !== null)
                                        $list[$ext] = $ctype;
                                }
                            }
                            unset($xmlFileNodeReader);
                            unset($xmlNodeReader);
                            $config->set_gzip_per_file($list);
                        }
                        break;
                    case 'ListableDirectories':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'Directory') {
                                    $list[] = $xmlNodeReader->readString();
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_listable_directories($list);
                        }
                        break;
                    case 'PHPConfig':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Configuration') {
                                    $xmlConfigNodeReader = new \XMLReader();
                                    $xmlConfigNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $ext = null;
                                    $ctype = null;
                                    while ($xmlConfigNodeReader->read()) {
                                        if ($xmlConfigNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlConfigNodeReader->name != 'File') {
                                            if ($xmlConfigNodeReader->name == 'Property') {
                                                $ext = $xmlConfigNodeReader->readString();
                                            }
                                            if ($xmlConfigNodeReader->name == 'Value') {
                                                $ctype = $xmlConfigNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($ext !== null && $ctype !== null)
                                        $list[$ext] = $ctype;
                                }
                            }
                            unset($xmlConfigNodeReader);
                            unset($xmlNodeReader);
                            $config->set_php_configs($list);
                        }
                        break;
                    case 'PHPFlag':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->name == 'Configuration') {
                                    $xmlConfigNodeReader = new \XMLReader();
                                    $xmlConfigNodeReader->XML($xmlNodeReader->readOuterXml());
                                    $ext = null;
                                    $ctype = null;
                                    while ($xmlConfigNodeReader->read()) {
                                        if ($xmlConfigNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlConfigNodeReader->name != 'File') {
                                            if ($xmlConfigNodeReader->name == 'Flag') {
                                                $ext = $xmlConfigNodeReader->readString();
                                            }
                                            if ($xmlConfigNodeReader->name == 'Value') {
                                                $ctype = $xmlConfigNodeReader->readString();
                                            }
                                        }
                                    }
                                    if ($ext !== null && $ctype !== null)
                                        $list[$ext] = $ctype;
                                }
                            }
                            unset($xmlConfigNodeReader);
                            unset($xmlNodeReader);
                            $config->set_php_flags($list);
                        }
                        break;
                    case 'ETag':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'Stat') {
                                    $stat = $xmlNodeReader->readString();
                                }
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'Flags') {
                                    $xmlFlagNodeReader = new \XMLReader();
                                    $xmlFlagNodeReader->XML($xmlNodeReader->readOuterXml());
                                    while ($xmlFlagNodeReader->read()) {
                                        if ($xmlFlagNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlFlagNodeReader->name != 'Flags') {
                                            if ($xmlFlagNodeReader->name == 'Flag') {
                                                $list[] = $xmlFlagNodeReader->readString();
                                            }
                                        }
                                    }
                                }
                            }
                            unset($xmlFlagNodeReader);
                            unset($xmlNodeReader);
                            $etag_settings = new \suite6\Tackle\TackleEtag($stat, $list);
                            $config->set_etag($etag_settings);
                        }
                        break;
                    case 'EtagFiles':
                        if (!$xmlReader->isEmptyElement) {
                            $xmlNodeReader = new \XMLReader();
                            $xmlNodeReader->XML($xmlReader->readOuterXml());
                            $list = array();
                            while ($xmlNodeReader->read()) {
                                if ($xmlNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlNodeReader->name == 'File') {
                                    $filename = $xmlNodeReader->getAttribute('Name');
                                    if (!$xmlNodeReader->isEmptyElement) {
                                        $xmlFileReader = new \XMLReader();
                                        $xmlFileReader->XML($xmlNodeReader->readOuterXml());
                                        $flag_list = array();
                                        while ($xmlFileReader->read()) {
                                            if ($xmlFileReader->nodeType == \XMLREADER::ELEMENT && $xmlFileReader->name == 'Stat') {
                                                $stat = $xmlFileReader->readString();
                                            }
                                            if ($xmlFileReader->nodeType == \XMLREADER::ELEMENT && $xmlFileReader->name == 'Flags') {
                                                $xmlFlagNodeReader = new \XMLReader();
                                                $xmlFlagNodeReader->XML($xmlFileReader->readOuterXml());
                                                while ($xmlFlagNodeReader->read()) {
                                                    if ($xmlFlagNodeReader->nodeType == \XMLREADER::ELEMENT && $xmlFlagNodeReader->name != 'Flags') {
                                                        if ($xmlFlagNodeReader->name == 'Flag') {
                                                            $flag_list[] = $xmlFlagNodeReader->readString();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        unset($xmlFlagNodeReader);
                                        unset($xmlFileReader);
                                        $list[$filename] = new \suite6\Tackle\TackleEtag($stat, $flag_list);
                                    } else {
                                        $list[$filename] = null;
                                    }
                                }
                            }
                            unset($xmlNodeReader);
                            $config->set_etag_files($list);
                        }
                        break;
                    case 'LastModified':
                        $config->set_last_modified($xmlReader->readString());
                        break;
                    case 'CacheControl':
                        $config->set_cache_control($xmlReader->readString());
                        break;
                    default:
                        break;
                }
            }
        }

        return $config;
    }

}