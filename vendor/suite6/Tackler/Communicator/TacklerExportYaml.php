<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TacklerExportYaml
 *
 * @author Muhammad Zeeshan Sharif
 */

namespace suite6\Tackler\Communicator;
class TacklerExportYaml {

    private $settings;
    
    public function __construct(\suite6\Tackler\TacklerConfiguration $settings)
    {
        $this->settings=$settings;
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
    
    public function get_setting_object() {
        return $this->settings;
    }
    
    public function get_configuration_yaml()
    {        
        $config_array=array();
        
        $config_array['DefaultPolicy'] = $this->settings->get_default_policy();
        $config_array['DefaultDirectoryListingPolicy'] = $this->settings->get_default_directory_listing_policy();
        $config_array['SymbolicLink'] = $this->settings->get_sym_link();
        $config_array['DefaultDirectoryHandler'] = $this->settings->get_default_directory_handler();
        $config_array['Default404Handler'] = $this->settings->get_default_404_handler();
        $config_array['Default403Handler'] = $this->settings->get_default_403_handler();
        $config_array['TimeToExpiration'] = $this->settings->get_time_to_expiration();
        $config_array['CachePHPScript'] = $this->settings->get_cache_php_script();
        $list=array();
        foreach ($this->settings->get_banned_ips() as $value) {
            $list[]= $value;
        }
        $config_array['BannedIps'] = $list;
        
        $list=array();
        foreach ($this->settings->get_denied_files() as $value) {
            $list[]= $value;
        }
        $config_array['DeniedFile'] = $list;
        
        $list=array();
        foreach ($this->settings->get_allowed_files() as $value) {
            $list[]= $value;
        }
        $config_array['AllowedFile'] = $list;
        
        $list=array();
        foreach ($this->settings->get_denied_directories() as $value) {
            $list[]= $value;
        }
        $config_array['DeniedDirectory'] = $list;
        
        $list=array();
        foreach ($this->settings->get_allowed_directories() as $value) {
            $list[]= $value;
        }
        $config_array['AllowedDirectory'] = $list;
        
        $list=array();
        foreach ($this->settings->get_internal_redirects() as $from => $to) {
            if(is_string($to)){
                $list[$from]= $to;
            } elseif(get_class($to)=='suite6\Tackler\TackleRuler') {
                $rule=$to->get_rule_condition();
                $rule_array=array();
                $rule_array['Pattern'] = $to->get_match_pattern();
                $rule_array['Action'] = $to->get_action_pattern();
                $condition_array = array();
                for ($condition_counter=0;$condition_counter<count($rule);$condition_counter++) {
                    $expression_array = array();
                    $expression_array['Compare'] = $rule[$condition_counter]->get_match_value();
                    $expression_array['Pattern'] = $rule[$condition_counter]->get_match_pattern();
                    $expression_array['Flags'] = $rule[$condition_counter]->get_condition_combine();
                    $condition_array[] = $expression_array;
                }
                $rule_array['Condition'] = $condition_array;
                $list[]= $rule_array;
            }
        }
        $config_array['InternalRedirect'] = $list;
        
        $list=array();
        foreach ($this->settings->get_temporary_redirects() as $from => $to) {
            $list[$from]= $to;
        }
        $config_array['TemporaryRedirect'] = $list;
        
        $list=array();
        foreach ($this->settings->get_permanent_redirects() as $from => $to) {
            $list[$from]= $to;
        }
        $config_array['PermanentRedirect'] = $list;
        
        $list=array();
        foreach ($this->settings->get_domain_redirects() as $from => $to) {
            $list[$from]= $to;
        }
        $config_array['DomainRedirect'] = $list;
        
        $list=array();
        foreach ($this->settings->get_gzip_serve_extensions() as $ext => $content_type) {
            $list[$ext]= $content_type;
        }
        $config_array['GzipCompression'] = $list;
        
        $list=array();
        foreach ($this->settings->get_gzip_per_file() as $ext => $content_type) {
            $list[$ext]= $content_type;
        }
        $config_array['GzipPerFile'] = $list;
        
        $list=array();
        foreach ($this->settings->get_listable_directories() as $value) {
            $list[]= $value;
        }
        $config_array['ListableDirectories'] = $list;
        
        $list=array();
        foreach ($this->settings->get_php_configs() as $key => $value) {
            $list[$key]= $value;
        }
        $config_array['PHPConfig'] = $list;
        
        $list=array();
        foreach ($this->settings->get_php_flags() as $key => $value) {
            $list[$key]= $value;
        }
        $config_array['PHPFlag'] = $list;
        
        if($this->settings->get_etag()!==null){
            $etag_settings = array();
            $etag_settings['State'] = $this->settings->get_etag()->get_stat();
            $etag_settings['Flags'] = $this->settings->get_etag()->get_flags();
            $config_array['ETag'] = $etag_settings;
        }
        
        $list=array();
        foreach ($this->settings->get_etag_files() as $key=>$etag) {
            if(!is_null($etag)){
                $etag_settings = array();
                $etag_settings['State'] = $etag->get_stat();
                $etag_settings['Flags'] = $etag->get_flags();
                $list[$key]= $etag_settings;
            } else {
                $list[$key]= null;
            }
        }
        $config_array['EtagFiles'] = $list;
        
        $config_array['LastModified'] = $this->settings->get_last_modified();
        
        $config_array['CacheControl'] = $this->settings->get_cache_control();
        
        $comments = '#'. implode(PHP_EOL . '#', $this->settings->getComments()) .PHP_EOL;
        
        return $comments . \suite6\Tackler\Communicator\Spyc::YAMLDump($config_array);
    }

}

?>
