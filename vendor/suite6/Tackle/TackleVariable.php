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

final class TackleVariable {

    private static $server_var = array(
        'Apache' => array('%{REQUEST_URI}', '%{QUERY_STRING}', '%{REQUEST_FILENAME}', '%{HTTP_HOST}', '%{SERVER_PORT}'),
        'IIS7' => array('{URL}', '{QUERY_STRING}', '{REQUEST_FILENAME}', '{HTTP_HOST}', '{SERVER_PORT}'),
        'Nginx' => array('$request_uri', '$query_string', '$request_filename', '$http_host', '$server_port'),
    );

    public static function get_variable($var_name, $personality = 'Apache') {
//        if (($personality == TackleConfiguration::server_apache)
//                OR ($personality == TackleConfiguration::server_nginx)
//                OR ($personality == TackleConfiguration::server_iis7)) {

            $servers = self::$server_var;

//            if (($key = array_search($var_name, $servers[TackleConfiguration::server_apache])) !== false) {
//                return $servers[$personality][$key];
//            } elseif (($key = array_search($var_name, $servers[TackleConfiguration::server_iis7])) !== false) {
//                return $servers[$personality][$key];
//            } elseif (($key = array_search($var_name, $servers[TackleConfiguration::server_nginx])) !== false) {
//                return $servers[$personality][$key];
//            } else {
//                return $var_name;
//            }
            if (($key = array_search($var_name, $servers['Apache'])) !== false) {
                return $servers[$personality][$key];
            } elseif (($key = array_search($var_name, $servers['IIS7'])) !== false) {
                return $servers[$personality][$key];
            } elseif (($key = array_search($var_name, $servers['Nginx'])) !== false) {
                return $servers[$personality][$key];
            } else {
                return $var_name;
            }
//        } else {
//            throw new \Exception('Tackle: Invalid Server Provided');
//        }
    }

}
