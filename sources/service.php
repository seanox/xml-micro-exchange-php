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
 * Initiates the use of a datasource.
 * A datasource (storage) is a temporary XML construct. It is based on a
 * cryptic ID (storage name) and optionally the name of the root element, which
 * must be transmitted with  every request.
 * This is similar to the header host for virtual servers.
 * The storage is only a temporary place for data exchange. Any client who
 * knows the path can access, use and design it.
 * There are no rules, only the clients know the rules.
 * A storage expires with all information if it is not used (read/write).
 * 
 * In addition, OPTIONS can also be used as an alternative to CONNECT, because
 * CONNECT is not an HTTP standard. For this purpose OPTIONS without XPath, but
 * with context path if necessary, is used. In this case OPTIONS will hand over
 * the work to CONNECT.
 *
 *     OPTIONS
 * Selects one or more elements and also attributes to an XPath and returns
 * meta information about them and the datasource in general.
 * The meta information is returned as response headers that differ in scope
 * and range from XPath as selector for elements and attributes.
 * Primarily the method is used to check what scope an XPath has as selector
 * and is comparable to EXISTS and FILE_INFO in the filesystem. 
 * 
 * In addition, OPTIONS can also be used as an alternative to CONNECT, because
 * CONNECT is not an HTTP standard. For this purpose OPTIONS without XPath, but
 * with context path if necessary, is used. In this case OPTIONS will hand over
 * the work to CONNECT.
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
 * pseudo-elements
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

    private $root;
    
    private $store;
    
    private $share;

    private $xml;

    private $xpath;

    /** Revision of the storage at the start of the request */
    private $revision;
    
    /** Serial related to the request */
    private $serial;

    /** Unique ID related to the request */
    private $unique;

    const PATTERN_HEADER_STORAGE = "/^([0-9A-Z]{35})(?:\s+(\w+)){0,1}$/";

    /**
     * Pattern to determine the structure of XPath expressions for attributes
     *     Group 0. Full match	
     *     Group 1. XPath
     *     Group 2.	Attribute
     */    
    const PATTERN_XPATH_ATTRIBUTE = "/^(\/.*?)\/{0,1}(?<=\/)(?:@|attribute::)(\w+)$/i";

    /**
     * Pattern to determine the structure of XPath expressions for pseudo elements
     *     Group 0. Full match	
     *     Group 1. XPath
     *     Group 2.	Attribute
     */    
    const PATTERN_XPATH_PSEUDO = "/^(\/.*?)(?:::(before|after|first|last)){0,1}$/i";

    private function __construct($storage, $root, $xpath) {

        $this->storage = $storage;
        $this->root    = $root ? $root : "data";
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
        $unique = base_convert(round(microtime(true) *1000), 10, 36) . $unique;
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
    
    static function share($storage, $xpath) {

        if (!preg_match(Storage::PATTERN_HEADER_STORAGE, $storage)) {
            Storage::addHeaders(400, "Bad Request");
            exit();
        }        

        $root = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$2", $storage);
        $storage = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$1", $storage);    

        Storage::cleanUp();
        if (!file_exists(Storage::DIRECTORY))
            mkdir(Storage::DIRECTORY, true);
        $storage = new Storage($storage, $root, $xpath);
        if ($storage->exists()) {
            $storage->open();
            // Safe is safe, if not the default 'data'' is used,
            // the name of the root element must be known.
            // Otherwise the request is quit with status 404 and terminated.
            if (($root ? $root : "data") != $storage->xml->getName()) {
                Storage::addHeaders(404, "Resource Not Found");
                exit();
            }
        }
        return $storage; 
    }

    private function exists() {
        return file_exists($this->store)
                && filesize($this->store) > 0;
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
            "<" . $this->root . " ___rev=\"0\" ___oid=\"" . $this->getSerial() . "\"/>");
            rewind($this->share);
        }

        fseek($this->share, 0, SEEK_END); 
        $size = ftell($this->share);
        rewind($this->share);
        $this->xml = fread($this->share, $size);
        $this->xml = new SimpleXMLElement($this->xml);
    } 
    
    function close() {

        if ($this->share == null)
            return;

        ftruncate($this->share, 0);
        rewind($this->share);
        fwrite($this->share, $this->xml->asXML());    

        flock($this->share, LOCK_UN);
        fclose($this->share);

        $this->share = null;
        $this->xml = null;
    }
    
    /**
     * Determines the current revision of the storage.
     * The revision is related to the current request and is used for all data
     * changes in this context.
     * @return integer current revision of the storage
     */
    private function getRevision() {
    
        $this->open();
        if (!$this->revision)
            $this->revision = $this->xml->xpath("/" . $this->root . "[1]/@___rev")[0];    
        return $this->revision;
    }

    /**
     * Creates a unique incremental ID.
     * @return string unique incremental ID
     */
    private function getSerial() {
        return $this->unique . ":" . $this->serial++;
    }

    /**
     * Determines the current size of the storage with the current data and can
     * therefore differ from the size in the file system.
     * @return integer current size of the storage
     */
    private function getSize() {
    
        if ($this->xml !== null)
            return strlen($this->xml->asXML());
        if ($this->share !== null)
            return filesize($this->share);
        return filesize($this->store);
    }

    private function getExpiration($format = null) {

        $date = new DateTime();
        $date->add(new DateInterval("PT" . Storage::TIMEOUT . "S"));
        return $format ? $date->format($format) : $date->getTimestamp();
    }

    function doConnect() {

        // Request:
        //     CONNECT / HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ

        // Request:
        //     CONNECT / HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ root

        // Response:
        //     HTTP/1.0 201 Created
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision (number)   
        //     Storage-Space: Total/Used (bytes)
        //     Storage-Last-Modified: Timestamp (RFC822)
        //     Storage-Expiration: Timeout/Timestamp (seconds/RFC822)

        // Response:
        //     HTTP/1.0 202 Accepted
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision (number)   
        //     Storage-Space: Total/Used (bytes)
        //     Storage-Last-Modified: Timestamp (RFC822)
        //     Storage-Expiration: Timeout/Timestamp (seconds/RFC822)

        // Response codes/ behavior:
        //     HTTP/1.0 400 Bad Request
        // - TODO:
        //     HTTP/1.0 507 Insufficient Storage   
        // - TODO:  
        
        // Only requests without XPath are allowed, otherwise status 400 Bad Request
        // PATH_INFO is used as XPath, not the request URI.
        // If the storage is full, the response is status 507 Insufficient Storage
        // An exact 35 characters long storage must be specified (pattern: [0-9A-Z]{35})
        // The response can be status 201 if the storage was newly created.
        // The answer can be status 202 if the storage already exists.

        if (!empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request");
            exit();
        }
        
        $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
        if (iterator_count($iterator) >= Storage::QUANTITY) {
            Storage::addHeaders(507, "Insufficient Storage");
            exit();
        }

        $response = [201, "Created"];    
        if (!$this->exists())
            $this->open();    
        else $response = [202, "Accepted"];
        
        Storage::addHeaders($response[0], $response[1], [
            "Storage" => $this->storage,
            "Storage-Revision" => $this->getRevision(),
            "Storage-Space" => Storage::SPACE . "/" . $this->getSize(),
            "Storage-Last-Modified" => date(DateTime::RFC822),
            "Storage-Expiration" => Storage::TIMEOUT . "/" . $this->getExpiration(DateTime::RFC822)
        ]);

        // The function and the reponse are complete.
        // The storage can be closed and the requests can be terminated.
        $this->close();
        exit;
    }

    function doOptions() {

        // Without XPath (PATH_INFO) behaves like CONNECT,
        // because CONNECT is no HTTP standard.
        // The function call is executed and the request is terminated.
        if (empty($this->xpath))
            $this->doConnect();      

        // TODO:
    }  

    function doGet() {
    
        // Request:
        //     GET /<xpath> HTTP/1.0   
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Accept: text/plain
        // Response (If value of attribute or result of a function):
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Space: Total/Used (in bytes)
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
        //     Storage-Space: Total/Used (in bytes)
        //     Content-Length: Bytes
        //     Content-Type: application/xslt+xml
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
    function doPut() {
    
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
        //     HTTP/1.0 204 No Content
        //     Elements-Affected: ... (list of OIDs)
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision (number)   
        //     Storage-Space: Total/Used (bytes)
        //     Storage-Last-Modified: Timestamp (RFC822)
        //     Storage-Expiration: Timeout/Timestamp (seconds/RFC822)

        // Response codes/ behavior:
        //     204 No Content
        // - Attributes successfully created or set   
        //     400 Bad Request
        // - XPath is missing 
        //     404 Resource Not Found
        // - Storage is invalid 
        //     415 Unsupported Media Type
        // - Attribute request without Content-Type text/plain
        
        // In any case an XPath is required for a valid request.
        if (empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request");
            exit();
        }

        // Without existing storage the request is not valid.
        if (!$this->exists()) {
            Storage::addHeaders(404, "Resource Not Found");
            exit();
        }

        // XPath can address nodes and attributes.
        // If the XPath ends with /attribute::<attribute> or /@<attribute> an
        // attribute is expected, in all other cases a node.

        if (preg_match(Storage::PATTERN_XPATH_ATTRIBUTE, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL)) {

            // For attributes only the content-type text/plain is supported and
            // required, for other content-types no conversion exists.
            if (!isset($_SERVER["CONTENT_TYPE"])
                    || strcasecmp($_SERVER["CONTENT_TYPE"], "text/plain") !== 0) {
                Storage::addHeaders(415, "Unsupported Media Type");
                exit();                
            }

            $xpath = $matches[1];
            $attribute = $matches[2];

            $nodes = $this->xml->xpath($xpath);
            if (!empty($nodes)) {
                $this->revision = $this->getRevision() +1;
                $value = file_get_contents('php://input');
                $identities = []; 
                foreach ($nodes as $node) {
                    $identities[] = $node["___oid"]; 
                    $node[attribute] = $value;
                    $node["___rev"] = $this->getRevision();
                }
                header("Elements-Affected: " . join(" ", $identities));
            }
            
            Storage::addHeaders(204, "No Content", [
                "Storage" => $this->storage,
                "Storage-Revision" => $this->getRevision(),
                "Storage-Space" => Storage::SPACE . "/" . $this->getSize(),
                "Storage-Last-Modified" => date(DateTime::RFC822),
                "Storage-Expiration" => Storage::TIMEOUT . "/" . $this->getExpiration(DateTime::RFC822)                
            ]);
             
            return;
        }

        if (preg_match(Storage::PATTERN_XPATH_PSEUDO, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL)) {

            $xpath = $matches[1];
            $pseudo = $matches[2];

            if (!empty($pseudo)) {

                // Pseudo elements can be used to insert an XML substructure
                // relative to the selected element. Therefore the content type
                // application/xslt+xml is required. Other Content-Type headers
                // are not supported.   
                if (!isset($_SERVER["CONTENT_TYPE"])
                        || strcasecmp($_SERVER["CONTENT_TYPE"], "application/xslt+xml") !== 0) {
                    Storage::addHeaders(415, "Unsupported Media Type");
                    exit();                
                }
                
                // The request body must also be a valid XML structure,
                // otherwise the request will be acknowledged with an error.
                $xml = file_get_contents('php://input');

            } else {

                if (isset($_SERVER["CONTENT_TYPE"])) {
                    if (strcasecmp($_SERVER["CONTENT_TYPE"], "text/plain") === 0) {

                        // TODO:

                    } else if (strcasecmp($_SERVER["CONTENT_TYPE"], "application/xslt+xml") === 0) {

                        // The request body must also be a valid XML structure,
                        // otherwise the request will be acknowledged with an error.
                        $input = file_get_contents('php://input');
                        $input = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><data>$input</data>";
                        $xml = new DOMDocument();
                        $xml->loadXML($input);

                        exit;
                    }
                }
            }

            // Whatever happens, this place should never be reached.
            throw new Exception("Unexpected exception during runtime");
        }
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
    function doPatch() {
    
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
        //     Storage-Space: Total/Used (in bytes)
        
        // Error Status:
        //    404 Destination does not exist
        //    405 Write access to attribute ___rev / __oid
        //    415 Content-Type is not supported
    }

    function doDelete() {
    
        // Request:
        //     DELETE /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ 
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXZ
        //     Storage-Revision: Revision   
        //     Storage-Space: Total/Used (in bytes)
    }
    
    static function addHeaders($status, $message, $headers = null) {
    
        header(trim("HTTP/1.0 $status $message"));
        
        if (!empty(Storage::CORS))
            foreach (Storage::CORS as $key => $value)
                header("Access-Control-$key: $value");
        
        if (!empty($headers))
            foreach ($headers as $key => $value)
                header(trim("$key: $value"));

        header("Execution-Time: " . round((microtime(true) -$_SERVER["REQUEST_TIME_FLOAT"]) *1000)); 
        
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

    static function onError($error, $message, $file, $line, $vars = array()) {

        $unique = "#" . Storage::uniqueId();
        $message = "$error: $message" . PHP_EOL . "\tat $file $line";
        $time = time();
        file_put_contents(date("Ymd", $time) . ".log", date("Y-m-d H:i:s", $time) . " $unique $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
        if (!headers_sent())
            Storage::addHeaders(500, "Internal Server Error", ["Error" => $unique]);
        exit;
    }
    
    static function onException($exception) {
        Storage::onError(get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }
}

set_error_handler("Storage::onError");
set_exception_handler("Storage::onException");

$storage = null;
if (isset($_SERVER["HTTP_STORAGE"]))
    $storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match(Storage::PATTERN_HEADER_STORAGE, $storage)) {
    Storage::addHeaders(400, "Bad Request");
    exit();
}

// Except for CONNECT and OPTIONS, all requests expect an XPath derived from
// PATH_INFO which must start as XPath with a slash.
// CONNECT and OPTIONS do not use an (X)Path to establish a storage.
$xpath = "";
if (isset($_SERVER["PATH_INFO"]))
    $xpath = $_SERVER["PATH_INFO"];
if (substr($xpath, 0, 1) !== "/"
        && !in_array(strtoupper($_SERVER["REQUEST_METHOD"]), ["OPTIONS", "CONNECT"]))
    $xpath = "/" . $xpath;        
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