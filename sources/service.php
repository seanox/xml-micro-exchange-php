<?php
/**
 * LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
 * Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 * Diese Software unterliegt der Version 2 der Apache License.
 *
 * XML Online Storage
 * Copyright (C) 2020 Seanox Software Solutions
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *  
 * http://www.apache.org/licenses/LICENSE-2.0
 *  
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * TODO: 
 */
class Service {

    /** Directory of the data storage */
    const DIRECTORY = "./data";

    /** Maximum number of files in data storage */
    const QUANTITY = 256;

    /** Maximum data size of files in data storage in bytes */
    const SPACE = 256 *1024;

    /** Maximum idle time of the files in seconds */
    const TIMEOUT = 15 *60;
    
    /** Cleans up all files that have exceeded the maximum idle time. */
    static function cleanUp() {

        if ($handle = opendir(Service::DIRECTORY)) {
            $timeout = time() -Service::TIMEOUT; 
            while (($entry = readdir($handle)) !== false) {
                if ($entry == "."
                        || $entry == "..")
                    continue;
                $entry = Service::DIRECTORY . "/" . $entry;
                if (filemtime($entry) > $timeout)
                    continue;
                if (file_exists($entry))
                    unlink($entry);
            }        
            closedir($handle);
        }
    }

    static function doConnect() {
        
        // Request:
        //     CONNECT / HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ

        // Response:
        //     HTTP/1.0 201 Created
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Expiration-Time: YYYY-MM-DD hh:mm:ss
        //     Server-Time: YYYY-MM-DD hh:mm:ss
        //     Storage-Length: 0 Bytes

        // Response:
        //     HTTP/1.0 202 Accepted
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Expiration-Time: YYYY-MM-DD hh:mm:ss
        //     Server-Time: YYYY-MM-DD hh:mm:ss
        //     Storage-Length: 0 Bytes
        
        // Only path / ist allowed, otherwise status 400 Bad Request
        // If the storage is full, the response is status 507 Insufficient Storage
        // An exact 35 characters long storage must be specified (pattern: [0-9A-Z]{35})
        // The response can be status 201 if the storage was newly created.
        // The answer can be status 202 if the storage already exists.
    
        exit();
    }

    static function doOptions() {
    
        // Request:
        //     OPTIONS / HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        
        // Response:
        //     HTTP/1.0 204 No Content
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Expiration-Time: YYYY-MM-DD hh:mm:ss
        //     Server-Time: YYYY-MM-DD hh:mm:ss
        //     Storage-Length: 0 Bytes        
        
        // Only path / ist allowed, otherwise status 400 Bad Request
        // If the storage does not existl, the response is status 404 Not Found
            
        exit();
    }
    
    static function doGet() {
    
        // Request:
        //     GET /<xpath> HTTP/1.0   
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        
        // Response:
        //     HTTP/1.0 200 No Content
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Expiration-Time: YYYY-MM-DD hh:mm:ss
        //     Server-Time: YYYY-MM-DD hh:mm:ss
        //     Storage-Length: 0 Bytes    
        //     Content-Length: 0 Bytes
        //     Content-Type: application/xslt+xml
        //
        //     Response-Body (XML)
        
        exit();
    }

    static function doPut() {
        exit();
    }

    static function doPatch() {
        exit();
    }

    static function doDelete() {
        exit();
    }
    
    public static function onError($error, $message, $file, $line, $vars = array()) {
        exit();
    }
    
    public static function onException($exception) {
        exit();
    }
    
    public static function onShutdown() {
        exit();
    }
}

set_error_handler("Service::onError");
set_exception_handler("Service::onException");
register_shutdown_function("Service::onShutdown");
 
Service::cleanUp();

switch (strtoupper($_SERVER["REQUEST_METHOD"])) {
    case "CONNECT":
        Service::doConnect();
        break;
    case "OPTIONS":
        Service::doOptions();
        break;
    case "GET":
        Service::doGet();
        break;
    case "PUT":
        Service::doPut();
        break;
    case "PATCH":
        Service::doPatch();
        break;
    case "DELETE":
        Service::doDelete();
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        header("Allow: CONNECT, OPTIONS, GET, PUT, PATCH, DELETE");
        exit();
}
?>