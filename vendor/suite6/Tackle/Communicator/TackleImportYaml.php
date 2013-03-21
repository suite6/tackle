<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TackleImportYaml
 *
 * @author Muhammad Zeeshan Sharif
 */

namespace suite6\Tackle\Communicator;
class TackleImportYaml {

    private $yaml;
    
    public function __construct($yaml)
    {
        $this->yaml=$yaml;
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
    
    public function get_yaml_string() {
        return $this->yaml;
    }
    
    /**
     * Returns the identified Role
     *
     * @throws Exception
     * @return suite6\Tackle\TackleConfiguration
     */
    public function get_configuration_object()
    {
        $config=new \suite6\Tackle\TackleConfiguration();
        
        $spycReader=new \suite6\Tackle\Communicator\Spyc();
        $yaml_array=\suite6\Tackle\Communicator\Spyc::YAMLLoadString($this->yaml);
        
        $config->set_default_policy($yaml_array['DefaultPolicy']);
        $config->set_default_directory_listing_policy($yaml_array['DefaultDirectoryListingPolicy']);
        $config->set_sym_link($yaml_array['SymbolicLink']);
        $config->set_default_directory_handler($yaml_array['DefaultDirectoryHandler']);
        $config->set_default_404_handler($yaml_array['Default404Handler']);
        $config->set_default_403_handler($yaml_array['Default403Handler']);
        $config->set_time_to_expiration($yaml_array['TimeToExpiration']);
        $config->set_cache_php_script($yaml_array['CachePHPScript']==1?\suite6\Tackle\TackleConfiguration::flag_on:\suite6\Tackle\TackleConfiguration::flag_off);
        $config->set_banned_ips($yaml_array['BannedIps']);
        $config->set_denied_files($yaml_array['DeniedFile']);
        $config->set_allowed_files($yaml_array['AllowedFile']);
        $config->set_denied_directories($yaml_array['DeniedDirectory']);
        $config->set_allowed_directories($yaml_array['AllowedDirectory']);
        $config->set_temporary_redirects($yaml_array['TemporaryRedirect']);
        $config->set_permanent_redirects($yaml_array['PermanentRedirect']);
        $config->set_domain_redirects($yaml_array['DomainRedirect']);
        $config->set_gzip_serve_extensions($yaml_array['GzipCompression']);
        $config->set_gzip_per_file($yaml_array['GzipPerFile']);
        $config->set_listable_directories($yaml_array['ListableDirectories']);
        $config->set_php_configs($yaml_array['PHPConfig']);
        $config->set_php_flags($yaml_array['PHPFlag']);
        $rule_array = array();
        foreach ($yaml_array['InternalRedirect'] as $from => $to) {
            if(is_string($to)){
                $rule_array[$from] = $to;
            } else if (is_array($to)) {
                $rule=new \suite6\Tackle\TackleRule();
                $rule->set_match_pattern($to['Pattern']);
                $rule->set_action_pattern($to['Action']);
                $condition_array = array();
                foreach ($to['Condition'] as $condition) {
                    $condition_array[] = new \suite6\Tackle\TackleCondition($condition['Compare'], $condition['Pattern'], $condition['Flags']);
                }
                $rule->set_rule_condition($condition_array);
                $rule_array[] = $rule;
            }
        }
        $config->set_internal_redirects($rule_array);
        
        //importing etag back
        if(isset($yaml_array['ETag'])){
            $etag_settings = new \suite6\Tackle\TackleEtag($yaml_array['ETag']['State']==1?\suite6\Tackle\TackleConfiguration::flag_on:\suite6\Tackle\TackleConfiguration::flag_off, $yaml_array['ETag']['Flags']);
            $config->set_etag($etag_settings);
        }
        
        //importing etag files back
        $list = array();
        foreach ($yaml_array['EtagFiles'] as $key => $value) {
            if(is_array($value)) {
                $etag_settings = new \suite6\Tackle\TackleEtag($value['State']==1?\suite6\Tackle\TackleConfiguration::flag_on:\suite6\Tackle\TackleConfiguration::flag_off, $value['Flags']);
                $list[$key] = $etag_settings;
            } else {
                $list[$key] = $value;
            }
        }
        $config->set_etag_files($list);

        $config->set_last_modified($yaml_array['LastModified']);
        
        $config->set_cache_control($yaml_array['CacheControl']);
        
        return $config;
    }

}

?>
