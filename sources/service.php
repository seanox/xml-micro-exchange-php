<?php
/**
 * LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
 * Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 * Diese Software unterliegt der Version 2 der Apache License.
 *
 * XMDS XML-Micro-Datasource
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
 * 
 *     Error Handling
 * Errors are communicated via the server status and the header 'Error'.
 * The header 'Error' contains only an error number, for security reasons no
 * details. The error number with details can be found in the log file of the
 * service.
 * 
 *     Security
 * This aspect was deliberately considered and implemented here only in a very
 * rudimentary form. Only the storage(-key) with a length of 36 characters can
 * be regarded as secret.  
 * For further security the approach of Basic Authentication, Digest Access
 * Authentication and/or Server/Client certificates is followed, which is
 * configured outside of the XMDS (XML-Micro-Datasource) at the web server.
 *
 *     CONNECT
 * This method is used to initiate access to a storage.
 * A storage is a temporary XML construct.
 * It is based on a cryptic ID (storage name), which must be transmitted with
 * every request. This is similar to the header host for virtual servers.  
 * The storage is only a temporary meeting place. Any client who knows the path
 * can access, use and design it.
 * There are no rules, only the clients know the rules.
 * A storage expires with all information if it is not used (read/write).
 *     IMPORTANT
 * CONNECT is no HTTP standard.
 * As an alternative, OPTIONS can be used without or with root path.
 * In this case OPTIONS will hand over the work to CONNECT.
 *
 *     OPTIONS
 * Determines informations about the storage and an (x)path destination.
 * A storage is based on an XML construct.
 * It manages data as entities and/or attributes of entities.
 * The OPTIONS method only determines information about the storage and
 * entities, so what is the status and what can be done. Attributes are not
 * supported. Storage and entity information is transmitted as response headers.
 * The response therefore has no response body. If the requested entity is not
 * unique, only storage information is determined.
 *     IMPORTANT
 * Without path or a root path behaves like CONNECT, because CONNECT is no HTTP
 * standard. In this case OPTIONS will hand over the work to CONNECT.
 *  
 *     GET
 * TODO:
 *
 *     PUT
 * Adds to the specified (x)path, a node or an attribute.
 * If the destination already exists, the method behaves like PATCH.
 * PUT expects an existing parent node (destination) to create a new node or
 * attribute. As parent (destination) the path until the last occurrence of the
 * slash or @ is interpreted. Only the last fragment after the last occurrence
 * of slash or @ is used as the node or attribute to be created. Creating new
 * complex branches seems tedious, but here PUT can insert complex XML fragments. 
 *
 *     PATCH
 * TODO:
 *
 *     DELETE 
 * TODO:
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
 * - OPTIONS: Should return information about the storage and an entity but in the context of the storage,
 *   so no details about the entity, only the indirect statement about Allow, whether the entity exists or not.
 *   Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
 *   Storage-Revision: Revision
 *   Storage-Size: Bytes
 *   Storage-Expired: Timestamp
 *   Storage-Expiration: Seconds
 *   Storage-Last-Modified: Timestamp
 *   Allow: CONNECT, OPTIONS, GET, PUT, PATCH, DELETE
 *   If the entity does not exist:
 *   Allow: CONNECT, OPTIONS, PUT, PATCH
 *   So only those methods are returned, which can be applied to the storage and the entity.
 * - META: Works like OPTIONS, but the focus is the entity
 *   Same storage informations as with OPTIONS.
 *   Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
 *   Storage-Revision: Revision
 *   Storage-Size: Bytes
 *   Storage-Expired: Timestamp
 *   Storage-Expiration: Seconds
 *   Storage-Last-Modified:
 *   Entity-Identifier: Number
 *   Entity-Revision: Revision  
 *   Entity-Last-Modified: Timestamp 
 *   If the (X)Path is not unique, the response is status 300 and without headers for the entity. 
 *   If the (X)Path is unique, the response is 200 and there is meta information about the entity.
 *   If the entity is not available the request is answered with 404 and without headers for the entity.
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
    
    /**
     * Optional CORS response headers as associative array.
     *     e.g. Allow-Origin, Allow-Credentials, Allow-Methods, Allow-Headers,
     *     Max-Age, Expose-Headers 
     * The prefix Access-Control is added automatically.
     *     e.g. Allow-Origin -> Access-Control-Allow-Origin
     */
    const CORS = ["Allow-Origin" => "*"];
    
    private $storage;
    
    private $store;
    
    private $share;

    private $xml;

    private $xpath;

    private $revision;
    
    private $serial;

    private $unique;

    private function __construct($storage, $xpath) {

        $this->storage = $storage;
        $this->store   = Storage::DIRECTORY . "/" . $this->storage; 
        $this->xpath   = $xpath;
        $this->change  = false;
        $this->unique  = Storage::uniqueId();  
        $this->serial  = 0;
    }

    /**
     * Return a unique ID related to the request.
     * @return string unique ID related to the request
     */
    private static function uniqueId() {
        
        // The method is based on time, network port and the assumption that a
        // port is not used more than once at the same time. On fast platforms,
        // however, the time factor is uncertain because the time from calling
        // the method is less than one millisecond. This is ignored here,
        // assuming that the port reassignment is greater than one millisecond.

        // Structure of the Unique-Id [? MICROSECONNECTORS][4 PORT]
        $unique = base_convert($_SERVER["REMOTE_PORT"], 10, 36);
        $unique = str_pad($unique, 4, 0, STR_PAD_LEFT);
        $unique = base_convert(microtime(true) *10000, 10, 36) . $unique;
        return strtoupper($unique);
    }
    
    /** Cleans up all files that have exceeded the maximum idle time. */
    private static function cleanUp() {

        if (!is_dir(Storage::DIRECTORY))
            return;
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
            Storage::addHeaders(400, "Bad Request");
            exit();
        }        
        Storage::cleanUp();
        if (!file_exists(Storage::DIRECTORY))
            mkdir(Storage::DIRECTORY, true);
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
            "<data ___rev=\"0\"/>");
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
        if (!$this->revision)
            $this->revision = $this->xml->xpath('/data[1]/@___rev')[0];    
        return $this->revision;
    }

    /**
     * Creates a unique incremental ID.
     * @return string unique incremental ID
     */
    private function getSerial() {
        return $this->unique . ":" . $this->serial++;
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
            Storage::addHeaders(400, "Bad Request");
            exit();
        }
        
        $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
        if (iterator_count($iterator) >= Storage::QUANTITY) {
            Storage::addHeaders(507, "Insufficient Storage");
            exit();
        }

        $response = [201, "Created"];        
        if (file_exists($this->store))
            $response = [202, "Accepted"];

        $this->open();

        Storage::addHeaders($response[0], $response[1], [
            "Storage" => $this->storage,
            "Storage-Revision" => $this->getRevision(),
            "Storage-Size" => $this->getSize(),
        ]);
        exit();
    }

    public function doOptions() {

        // Without path or a root path behaves like CONNECT,
        // because CONNECT is no HTTP standard.
        if (empty($this->xpath)
              || strcmp($this->xpath, "/") === 0) {
            $this->doConnect();      
            exit();
        }

        // TODO:
              
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
     * Adds to the specified path, a node or an attribute.
     * If the destination already exists, the method behaves like PATCH.
     * PUT expects an existing parent node (destination) to create a new node
     * or attribute. As parent (destination) the path until the last occurrence
     * of the slash or @ is interpreted. Only the last fragment after the last
     * occurrence of slash or @ is used as the node or attribute to be created.
     * Creating new complex branches seems tedious, but here PUT can insert
     * complex XML fragments. 
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
    
    public static function addHeaders($status, $message, $headers = null) {
    
        header(trim("HTTP/1.0 $status $message"));
        
        if (!empty(Storage::CORS))
            foreach (Storage::CORS as $key => $value)
                header("Access-Control-$key: $value");
        
        if (!empty($headers))
            foreach ($headers as $key => $value)
                header(trim("$key: $value"));
        
        $headers = array_change_key_case(empty($headers) ? [] : $headers, CASE_LOWER);
        // Header Content-Type is not sent by default.
        // PHP has a habit of adding the header automatically, but this is not wanted here.
        // It is removed somewhat unconventionally.
        if (!array_keys($headers, "context-type")) {
            header("Content-Type: none");
            header_remove("Content-Type"); 
        }
        // When responding to an error, the default Allow header is added.
        // But only if no Allow header was passed.
        // So the header does not always have to be added manually.
        if ($status >= 400
                && !array_keys($headers, "allow"))
            header("Allow: CONNECT, OPTIONS, GET, PUT, PATCH, DELETE");        
    }

    public static function onError($error, $message, $file, $line, $vars = array()) {

        $unique = "#" . Storage::uniqueId();
        $message = "$error: $message" . PHP_EOL . "\tat $file $line";
        $time = time();
        file_put_contents(date("Ymd", $time) . ".log", date("Y-m-d H:i:s", $time) . " $unique $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
        if (!headers_sent())
            Storage::addHeaders(500, "Internal Server Error", ["Error" => $unique]);
        exit;
    }
    
    public static function onException($exception) {
        Storage::onError(get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }
}

set_error_handler("Storage::onError");
set_exception_handler("Storage::onException");

$storage = null;
if (isset($_SERVER["HTTP_STORAGE"]))
    $storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match("/^[0-9A-Z]{35}$/", $storage)) {
    Storage::addHeaders(400, "Bad Request");
    exit();
}

$xpath = "/";
if (isset($_SERVER["PATH_INFO"]))
    $xpath = $_SERVER["PATH_INFO"];
if (empty($xpath))
    $xpath = "/";        
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
            Storage::addHeaders(405, "Method Not Allowed");
            exit();
    }
} finally {
    $storage->close();
}
?>