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

namespace suite6\Tackle;

class TackleEtag {

    const etag_all = 'All';
    const etag_none = 'None';
    const etag_mtime = 'MTime';
    const etag_inode = 'INode';
    const etag_size = 'Size';

    private $stat;
    private $flags = array();

    public function __construct($stat = TackleConfiguration::flag_on, $flags = array(TackleEtag::etag_all)) {
        $this->set_stat($stat);
        $this->set_flags($flags);
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

    public function set_stat($flag) {
        if ($flag == TackleConfiguration::flag_off || $flag == TackleConfiguration::flag_on) {
            $this->stat = $flag;
        } else {
            throw new \Exception('Tackle: Flag not found');
        }
    }

    public function get_stat() {
        return $this->stat;
    }

    public function set_flags($etag_flags) {
        if (is_array($etag_flags) && count($etag_flags) > 0) {
            foreach ($etag_flags as $flag) {
                $this->isValidFlag($flag);
            }
            $this->flags = $etag_flags;
        } else {
            throw new \Exception('Tackle: Invalid argument passed, must be an array with valid etag flags');
        }
    }

    public function get_flags() {
        return $this->flags;
    }

    private function isValidFlag($flag) {
        if ($flag == TackleEtag::etag_all
                || $flag == TackleEtag::etag_inode
                || $flag == TackleEtag::etag_mtime
                || $flag == TackleEtag::etag_size) {
            return true;
        } else {
            throw new \Exception('Tackle: Invalid argument passed, must be a valid etag flags');
        }
    }

}