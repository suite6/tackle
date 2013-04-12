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

/*
 * This file is optional and can be used to load the Tackler classes if you don't have
 * an autoloading mechanism in place already. If you do, this will not interfere.
 */

namespace suite6\Tackler;

function require_if_exists($path) {
    if ((file_exists($path)) && (is_readable($path))) {
        require_once($path);
        return TRUE;
    }
    return FALSE;
}

spl_autoload_register(function($class) {
            $class = str_replace('\\', '/', $class);
            if (strcmp($class, 'Zip') == 0)
                $path = __DIR__ . '/Generator/' . $class;
            else
                $path = 'vendor/' . $class;
            if (!require_if_exists($path . '.php'))
                require_if_exists($path . '.inc');
        }
);