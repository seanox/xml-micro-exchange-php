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
class Storage {

    /** Directory of the data storage */
    const DIRECTORY = "./data";

    /** Maximum number of files in data storage */
    const QUANTITY = 256;

    /** Maximum data size of files in data storage in bytes */
    const SPACE = 256 *1024;

    /** Maximum idle time of the files in seconds */
    const TIMEOUT = 15 *60;
    
    private $storage;
    
    private $store;
    
    private $share;

    private $xml;

    private $xpath;
    
    private function __construct($storage, $xpath) {
        $this->storage = $storage;
        $this->store = Storage::DIRECTORY . "/" . $this->storage; 
        $this->xpath = $xpath;
    }
    
    /** Cleans up all files that have exceeded the maximum idle time. */
    private static function cleanUp() {

        if ($handle = opendir(Storage::DIRECTORY)) {
            $timeout = time() -Storage::TIMEOUT; 
            while (($entry = readdir($handle)) !== false) {
                if ($entry == "."
                        || $entry == "..")
                    continue;
                $entry = Storage::DIRECTORY . "/$entry";
                if (filemtime($entry) > $timeout)
                    continue;
                if (file_exists($entry))
                    @unlink($entry);
            }        
            closedir($handle);
        }
    }
    
    public static function share($storage, $xpath) {

        if (!preg_match("/^[0-9A-Z]{35}$/", $storage)) {
            header("HTTP/1.0 400 Bad Request");
            exit();
        }        
        Storage::cleanUp();
        return new Storage($storage, $xpath);
    }
    
    private function open() {
        
        if ($this->share !== null)
            return;
            
        $this->share = fopen($this->store, "x+");
        flock($this->share, LOCK_EX);

        if (filesize($this->store) <= 0) {
            fwrite($this->share,
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n" .
                "<data rev=\"0\"/>");
            rewind($this->share);
        }

        $this->xml = fread($this->share, filesize($this->store));
        $this->xml = new SimpleXMLElement($this->xml);
    } 
    
    public function close() {

        if ($this->share == null)
            return;
            
        ftruncate($this->share, 0);
        fwrite($this->share, $this->xml->asXML());    

        flock($this->share, LOCK_UN);
        fclose($this->share);

        $this->share = null;
        $this->xml = null;
    }
    
    private function getRevision() {
    
        $this->open();
        return $this->xml->xpath("/data@rev");
    }

    private function getSize() {
    
        if ($this->xml !== null)
            return strlen($this->xml->asXML());
        if ($this->share !== null)
            return filesize($this->share);
        return filesize($this->store);
    }
    
    public function doConnect() {

        // Request:
        //     CONNECT / HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ

        // Response:
        //     HTTP/1.0 201 Created
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes

        // Response:
        //     HTTP/1.0 202 Accepted
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes
        
        // Only path / ist allowed, otherwise status 400 Bad Request
        // If the storage is full, the response is status 507 Insufficient Storage
        // An exact 35 characters long storage must be specified (pattern: [0-9A-Z]{35})
        // The response can be status 201 if the storage was newly created.
        // The answer can be status 202 if the storage already exists.

        //TODO: if ($this->xpath !== "/") {
        //    header("HTTP/1.0 400 Bad Request");
        //    exit();
        //}
        
        $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
        if (iterator_count($iterator) >= Storage::QUANTITY) {
            header("HTTP/1.0 507 Insufficient Storage");
            exit();
        }
        
        if (file_exists($this->store)) {
            touch($this->store); 
            header("HTTP/1.0 202 Accepted");
        } else {
            $this->open();
            header("HTTP/1.0 201 Created");
        }

        header("Storage: " . $this->storage);
        header("Storage-Revision: " . $this->getRevision());
        header("Storage-Size: " . $this->getSize());
        exit();
    }

    public function doGet() {
    
        // Request:
        //     GET /<xpath> HTTP/1.0   
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes   
        //     Content-Length: Bytes
        //     Content-Type: text/plain
        //
        //     Response-Body
        //     - key / value pairs per line
        //     - support of escape-Sequences \r\n\t...\u0000
        //     - support attributes and entities (also nested)
        // 
        //     $attribute: value
        //     entity: value
        //     entity.entity: value
        //     entity.entity.entity: value
        //     entity$attribute: value
        
        exit();
    }

    public function doPut() {
    
        // Request:
        //     PUT /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: Bytes
        //     Content-Type: application/xml
        //
        //     Request-Body
        //     - key / value pairs per line
        //     - support of escape-Sequences \r\n\t...\u0000
        //     - support attributes and entities (also nested)
        // 
        //     $attribute: value
        //     entity: value
        //     entity.entity: value
        //     entity.entity.entity: value
        //     entity$attribute: value
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes
            
        exit();    
    }

    public function doPatch() {
    
        // Request:
        //     PATCH /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: 0 Bytes
        //     Content-Type: application/xml
        //
        //     Request-Body
        //     - key / value pairs per line
        //     - support of escape-Sequences \r\n\t...\u0000
        //     - support attributes and entities (also nested)
        // 
        //     $attribute: value
        //     entity: value
        //     entity.entity: value
        //     entity.entity.entity: value
        //     entity$attribute: value
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes
            
        exit();
    }

    public function doDelete() {
    
        // Request:
        //     DELETE /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ 
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes   
    
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

set_error_handler("Storage::onError");
set_exception_handler("Storage::onException");
register_shutdown_function("Storage::onShutdown");

$storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match("/^[0-9A-Z]{35}$/", $storage)) {
    header("HTTP/1.0 400 Bad Request");
    exit();
}

$storage = Storage::share($storage, $_SERVER["REQUEST_URI"]);

try {
    switch (strtoupper($_SERVER["REQUEST_METHOD"])) {
        case "CONNECT":
            $storage->doConnect();
            break;
        case "GET":
            $storage->doGet();
            break;
        case "PUT":
            $storage->doPut();
            break;
        case "PATCH":
            $storage->doPatch();
            break;
        case "DELETE":
            $storage->doDelete();
            break;
        default:
            header("HTTP/1.0 405 Method Not Allowed");
            header("Allow: CONNECT, OPTIONS, GET, PUT, PATCH, DELETE");
            exit();
    }
} finally {
    $storage->close();
}

?>