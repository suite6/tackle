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

namespace suite6\Tackler\Communicator;

class TacklerExport {

    private $settings;

    public function __construct(\suite6\Tackler\TacklerConfiguration $settings) {
        $this->settings = $settings;
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

    public function get_configuration_xml() {
        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);

        $xmlWriter->startDocument('1.0', 'utf8');
        $xmlWriter->writeComment(PHP_EOL . implode(PHP_EOL, $this->settings->getComments()) . PHP_EOL);
        
        $xmlWriter->startElement('configuration');
        $xmlWriter->startElement('meta');
        $xmlWriter->writeElement('LibraryVersion', $this->settings->get_library_version());
        $xmlWriter->writeElement('SchemaVersion', $this->settings->get_schema_version());
        $xmlWriter->endElement(); //end meta
        $xmlWriter->writeElement('DefaultPolicy', $this->settings->get_default_policy());
        $xmlWriter->writeElement('DefaultDirectoryListingPolicy', $this->settings->get_default_directory_listing_policy());
        $xmlWriter->writeElement('SymbolicLink', $this->settings->get_sym_link());
        $xmlWriter->writeElement('DefaultDirectoryHandler', $this->settings->get_default_directory_handler());
        $xmlWriter->writeElement('Default404Handler', $this->settings->get_default_404_handler());
        $xmlWriter->writeElement('Default403Handler', $this->settings->get_default_403_handler());
        $xmlWriter->writeElement('TimeToExpiration', $this->settings->get_time_to_expiration());
        $xmlWriter->writeElement('CachePHPScript', $this->settings->get_cache_php_script());

        $xmlWriter->startElement('BannedIps');
        foreach ($this->settings->get_banned_ips() as $value) {
            $xmlWriter->writeElement('IP', $value);
        }
        $xmlWriter->endElement(); //end BannedIps

        $xmlWriter->startElement('DeniedFile');
        foreach ($this->settings->get_denied_files() as $value) {
            $xmlWriter->writeElement('File', $value);
        }
        $xmlWriter->endElement(); //end DeniedFile

        $xmlWriter->startElement('AllowedFile');
        foreach ($this->settings->get_allowed_files() as $value) {
            $xmlWriter->writeElement('File', $value);
        }
        $xmlWriter->endElement(); //end AllowedFile

        $xmlWriter->startElement('DeniedDirectory');
        foreach ($this->settings->get_denied_directories() as $value) {
            $xmlWriter->writeElement('Directory', $value);
        }
        $xmlWriter->endElement(); //end DeniedDirectory

        $xmlWriter->startElement('AllowedDirectory');
        foreach ($this->settings->get_allowed_directories() as $value) {
            $xmlWriter->writeElement('Directory', $value);
        }
        $xmlWriter->endElement(); //end AllowedDirectory

        $xmlWriter->startElement('InternalRedirect');
        foreach ($this->settings->get_internal_redirects() as $from => $to) {
            if (is_string($to)) {
                $xmlWriter->startElement('Redirect');
                $xmlWriter->writeElement('Pattern', $from);
                $xmlWriter->writeElement('Action', $to);
                $xmlWriter->endElement(); //end Redirect
            } elseif (get_class($to) == 'suite6\Tackler\TacklerRule') {
                $rule = $to->get_rule_condition();
                $xmlWriter->startElement('Redirect');
                $xmlWriter->writeElement('Pattern', $to->get_match_pattern());
                $xmlWriter->writeElement('Action', $to->get_action_pattern());
                $xmlWriter->startElement('Conditions');
                for ($condition_counter = 0; $condition_counter < count($rule); $condition_counter++) {
                    //if multiple flags are provided
                    if (is_array($rule[$condition_counter]->get_condition_combine())) {
                        $xmlWriter->startElement('Expression');
                        $xmlWriter->startElement('Comparison');
                        $xmlWriter->writeAttribute('Flags', implode(',', $rule[$condition_counter]->get_condition_combine()));
                        $xmlWriter->text($rule[$condition_counter]->get_match_value());
                        $xmlWriter->endElement(); //end Comparison
                        $xmlWriter->writeElement('Value', $rule[$condition_counter]->get_match_pattern());
                        $xmlWriter->endElement(); //end Expression
                    } else {
                        $xmlWriter->startElement('Expression');
                        $xmlWriter->startElement('Comparison');
                        $xmlWriter->writeAttribute('Flags', $rule[$condition_counter]->get_condition_combine());
                        $xmlWriter->text($rule[$condition_counter]->get_match_value());
                        $xmlWriter->endElement(); //end Comparison
                        $xmlWriter->writeElement('Value', $rule[$condition_counter]->get_match_pattern());
                        $xmlWriter->endElement(); //end Expression
                    }
                }
                $xmlWriter->endElement(); //end Conditions
                $xmlWriter->endElement(); //end Redirect
            }
        }
        $xmlWriter->endElement(); //end InternalRedirect

        $xmlWriter->startElement('TemporaryRedirect');
        foreach ($this->settings->get_temporary_redirects() as $from => $to) {
            $xmlWriter->startElement('Redirect');
            $xmlWriter->writeElement('Pattern', $from);
            $xmlWriter->writeElement('Action', $to);
            $xmlWriter->endElement(); //end Redirect
        }
        $xmlWriter->endElement(); //end TemporaryRedirect

        $xmlWriter->startElement('PermanentRedirect');
        foreach ($this->settings->get_permanent_redirects() as $from => $to) {
            $xmlWriter->startElement('Redirect');
            $xmlWriter->writeElement('Pattern', $from);
            $xmlWriter->writeElement('Action', $to);
            $xmlWriter->endElement(); //end Redirect
        }
        $xmlWriter->endElement(); //end PermanentRedirect

        $xmlWriter->startElement('DomainRedirect');
        foreach ($this->settings->get_domain_redirects() as $from => $to) {
            $xmlWriter->startElement('Redirect');
            $xmlWriter->writeElement('Pattern', $from);
            $xmlWriter->writeElement('Action', $to);
            $xmlWriter->endElement(); //end Redirect
        }
        $xmlWriter->endElement(); //end DomainRedirect

        $xmlWriter->startElement('GzipCompression');
        foreach ($this->settings->get_gzip_serve_extensions() as $ext => $content_type) {
            $xmlWriter->startElement('File');
            $xmlWriter->writeElement('Extension', $ext);
            $xmlWriter->writeElement('ContentType', $content_type);
            $xmlWriter->endElement(); //end File
        }
        $xmlWriter->endElement(); //end GzipCompression

        $xmlWriter->startElement('GzipPerFile');
        foreach ($this->settings->get_gzip_per_file() as $ext => $content_type) {
            $xmlWriter->startElement('File');
            $xmlWriter->writeElement('Extension', $ext);
            $xmlWriter->writeElement('ContentType', $content_type);
            $xmlWriter->endElement(); //end File
        }
        $xmlWriter->endElement(); //end GzipPerFile

        $xmlWriter->startElement('ListableDirectories');
        foreach ($this->settings->get_listable_directories() as $value) {
            $xmlWriter->writeElement('Directory', $value);
        }
        $xmlWriter->endElement(); //end ListableDirectories

        $xmlWriter->startElement('PHPConfig');
        foreach ($this->settings->get_php_configs() as $key => $value) {
            $xmlWriter->startElement('Configuration');
            $xmlWriter->writeElement('Property', $key);
            $xmlWriter->writeElement('Value', $value);
            $xmlWriter->endElement(); //end Configuration
        }
        $xmlWriter->endElement(); //end PHPConfig

        $xmlWriter->startElement('PHPFlag');
        foreach ($this->settings->get_php_flags() as $key => $value) {
            $xmlWriter->startElement('Configuration');
            $xmlWriter->writeElement('Flag', $key);
            $xmlWriter->writeElement('Value', $value);
            $xmlWriter->endElement(); //end Configuration
        }
        $xmlWriter->endElement(); //end PHPFlag

        if ($this->settings->get_etag() !== null) {
            $xmlWriter->startElement('ETag');
            $xmlWriter->writeElement('Stat', $this->settings->get_etag()->get_stat());
            $xmlWriter->startElement('Flags');
            foreach ($this->settings->get_etag()->get_flags() as $flag) {
                $xmlWriter->writeElement('Flag', $flag);
            }
            $xmlWriter->endElement(); //end Flags
            $xmlWriter->endElement(); //end ETag
        }

        $xmlWriter->startElement('EtagFiles');
        foreach ($this->settings->get_etag_files() as $key => $etag) {
            $xmlWriter->startElement('File');
            $xmlWriter->writeAttribute('Name', $key);
            if (!is_null($etag)) {
                $xmlWriter->writeElement('Stat', $etag->get_stat());
                $xmlWriter->startElement('Flags');
                foreach ($etag->get_flags() as $flag) {
                    $xmlWriter->writeElement('Flag', $flag);
                }
                $xmlWriter->endElement(); //end Flags
            }
            $xmlWriter->endElement(); //end File
        }
        $xmlWriter->endElement(); //end EtagFiles

        $xmlWriter->writeElement('LastModified', $this->settings->get_last_modified());

        $xmlWriter->writeElement('CacheControl', $this->settings->get_cache_control());

        $xmlWriter->endElement(); //end configuration
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();
    }

}