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

namespace suite6\Tackler;

class TacklerConfiguration {

    const policy_allow = 'ALLOW';
    const policy_deny = 'DENY';
    const policy_follow = 'FOLLOW';
    const policy_unfollow = 'UNFOLLOW';
    const format_xml = 'XML';
    const format_yaml = 'YAML';
    const flag_on = 'ON';
    const flag_off = 'OFF';

    private $default_policy = TacklerConfiguration::policy_allow;
    private $sym_link;
    private $denied_files = array(); // 403
    private $allowed_files = array();
    private $denied_directories = array(); // 403
    private $allowed_directories = array();
    private $internal_redirects = array();
    private $temporary_redirects = array(); // 302
    private $permanent_redirects = array();  // 301
    private $domain_redirects = array();  // 301
    private $banned_ips = array();
    private $default_directory_listing_policy = TacklerConfiguration::policy_deny;
    private $listable_directories = array();
    private $default_directory_handler = 'index.php';
    private $default_404_handler;
    private $default_403_handler;
    private $php_configs = array();
    private $php_flags = array();
    private $time_to_expiration;
    private $cache_php_script;
    private $gzip_serve_extensions = array();
    private $gzip_per_file = array();
    private $comments = array();
    private $etag;
    private $etag_files = array();
    private $last_modified;
    private $cache_control;

    public function __construct() {
        $this->denied_files = array('.tackle');
        $this->comments[] = "Tackler: Configure any web server with only one set of directives";
        $this->comments[] = "http://tackleproject.org";
        $this->comments[] = "MIT License. By Louis-Eric Simard / Suite6";
    }
    
    public function get_library_version()
    {
        return '1.0.0';
    }
    
     public function get_schema_version()
    {
        return '1.0';
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

    public function addComments($comment) {
        $this->comments[] = $comment;
    }

    public function getComments() {
        return array_unique($this->comments);
    }

    public function set_default_policy($value) {
        if (($value == TacklerConfiguration::policy_allow) OR ($value == TacklerConfiguration::policy_deny)) {
            $this->default_policy = $value;
        }
    }

    public function get_default_policy() {
        return $this->default_policy;
    }

    public function set_sym_link($value) {
        if (($value == TacklerConfiguration::policy_follow) OR ($value == TacklerConfiguration::policy_unfollow)) {
            $this->sym_link = $value;
        }
    }

    public function get_sym_link() {
        return $this->sym_link;
    }

    public function set_denied_files(array $file_list) {
        if (is_array($file_list))
            $this->denied_files = $file_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_denied_files() {
        return $this->denied_files;
    }

    public function set_allowed_files(array $file_list) {
        if (is_array($file_list))
            $this->allowed_files = $file_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_allowed_files() {
        return $this->allowed_files;
    }

    public function set_denied_directories(array $dir_list) {
        if (is_array($dir_list))
            $this->denied_directories = $dir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_denied_directories() {
        return $this->denied_directories;
    }

    public function set_allowed_directories(array $dir_list) {
        if (is_array($dir_list))
            $this->allowed_directories = $dir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_allowed_directories() {
        return $this->allowed_directories;
    }

    public function set_internal_redirects(array $redir_list) {
        if (is_array($redir_list))
            $this->internal_redirects = $redir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_internal_redirects() {
        return $this->internal_redirects;
    }

    public function set_temporary_redirects(array $redir_list) {
        if (is_array($redir_list))
            $this->temporary_redirects = $redir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_temporary_redirects() {
        return $this->temporary_redirects;
    }

    public function set_permanent_redirects(array $redir_list) {
        if (is_array($redir_list))
            $this->permanent_redirects = $redir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_permanent_redirects() {
        return $this->permanent_redirects;
    }

    public function set_domain_redirects(array $redir_list) {
        if (is_array($redir_list))
            $this->domain_redirects = $redir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_domain_redirects() {
        return $this->domain_redirects;
    }

    public function set_banned_ips(array $ips_list) {
        if (is_array($ips_list))
            $this->banned_ips = $ips_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_banned_ips() {
        return $this->banned_ips;
    }

    public function set_default_directory_listing_policy($value) {
        if (($value == TacklerConfiguration::policy_allow) OR ($value == TacklerConfiguration::policy_deny)) {
            $this->default_directory_listing_policy = $value;
        }
    }

    public function get_default_directory_listing_policy() {
        return $this->default_directory_listing_policy;
    }

    public function set_listable_directories(array $dir_list) {
        if (is_array($dir_list))
            $this->listable_directories = $dir_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_listable_directories() {
        return $this->listable_directories;
    }

    public function set_default_directory_handler($file_name) {
        $this->default_directory_handler = $file_name;
    }

    public function get_default_directory_handler() {
        return $this->default_directory_handler;
    }

    public function set_default_404_handler($file_name) {
        $this->default_404_handler = $file_name;
    }

    public function get_default_404_handler() {
        return $this->default_404_handler;
    }

    public function set_default_403_handler($file_name) {
        $this->default_403_handler = $file_name;
    }

    public function get_default_403_handler() {
        return $this->default_403_handler;
    }

    public function set_php_configs(array $configs) {
        if (is_array($configs))
            $this->php_configs = $configs;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_php_configs() {
        return $this->php_configs;
    }

    public function set_php_flags(array $flags) {
        if (is_array($flags))
            $this->php_flags = $flags;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_php_flags() {
        return $this->php_flags;
    }

    public function set_time_to_expiration($time) {
        if (trim($time) != '')
            $this->time_to_expiration = trim($time);
    }

    public function get_time_to_expiration() {
        return $this->time_to_expiration;
    }

    public function set_cache_php_script($flag) {
        if ($flag == TacklerConfiguration::flag_off || $flag == TacklerConfiguration::flag_on) {
            $this->cache_php_script = $flag;
        } else {
            throw new \Exception('Tackler: Flag not found');
        }
    }

    public function get_cache_php_script() {
        return $this->cache_php_script;
    }

    public function set_gzip_serve_extensions(array $ext) {
        if (is_array($ext))
            $this->gzip_serve_extensions = $ext;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_gzip_serve_extensions() {
        return $this->gzip_serve_extensions;
    }

    public function set_gzip_per_file(array $ext) {
        if (is_array($ext))
            $this->gzip_per_file = $ext;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_gzip_per_file() {
        return $this->gzip_per_file;
    }

    public function set_etag(TacklerEtag $etag) {
        $this->etag = $etag;
    }

    /**
     * @return TacklerEtag
     * @return type 
     */
    public function get_etag() {
        return $this->etag;
    }

    public function set_etag_files(array $file_list) {
        if (is_array($file_list)) {
            foreach ($file_list as $etag) {
                //null is allowed
                if (!is_null($etag)) {
                    //only object of TacklerEtag class is allowed
                    if (gettype($etag) == 'object') {
                        if (!get_class($etag) == 'TacklerEtag')
                            throw new \Exception("Invalid parameter type passed to property, must be an intance of etag class");
                    } else {
                        throw new \Exception("Invalid parameter type passed to property, must be an intance of etag class");
                    }
                }
            }
            $this->etag_files = $file_list;
        }
        else
            throw new \Exception("Invalid parameter type passed to property, must be an intance of etag class");
    }

    public function get_etag_files() {
        return $this->etag_files;
    }

    public function set_last_modified($datetime) {
        $this->last_modified = $datetime;
    }

    public function get_last_modified() {
        return $this->last_modified;
    }

    public function set_cache_control($cache) {
        $this->cache_control = $cache;
    }

    public function get_cache_control() {
        return $this->cache_control;
    }

    /**
     * Returns tackle configuration for specified server in array
     *
     * this method will return the tackler configuration for the specified server
     * as an array. this method will return the configuration as per filled by
     * the user before calling it using the set_ properties of this class
     *
     * @param $personality this parameter is the server/generator class name
     * right now we have support for three servers, apache (default), nginx and
     * IIS7, so for nginx we can pass Nginx and this will automacitally load 
     * the corresponding class
     * @throws Exception
     * @return array()
     */
    public function get_configuration($personality='Apache') {

            $generator_name = 'suite6\Tackler\Generator\TacklerGenerator' . $personality;
            
            $tackleGenerator = new $generator_name($this);
            return $tackleGenerator->generate_configs();
    }
    
    public function get_config_file($personality='Apache') {
        $generator_name = 'suite6\Tackler\Generator\TacklerGenerator' . $personality;           
        $tackleGenerator = new $generator_name($this);
        return $tackleGenerator->generate_configs_file();
    }

    /**
     * Saves the loaded tackler configuration to specified file
     *
     * this method will save the tackler configuration to the specified file 
     * of the specified server, the configuration could be load use the load
     * method of this class or can be also done by manually filling properties
     * if file name passed is .tackle then default format will be used (xml) and
     * if file name passed like .tackle.yaml then this will try to load the requested
     * format class and if fail will generate an error
     *
     * @param $filename Name of the file that will be written to root folder
     * 
     * @throws Exception
     * @return void
     */
    public function save($filename = ".tackle") {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        $config_string = '';
        if ($extension == 'tackle') {
            $xml_exporter = new Communicator\TacklerExport($this);
            $config_string = $xml_exporter->get_configuration_xml();
        } else {
            $communicator = "suite6\Tackler\Communicator\TacklerExport" . ucfirst($extension);
            $unknown_exporter = new $communicator($this);
            $unknown_method = "get_configuration_" . $extension;
            $config_string = $unknown_exporter->$unknown_method();
        }

        //writing to file overwrite if file exists
        file_put_contents($filename, $config_string);
    }

    /**
     * Load tackle configuration from the specified xml/yaml file
     *
     * this method will load tackle configuration fromt he specified file, 
     * file could be xml or yaml file that was previously extracted using the
     * save method of this class. Defualt file is .tackle in output folder
     * if file name passed is .tackle then default format will be used (xml) and
     * if file name passed like .tackle.yaml then this will try to load the requested
     * format class and if fail will generate an error
     *
     * @param $filename Name of the file with path to load tackle configuration
     * 
     * @throws Exception
     * @return TacklerConfiguration
     */
    public function load($filename = ".tackle") {
        if (file_exists($filename)) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            if ($extension == 'tackle') {
                $xml_string = file_get_contents($filename);
                $xml_importer = new Communicator\TacklerImporter($xml_string);
                return $xml_importer->get_configuration_object();
            } else {
                $unknown_string = file_get_contents($filename);
                $communicator = "suite6\Tackler\Communicator\TacklerImport" . ucfirst($extension);
                $unknown_importer = new $communicator($unknown_string);
                return $unknown_importer->get_configuration_object();
            }
        } else {
            throw new \Exception('Tackler: Specified File does not exist');
        }
    }

    /**
     * Download the loaded tackler configuration for specified server
     *
     * this method will make the configuration file(s) downloadable to the user
     * as per the provided server name .htaccess, web.config, nginx.conf
     *
     * @param $personality can consume any of the following class constants
     * TacklerConfiguration::server_apache for apache (default)
     * TacklerConfiguration::server_nginx for nginx
     * TacklerConfiguration::server_iis7 for IIS7
     * 
     * @throws Exception
     * @return void
     */
    public function download($personality = 'Apache') {
        $configuration = $this->get_config_file($personality);
        header($configuration['mime-type']);
        header("Content-Disposition: attachment; filename=". $configuration['file-name']);
        echo $configuration['content'];
    }

    /**
     * Merge the two configurations object
     *
     * this method will prepend the provided configuration through parameter to
     * current object and return a new object, current configuration will override
     * the provided configuration (through parameter) if there is a match
     *
     * @param $settings TacklerConfiguration object to prepend with current object
     * 
     * @throws Exception
     * @return TacklerConfiguration
     */
    public function merge(TacklerConfiguration $settings) {
        $combined_settings = new TacklerConfiguration();

        //set default policy configuration
        if (is_null($this->get_default_policy()))
            $combined_settings->set_default_policy($settings->get_default_policy());
        else
            $combined_settings->set_default_policy($this->get_default_policy());

        //set default directory listing policy configuration
        if (is_null($this->get_default_directory_listing_policy()))
            $combined_settings->set_default_directory_listing_policy($settings->get_default_directory_listing_policy());
        else
            $combined_settings->set_default_directory_listing_policy($this->get_default_directory_listing_policy());

        //set symbolic link configuration
        if (is_null($this->get_sym_link()))
            $combined_settings->set_sym_link($settings->get_sym_link());
        else
            $combined_settings->set_sym_link($this->get_sym_link());

        //set banned ips
        $combined_settings->set_banned_ips(array_unique(array_merge($settings->get_banned_ips(), $this->get_banned_ips())));

        //set denied files list 403
        $combined_settings->set_denied_files(array_unique(array_merge($settings->get_denied_files(), $this->get_denied_files())));

        //set allowed files list 
        $combined_settings->set_allowed_files(array_unique(array_merge($settings->get_allowed_files(), $this->get_allowed_files())));

        //set denied directories list 403
        $combined_settings->set_denied_directories(array_unique(array_merge($settings->get_denied_directories(), $this->get_denied_directories())));

        //set allowed directories list 
        $combined_settings->set_allowed_directories(array_unique(array_merge($settings->get_allowed_directories(), $this->get_allowed_directories())));

        //handling internal redirects
        $all_rules = array();
        $current_redirect = $this->get_internal_redirects();
        //put all rule's match pattern in $all_rules array to compare later
        foreach ($this->get_internal_redirects() as $key => $rule) {
            if (!is_string($rule) && get_class($rule) == 'suite6\Tackler\TacklerRule') {
                if (!array_key_exists($rule->get_match_pattern(), $all_rules)) {
                    $all_rules[$rule->get_match_pattern()] = $rule->get_action_pattern();
                }
            }
        }

        foreach ($settings->get_internal_redirects() as $from => $to) {
            if (is_string($to)) {
                if (!array_key_exists($from, $combined_settings->get_internal_redirects())) {
                    $current_redirect[$from] = $to;
                }
            } elseif (get_class($to) == 'suite6\Tackler\TacklerRule') {
                //check if rule is already there with same match pattern
                if (!array_key_exists($to->get_match_pattern(), $all_rules)) {
                    $current_redirect[] = $to;
                }
            }
        }

        $combined_settings->set_internal_redirects($current_redirect);

        //handling temporary redirects 302
        $combined_settings->set_temporary_redirects(array_merge($settings->get_temporary_redirects(), $this->get_temporary_redirects()));

        //handling permenent redirects 301
        $combined_settings->set_permanent_redirects(array_merge($settings->get_permanent_redirects(), $this->get_permanent_redirects()));

        //handling domain redirects 301
        $combined_settings->set_domain_redirects(array_merge($settings->get_domain_redirects(), $this->get_domain_redirects()));

        //handling gzip extensions
        $combined_settings->set_gzip_serve_extensions(array_merge($settings->get_gzip_serve_extensions(), $this->get_gzip_serve_extensions()));

        //handling per file gzip extensions
        $combined_settings->set_gzip_per_file(array_merge($settings->get_gzip_per_file(), $this->get_gzip_per_file()));

        //handling listable directories
        $combined_settings->set_listable_directories(array_unique(array_merge($settings->get_listable_directories(), $this->get_listable_directories())));

        //setting default directory handler/document root
        if (is_null($this->get_default_directory_handler()))
            $combined_settings->set_default_directory_handler($settings->get_default_directory_handler());
        else
            $combined_settings->set_default_directory_handler($this->get_default_directory_handler());

        //setting default 404
        if (is_null($this->get_default_404_handler()))
            $combined_settings->set_default_404_handler($settings->get_default_404_handler());
        else
            $combined_settings->set_default_404_handler($this->get_default_404_handler());

        //setting default 403
        if (is_null($this->get_default_403_handler()))
            $combined_settings->set_default_403_handler($settings->get_default_403_handler());
        else
            $combined_settings->set_default_403_handler($this->get_default_403_handler());

        //handling php values
        $combined_settings->set_php_configs(array_merge($settings->get_php_configs(), $this->get_php_configs()));

        //handling php flags
        $combined_settings->set_php_flags(array_merge($settings->get_php_flags(), $this->get_php_flags()));

        //handling cache
        if (is_null($this->get_time_to_expiration()))
            $combined_settings->set_time_to_expiration($settings->get_time_to_expiration());
        else
            $combined_settings->set_time_to_expiration($this->get_time_to_expiration());

        //handling of php cache script
        if (is_null($this->get_cache_php_script()))
            $combined_settings->set_cache_php_script($settings->get_cache_php_script());
        else
            $combined_settings->set_cache_php_script($this->get_cache_php_script());

        //handling of etag
        if (!is_null($this->get_etag()))
            $combined_settings->set_etag($this->get_etag());
        elseif(!is_null($settings->get_etag()))
            $combined_settings->set_etag($settings->get_etag());

        //handling of etag per file
        $combined_settings->set_etag_files(array_merge($settings->get_etag_files(), $this->get_etag_files()));

        //handling of last modified
        if (is_null($this->get_last_modified()))
            $combined_settings->set_last_modified($settings->get_last_modified());
        else
            $combined_settings->set_last_modified($this->get_last_modified());

        //handling of cache control
        if (is_null($this->get_cache_control()))
            $combined_settings->set_cache_control($settings->get_cache_control());
        else
            $combined_settings->set_cache_control($this->get_cache_control());

        //handling of comments
        $combined_comments = array_unique($settings->getComments() + $this->getComments());
        foreach ($combined_comments as $comment) {
            $combined_settings->addComments($comment);
        }

        return $combined_settings;
    }
    
    

}

