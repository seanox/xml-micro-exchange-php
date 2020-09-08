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
 * - Each node has an internal revision attribute ___rev
 *   Milliseconds since 01/01/2000 alphanumerical radix 36, therefore also lastmodified
 * - Each node has an internal object identify attribute ___oid 
 *   Long counter and reflects the order of creation
 * - Hopefully it is unlikely that someone wants to use this names. 
 * - The field can be accessed read but not write.   
 *   Writing will cause status 405.
 * - If a node or attribute is changed, the revision of the current node and all its parents is increased.
 *   The revision of the root account +1 is used as value.
 *   The assumption that the root node always contains the current revision.
 *   So it is also traceable if a branch or node has changed during the last transaction.
 * - Access parallel / concurrent
 *   Read accesses are executed in parallel.
 *   Write accesses are executed exclusively and block simultaneous read and write accesses.
 * - Transaction
 *   The write access is exclusive and uses flock + LOCK_EX / LOCK_UN
 * - Multi-functional effect
 *   In the first idea OPTIONS/PUT/PATCH/DELETE could only use unique entities.
 *   But there are advantages (also disadvantages) if a multiple scope is supported. 
 *   For example, change or delete the attributes of all matching entries.
 *   Therefore, the status 300 is also omitted.
 *   TODO: It is still open how OPTIONS should work.
 * - OPTIONS: Should return information about the storage and an entity but in the context of the storage,
 *   so no details about the entity, only the indirect statement about Allow, whether the entity exists or not.
 *   Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
 *   Storage-Revision: Revision
 *   Storage-Size: Bytes
 *   Storage-Expired: Timestamp
 *   Storage-Expiration: Seconds
 *   Last-Modified: Timestamp
 *   Allow: CONNECT, OPTIONS, GET, HEAD, CREATE, PUT, PATCH, DELETE
 *   If the entity does not exist:
 *   Allow: CONNECT, OPTIONS, CREATE, PUT, PATCH
 *   So only those methods are returned, which can be applied to the storage and the entity.
 * - HEAD: Works like OPTIONS, but the focus is the entity
 *   Same storage informations as with OPTIONS.
 *   Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
 *   Storage-Revision: Revision
 *   Storage-Size: Bytes
 *   Storage-Expired: Timestamp
 *   Storage-Expiration: Seconds
 *   Last-Modified: Timestamp
 *   If the (X)Path is not unique, the response is status 300.
 *   If the (X)Path is unique, the response is 200 and there is meta information about the entity.
 *   Last-Modified: Then refers to the entity and not to the storage.
 *   If the entity is not available the request is answered with 404.
 */
class Storage {

    /** Directory of the data storage */
    const DIRECTORY = "./data";

    /** Maximum number of files in data storage */
    const QUANTITY = 65535;

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
        $this->store   = Storage::DIRECTORY . "/" . $this->storage; 
        $this->xpath   = $xpath;
        $this->change  = false;
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
            header('Content-Type: none');
            header_remove("Content-Type"); 
            exit();
        }        
        Storage::cleanUp();
        return new Storage($storage, $xpath);
    }
    
    private function open() {
        
        if ($this->share !== null)
            return;
            
        touch($this->store);    
        $this->share = fopen($this->store, "c+");
        flock($this->share, LOCK_EX);

        if (filesize($this->store) <= 0) {
            fwrite($this->share,
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n" .
            "<data rev=\"0\"/>");
            rewind($this->share);
        }

        fseek($this->share, 0, SEEK_END); 
        $size = ftell($this->share);
        rewind($this->share);
        $this->xml = fread($this->share, $size);
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
        return $this->xml->xpath('/data[1]/@rev')[0];
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

        if ($this->xpath !== "/") {
            header("HTTP/1.0 400 Bad Request");
            header('Content-Type: none');
            header_remove("Content-Type"); 
            exit();
        }
        
        $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
        if (iterator_count($iterator) >= Storage::QUANTITY) {
            header("HTTP/1.0 507 Insufficient Storage");
            header('Content-Type: none');
            header_remove("Content-Type"); 
            exit();
        }
        
        if (file_exists($this->store)) {
            header("HTTP/1.0 202 Accepted");
        } else {
            header("HTTP/1.0 201 Created");
        }

        $this->open();

        header("Storage: " . $this->storage);
        header("Storage-Revision: " . $this->getRevision());
        header("Storage-Size: " . $this->getSize());
        header('Content-Type: none');
        header_remove("Content-Type"); 
        exit();
    }

    public function doOptions() {
        exit();
    }  

    public function doGet() {
    
        // Request:
        //     GET /<xpath> HTTP/1.0   
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Accept: text/plain
        // Response (If value of attribute or result of a function):
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes   
        //     Content-Length: Bytes
        //     Content-Type: text/plain        

        // Request:
        //     GET /<xpath> HTTP/1.0   
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Accept: application/xslt+xml
        // Response (If response is a partial XML structure):
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes   
        //     Content-Length: Bytes
        //     Content-Type: application/xslt+xml

        exit();
    }

    /**
     * TODO:
     *
     * The (x)path must be valid and can be created in the XML structure,
     * otherwise the request is answered with status 422 (Unprocessable Entity).
     */
    public function doCreate() {
        exit();
    }  

    /**
     * Adds to the specified path, a node or an attribute.
     * If the destination already exists, the method behaves like PATCH.
     * PUT expects an existing parent node (destination) to create a new node
     * or attribute. As parent (destination) the path until the last occurrence
     * of the slash or @ is interpreted. Only the last fragment after the last
     * occurrence of slash or @ is used as the node or attribute to be created.
     * Creating new complex branches seems tedious, but here PUT can insert
     * complex XML fragments and CREATE works like PUT, but can handle complex
     * paths. 
     * 
     * The Content-Type of the request defines the data type.
     *
     * Nodes supports text/plain and application/xslt+xml.
     *     text/plain
     * Inserts a text as content for the node (inner node).
     * If necessary, CDATA is used.
     *     application/xslt+xml
     * Replaces the node with an XML fragment (outer node).
     * Other content types are answered with status 415 (Unsupported Media Type).
     *       
     * Attributes supports only text/plain and replaces the value of the
     * attribute. If necessary, the value is escaped.
     * Other content types are answered with status 415 (Unsupported Media Type).
     *
     * The attributes ___rev / ___oid  are used internally and cannot be changed.
     * Write accesses cause the status 405. 
     */
    public function doPut() {
    
        // Request:
        //     PUT /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: Bytes
        //     Content-Type: application/xslt+xml
        // Request-Body:
        //     XML fragment for the node to be added

        // Request:
        //     PUT /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: Bytes
        //     Content-Type: text/plain
        // Request-Body:
        //     Value as plain text
        
        // Response:
        //     HTTP/1.0 201 Created
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes

        exit();    
    }

    /**
     * Replaces to the specified path, a node or an attribute.
     * The destination must exist, otherwise the request is answered with status
     * 404 (Not Found).
     * 
     * The Content-Type of the request defines the data type.
     *
     * Nodes supports text/plain and application/xslt+xml.
     *     text/plain
     * Inserts a text as content for the node (inner node).
     * If necessary, CDATA is used.
     *     application/xslt+xml
     * Replaces the node with an XML fragment (outer node).
     * Other content types are answered with status 415 (Unsupported Media Type).
     *       
     * Attributes supports only text/plain and replaces the value of the
     * attribute. If necessary, the value is escaped.
     * Other content types are answered with status 415 (Unsupported Media Type).
     *
     * The attributes ___rev / ___oid  are used internally and cannot be changed.
     * Write accesses cause the status 405. 
     */
    public function doPatch() {
    
        // Request:
        //     PATCH /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: 0 Bytes
        //     Content-Type: application/xslt+xml
        // Request-Body:
        //     XML fragment for the node to be added
        
        // Request:
        //     PATCH /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Content-Length: 0 Bytes
        //     Content-Type: text/plain
        // Request-Body:
        //     Value as plain text     
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Size: Bytes
        
        // Error Status:
        //    404 Destination does not exist
        //    405 Write access to attribute ___rev / __oid
        //    415 Content-Type is not supported

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

//set_error_handler("Storage::onError");
//set_exception_handler("Storage::onException");
//register_shutdown_function("Storage::onShutdown");

$storage = null;
if (isset($_SERVER["HTTP_STORAGE"]))
    $storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match("/^[0-9A-Z]{35}$/", $storage)) {
    header("HTTP/1.0 400 Bad Request");
    header('Content-Type: none');
    header_remove("Content-Type"); 
    exit();
}

$xpath = "/";
if (isset($_SERVER["PATH_INFO"]))
    $xpath = $_SERVER["PATH_INFO"];
$storage = Storage::share($storage, $xpath);

try {
    switch (strtoupper($_SERVER["REQUEST_METHOD"])) {
        case "CONNECT":
            $storage->doConnect();
            break;
        case "OPTIONS":
            $storage->doOptions();
            break;
        case "GET":
            $storage->doGet();
            break;
        case "CREATE":
            $storage->doCreate();
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
            header("Allow: CONNECT, OPTIONS, GET, CREATE, PUT, PATCH, DELETE");
            header('Content-Type: none');
            header_remove("Content-Type"); 
            exit();
    }
} finally {
    $storage->close();
}
?>