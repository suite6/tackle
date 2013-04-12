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

class TacklerRule {

    private $match_pattern;
    private $action_pattern;
    private $rule_condition = array();

    public function __construct() {
        
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

    public function set_match_pattern($value) {
        $this->match_pattern = $value;
    }

    public function get_match_pattern() {
        return $this->match_pattern;
    }

    public function set_action_pattern($value) {
        $this->action_pattern = $value;
    }

    public function get_action_pattern() {
        return $this->action_pattern;
    }

    public function set_rule_condition(array $condition_list) {
        if (is_array($condition_list))
            $this->rule_condition = $condition_list;
        else
            throw new \Exception("Invalid parameter type passed to property, must be an array");
    }

    public function get_rule_condition() {
        return $this->rule_condition;
    }

}
