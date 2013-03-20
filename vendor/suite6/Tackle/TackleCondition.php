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

class TackleCondition {

    const condition_and = 'AND';
    const condition_or = 'OR';
    const condition_file = 'FILE';
    const condition_dir = 'DIR';
    const condition_not_file = 'NOT_FILE';
    const condition_not_dir = 'NOT_DIR';
    const condition_case_insensitive = 'NOT_CASE';

    private $match_value;
    private $match_pattern;
    private $condition_combine = TackleCondition::condition_and;

    public function __construct($value, $pattern, $combine = TackleCondition::condition_and) {
        $this->match_value = $value;
        $this->match_pattern = $pattern;
        $this->condition_combine = $combine;
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

    public function set_match_value($value) {
        $this->match_value = $value;
    }

    public function get_match_value() {
        return $this->match_value;
    }

    public function set_match_pattern($value) {
        $this->match_pattern = $value;
    }

    public function get_match_pattern() {
        return $this->match_pattern;
    }

    public function set_condition_combine($value) {
        if (($value == TackleCondition::condition_and)
                OR ($value == TackleCondition::condition_or)
                OR ($value == TackleCondition::condition_file)
                OR ($value == TackleCondition::condition_dir)
                OR ($value == TackleCondition::condition_not_file)
                OR ($value == TackleCondition::condition_not_dir)
                OR (is_array($value))) {
            $this->condition_combine = $value;
        }
    }

    public function get_condition_combine() {
        return $this->condition_combine;
    }

}