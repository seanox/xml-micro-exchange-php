<?php
/**
 * LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
 * Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 * Diese Software unterliegt der Version 2 der Apache License.
 *
 * XMEX XML-Micro-Exchange
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
 *     DESCRIPTION
 * 
 * XML-Micro-Exchange is a volatile RESTful micro datasource.
 * It is designed for easy communication and data exchange of web applications
 * and for IoT.  
 * The XML based datasource is volatile and lives through continuous use and
 * expires through inactivity. They are designed for active and near real-time
 * data exchange but not as a real-time capable long-term storage.
 * Compared to a JSON storage, this datasource supports more dynamics, partial
 * data access, data transformation, and volatile short-term storage. 
 * 
 *     TERMS / WORDING
 * 
 *         XMEX / XML-Micro-Exchange
 * Name of the project and the corresponding abbreviation
 * 
 *         Datasource
 * XML-Micro-Exchange is a data service that manages different data areas.
 * The entirety, so the service itself, is the datasource.
 * Physically this is the data directory.
 * 
 *         Storage
 * The data areas managed by the XML Micro-Exchange as a data service are
 * called storage areas. A storage area corresponds to an XML file in the data
 * directory.
 * 
 *         Storage Identifier
 * Each storage has an identifier, the Storage Identifier.
 * The Storage Identifier is used as the filename of the corresponding XML file
 * and must be specified with each request so that the datasource uses the
 * correct storage.
 * 
 *         Element(s)
 * The content of the XML file of a storage provide the data as object or tree
 * structure. The data entries are called elements. 
 * Elements can enclose other elements.
 * 
 *         Attribute(s)
 * Elements can also contain direct values in the form of attributes.
 * 
 *         XPath
 * XPath is a notation for accessing and navigating the XML data structure.
 * An XPath can be an axis or a function.
 * 
 *         XPath Axis
 * XPath axes address or select elements or attributes.
 * The axes can have a multidimensional effect.
 * 
 *         XPath Axis Pseudo Elements
 * For PUT requests it is helpful to specify a relative navigation to an XPath
 * axis. For example first, last, before, after. This extension of the notation
 * is supported for PUT requests and is added to an XPath axis separated by two
 * colons at the end (e.g. /root/element::end - means put in element as last).
 * 
 *         XPath Function
 * The XPath notation also supports functions that can be used in combination
 * with axes and standalone for dynamic data requests. In combination with
 * XPath axes, the addressing and selection of elements and attributes can be
 * made dynamic.
 * 
 *        Revision
 * Every change in a storage is expressed as a revision.
 * This should make it easier for the client to determine whether data has
 * changed, even for partial requests.
 * The revision is a counter of changes per request, without any claim of
 * version management of past revisions.
 * It starts with initial revision 0 when a storage is created on the first
 * call. The first change already uses revision 1. 
 * 
 * Each element uses a revision in the read-only attribute ___rev, which, as
 * with all parent revision attributes, is automatically incremented when it
 * changes.
 * A change can affect the element itself or the change to its children.
 * Because the revision is passed up, the root element automatically always
 * uses the current revision.
 * 
 * Changes are: PUT, PATCH, DELETE
 * 
 * Write accesses to attribute ___rev are accepted with status 204, will have
 * no effect from then on and are therefore not listed in the response header
 * Storage-Effects. 
 * 
 *       UID
 * Each element uses a unique identifier in the form of the read-only attribute
 * ___uid. The unique identifier is automatically created when an element is
 * put into storage and never changes.
 * If elements are created or modified by a request, the created or affected
 * unique identifiers are sent to the client in the response header
 * Storage-Effects.
 * 
 * The UID uses an alphanumeric format based on radix 36 which, when converted
 * into a number, gives the timestamps of the creation in milliseconds since
 * 01/01/2000.
 * The UID is thus also sortable and provides information about the order in
 * which elements are created.
 * 
 * Write accesses to attribute ___uid are accepted with status 204, will have
 * no effect from then on and are therefore not listed in the response header
 * Storage-Effects. 
 * 
 *     Transaction / Concurrent Access
 * In the first version of XML Micro-Exchange all requests to a storage use
 * flock + LOCK_EX / LOCK_UN, that should change later.
 * So that also simultaneous accesses are supported, but no dirty reading.
 * 
 * TODO:    
 * 
 *     ERROR HANDLING
 * Errors are communicated via the server status and the header 'Error'.
 * The header 'Error' contains only an error number, for security reasons no
 * details. The error number with details can be found in the log file of the
 * service.
 * 
 *     SECURITY
 * This aspect was deliberately considered and implemented here only in a very
 * rudimentary form. Only the storage(-key) with a length of 36 characters can
 * be regarded as secret.  
 * For further security the approach of Basic Authentication, Digest Access
 * Authentication and/or Server/Client certificates is followed, which is
 * configured outside of the XMDS (XML-Micro-Datasource) at the web server.
 *
 * TODO:
 */
class Storage {

    /** Directory of the data storage */
    const DIRECTORY = "./data";

    /** Maximum number of files in data storage */
    const QUANTITY = 65535;

    /** 
     * Maximum data size of files in data storage in bytes.
     * The value also limits the size of the requests(-body).
     */
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

    /**
     * Pattern to determine a HTTP request
     *     Group 0. Full match
     *     Group 1. Method
     *     Group 2. URI
     *     Group 3. Protocoll
     */    
    const PATTERN_HTTP_REQUEST = "/^([A-Z]+)\s+(.+)\s+(HtTP\/\d+(?:\.\d+)*)$/i";

    const PATTERN_HTTP_REQUEST_URI = "/^(.*?)[!#$*:?@|~]+(.*)$/i";

    /**
     * Pattern for the Storage header
     *     Group 0. Full match
     *     Group 1. Storage
     *     Group 2. Name of the root element (optional)
     */    
    const PATTERN_HEADER_STORAGE = "/^([0-9A-Z]{36})(?:\s+(\w+)){0,1}$/";

    /**
     * Pattern to determine the structure of XPath axis expressions for attributes
     *     Group 0. Full match
     *     Group 1. XPath axis
     *     Group 2. Attribute
     */    
    const PATTERN_XPATH_ATTRIBUTE = "/^(.*?)\/{0,}(?<=\/)(?:@|attribute::)(\w+)$/i";

    /**
     * Pattern to determine the structure of XPath axis expressions for pseudo elements
     *     Group 0. Full match
     *     Group 1. XPath axis
     *     Group 2. Attribute
     */    
    const PATTERN_XPATH_PSEUDO = "/^(.*?)(?:::(before|after|first|last)){0,1}$/i";

    /**
     * Pattern as indicator for XPath functions
     * Assumption for interpretation: Slash and dot are indications of an axis
     * notation, the round brackets can be ignored, the question remains, if
     * the XPath starts with an axis symbol, then it is an axis, with other
     * characters at the beginning must be a function.
     *     Group 0. Full match
     *     Group 1. XPath function
     */
    const PATTERN_XPATH_FUNCTION = "/^(\(*[a-z].*)$/i";

    const CONTENT_TYPE_TEXT = "text/plain";
    const CONTENT_TYPE_XPATH = "text/xpath";
    const CONTENT_TYPE_XML = "application/xslt+xml";

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
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid storage identifier"]);
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
            if (($root ? $root : "data") != $storage->xml->firstChild->nodeName) {
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
            "<" . $this->root . " ___rev=\"0\" ___uid=\"" . $this->getSerial() . "\"/>");
            rewind($this->share);
        }

        fseek($this->share, 0, SEEK_END); 
        $size = ftell($this->share);
        rewind($this->share);
        $this->xml = new DOMDocument();
        $this->xml->loadXML(fread($this->share, $size));
    } 
    
    function close() {

        if ($this->share == null)
            return;

        // The size of the storage is limited by Storage::SPACE because it is a
        // volatile micro datasource for short-term data exchange. 
        // Exceeding it leads to the status xxx.
        $output = $this->xml->saveXML();
        if (strlen($output) <= Storage::SPACE) {
            ftruncate($this->share, 0);
            rewind($this->share);
            fwrite($this->share, $output);    
        } else {
            header_remove();
            Storage::addHeaders(413, "Payload Too Large");
            exit();
        }

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
            $this->revision = $this->xml->firstChild->getAttribute("___rev");
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
            return strlen($this->xml->saveXML());
        if ($this->share !== null)
            return filesize($this->share);
        return filesize($this->store);
    }

    private function getExpiration($format = null) {

        $date = new DateTime();
        $date->add(new DateInterval("PT" . Storage::TIMEOUT . "S"));
        return $format ? $date->format($format) : $date->getTimestamp();
    }

    private static function updateNodeRevision($node, $revision) {

        while ($node->nodeType == XML_ELEMENT_NODE) {
            $node->setAttribute("___rev", $revision);
            $node = $node->parentNode;
        }
    }

    /**
     * CONNECT initiates the use of a storage.
     * A storage is a volatile XML construct that is used via a datasource URL.
     * The datasource managed several independent storages.
     * Each storage has a name specified by the client, which must be sent with
     * each request. This is similar to the header host for virtual servers.
     * Optionally, the name of the root element can also be defined by the
     * client.
     * 
     * Each client can create a new storage at any time.
     * Communication is established when all parties use the same name.
     * There are no rules, only the clients know the rules.
     * A storage expires with all information if it is not used (read/write).
     * 
     * In addition, OPTIONS can also be used as an alternative to CONNECT,
     * because CONNECT is not an HTTP standard. For this purpose OPTIONS
     * without XPath, but with context path if necessary, is used. In this case
     * OPTIONS will hand over the work to CONNECT.
     * 
     *     Request:
     * CONNECT / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * 
     *     Request:
     * CONNECT / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root
     * 
     *    Response:
     * HTTP/1.0 201 Created
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number) 
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
     * 
     *     Response:
     * HTTP/1.0 202 Accepted
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
     * 
     *     Response codes / behavior:
     *         HTTP/1.0 201 Resource Created
     * - Response can be status 201 if the storage was newly created
     *         HTTP/1.0 202 Accepted
     * - Response can be status 202 if the storage already exists#
     *         HTTP/1.0 400 Bad Request
     * - Requests without XPath are responded with status 400 Bad Request
     * - Requests with a invalid Storage header are responded with status 400
     *   Bad Request, exactly 36 characters are expected - Pattern [0-9A-Z]{36}
     * - XPath is used from PATH_INFO + QUERY_STRING, not the request URI
     *         HTTP/1.0 404 Resource Not Found   
     * - Only mentioned here for completeness.
     *   Occurs when the storage exists but the name of the root element does
     *   not match.  
     *         HTTP/1.0 507 Insufficient Storage
     * - Response can be status 507 if the storage is full
     */
    function doConnect() {

        if (!empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
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

    /**
     * OPTIONS is used to request the functions to an XPath, which is responded
     * with the Allow header.
     * This method distinguishes between XPath axis and XPath function and uses
     * different Allow headers. Also the existence of the target on an XPath
     * axis has an influence on the response.
     * The method will not use state 404 in relation to non-existing targets,
     * but will offer the methods CONNECT, OPTIONS, PUT via Allow-Header.
     * If the XPath is a function, it is executed and thus validated, but
     * without returning the result.
     * Faulty XPath will cause the status 400.
     *
     * In addition, OPTIONS can also be used as an alternative to CONNECT,
     * because CONNECT is not an HTTP standard. For this purpose OPTIONS
     * without XPath, but with context path if necessary, is used. In this case
     * OPTIONS will hand over the work to CONNECT.
     * 
     * TODO:
     */
    function doOptions() {

        // Without XPath (PATH_INFO) behaves like CONNECT,
        // because CONNECT is no HTTP standard.
        // The function call is executed and the request is terminated.
        if (empty($this->xpath))
            $this->doConnect();      

        // Without existing storage the request is not valid.
        if (!$this->exists()) {
            Storage::addHeaders(404, "Resource Not Found");
            exit();
        }            

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
            exit();
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $allow = "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE";

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            $allow = "OPTIONS, GET, POST";
            $result = (new DOMXpath($this->xml))->evaluate($this->xpath); 
            if (!empty(libxml_get_errors())) {
                $message = "Invalid XPath Function";
                if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
                exit();
            }
            $allow = "CONNECT, OPTIONS, GET, POST";
        } else {
            $targets = (new DOMXpath($this->xml))->query($this->xpath);
            if (!empty(libxml_get_errors())) {
                $message = "Invalid XPath Axis";
                if (Storage::fetchLastXmlErrorMessage())
                    $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
                exit();
            }
            if ($targets && !empty($targets) && $targets->length > 0) {
                $serials = [];
                foreach ($targets as $target) {
                    if ($target instanceof DOMAttr)
                        $target = $target->parentNode;
                    if ($target instanceof DOMElement)
                        $serials[] = $target->getAttribute("___uid");
                }
                if (!empty($serials))
                    header("Storage-Effects: " . join(" ", $serials));
                $allow = "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE";

            } else $allow = "CONNECT, OPTIONS, PUT";
        }

        Storage::addHeaders(204, "No Content", [
            "Storage" => $this->storage,
            "Storage-Revision" => $this->getRevision(),
            "Storage-Space" => Storage::SPACE . "/" . $this->getSize(),
            "Storage-Last-Modified" => date(DateTime::RFC822),
            "Storage-Expiration" => Storage::TIMEOUT . "/" . $this->getExpiration(DateTime::RFC822)  ,
            "Allow" => $allow
        ]);

        // The function and the reponse are complete.
        // The storage can be closed and the requests can be terminated.
        $this->close();
        exit;        
    }  

    /**
     * GET is another way to retrieve data about XPath axes and functions.
     * For this, the XPath axes or functions is sent with URI.
     * Depending on whether the request is an XPath axis or an XPath function,
     * different Content-Type are used for the response.
     * 
     *     XPath axes
     * Conent-Type: application/xslt+xml
     * When the XPath axis addresses one target, the addressed target is the
     * root element of the returned XML structure
     * If the XPath addresses multiple targets, their XML structure is combined
     * in the root element collection.
     * 
     *     XPath function
     * Conent-Type: text/plain
     * The result of XPath functions is returned as plain text.
     * Decimal results use float, booleans the values true and false.
     * 
     *     Request:
     * GET /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     *  
     *     Response:
     * HTTP/1.0 200 Success
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)   
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
     * Content-Length: (bytes)
     *     Response-Body:
     * The result of the XPath request
     * 
     *     Response codes / behavior:
     *         HTTP/1.0 200 Success
     * - Request was successfully executed
     *         HTTP/1.0 400 Bad Request
     * - XPath is malformed
     *         HTTP/1.0 404 Resource Not Found
     * - Storage is invalid 
     */ 
    function doGet() {
    
        // Without existing storage the request is not valid.
        if (!$this->exists()) {
            Storage::addHeaders(404, "Resource Not Found");
            exit();
        }

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
            exit();
        }
        
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        
        $media = Storage::CONTENT_TYPE_TEXT;

        $result = (new DOMXpath($this->xml))->evaluate($this->xpath); 
        if (!empty(libxml_get_errors())) {
            $message = "Invalid XPath";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
            exit();            
        } else if ($result instanceof DOMNodeList) {
            $media = Storage::CONTENT_TYPE_XML;
            $xml = new DOMDocument();
            if ($result->length == 1) {
                if ($result[0] instanceof DOMDocument)
                    $result = [$result[0]->firstChild];
                if (($result[0] instanceof DOMAttr)) {
                    $media = Storage::CONTENT_TYPE_TEXT;
                    $result = $result[0]->value;
                } else {
                    $xml->appendChild($xml->importNode($result[0], true));
                    $result = $xml->saveXML();
                }
            } else if ($result->length > 0) {
                $collection = $xml->createElement("collection");
                $xml->importNode($collection);
                foreach ($result as $entry)
                    $collection->appendChild($xml->importNode($entry, true));
                $xml->appendChild($collection);
                $result = $xml->saveXML();     
            } else $result = "";
        } else if (is_bool($result)) {
            $result = $result ? "true" : "false";
        }

        Storage::addHeaders(200, "Success", [
            "Storage" => $this->storage,
            "Storage-Revision" => $this->getRevision(),
            "Storage-Space" => Storage::SPACE . "/" . $this->getSize(),
            "Storage-Last-Modified" => date(DateTime::RFC822),
            "Storage-Expiration" => Storage::TIMEOUT . "/" . $this->getExpiration(DateTime::RFC822),
            "Content-Length" => strlen($result),
            "Content-Type" => $media
        ]);   
        print($result);
        exit;
    }

    /**
     * POST is another way to query data via transformation.
     * For this, an XSLT stylesheet is sent with the request-body, which is
     * then applied by the XSLT processor to the data in storage.
     * Thus the content type application/xslt+xml is always required.
     * The client defines the content type for the output with the output-tag
     * and the method-attribute. 
     * The XPath is optional for this method and is used to limit and preselect
     * the data.
     * 
     *     Request:
     * POST /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Content-Length: (bytes)
     * Content-Type: application/xslt+xml
     *     Request-Body:
     * XSLT stylesheet    
     *  
     *     Response:
     * HTTP/1.0 200 Success
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)   
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
     * Content-Length: (bytes)
     *     Response-Body:
     * The result of the transformation
     * 
     *     Response codes / behavior:
     *         HTTP/1.0 200 Success
     * - Request was successfully executed
     *         HTTP/1.0 400 Bad Request
     * - XPath is malformed
     * - XSLT Stylesheet is erroneous
     *         HTTP/1.0 404 Resource Not Found
     * - Storage is invalid 
     *         HTTP/1.0 415 Unsupported Media Type
     * - Attribute request without Content-Type text/plain
     */
    function doPost() {

        // Without existing storage the request is not valid.
        if (!$this->exists()) {
            Storage::addHeaders(404, "Resource Not Found");
            exit();
        }        

        // POST always expects an valid XSLT template for transformation.
        if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XML) !== 0) {
            Storage::addHeaders(415, "Unsupported Media Type");
            exit();
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // POST always expects an valid XSLT template for transformation.
        $style = new DOMDocument();
        if (!$style->loadXML(file_get_contents('php://input'))) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
            exit();
        }

        $processor = new XSLTProcessor();
        $processor->importStyleSheet($style);

        $xml = $this->xml;
        if (!empty($this->xpath)) {
            $xml = new DOMDocument();
            $targets = (new DOMXpath($this->xml))->query($this->xpath);
            foreach ($targets as $target)
                $xml->appendChild($xml->importNode($target, true));
        }
        
        $output = $processor->transformToXML($xml);
        if ($output === false) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
            exit();            
        }

        $media = (new DOMXpath($style))->query("//*[local-name()='output']/@method");
        if (!empty($media)
                && $media->length > 0
                && strcasecmp($media[0]->nodeValue, "text") === 0)
            $media = Storage::CONTENT_TYPE_TEXT;
        else $media = Storage::CONTENT_TYPE_XML;             

        Storage::addHeaders(200, "Success", [
            "Storage" => $this->storage,
            "Storage-Revision" => $this->getRevision(),
            "Storage-Space" => Storage::SPACE . "/" . $this->getSize(),
            "Storage-Last-Modified" => date(DateTime::RFC822),
            "Storage-Expiration" => Storage::TIMEOUT . "/" . $this->getExpiration(DateTime::RFC822),
            "Content-Length" => strlen($output),
            "Content-Type" => $media
        ]);        

        print($output);

        // The function and the reponse are complete.
        // The storage can be closed and the requests can be terminated.
        $this->close();
        exit;
    }

    /**
     * PUT inserts new elements and attributes into the storage.
     * The position for the insert is defined via an XPath.
     * XPath uses different notations for elements and attributes.
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     * The attribute values can be static (text) and dynamic (XPath function).
     * Values are send as request-body.
     * Whether they are used as text or XPath function is decided by the
     * Content-Type header of the request.
     *     text/plain: static text
     *     text/xpath: XPath function
     * 
     * If the XPath notation corresponds to attributes, elements are assumed.
     * For elements, the notation for pseudo elements is also supported: 
     *     <XPath>::first, <XPath>::last, <XPath>::before or <XPath>::after
     * Pseudo elements are a relative position specification to the selected
     * element.
     * 
     * The value of elements can be static (text), dynamic (XPath function) or
     * be an XML structure. Again, the value is transmitted with the
     * request-body and the type of processing is determined by the Content-Type:
     *     text/plain: static text
     *     text/xpath: XPath function
     *     application/xslt+xml: XML structure
     * 
     * The PUT method works resolutely and inserts or overwrites existing data.
     * The processing of the XPath is strict and dispenses with superfluous
     * spaces. The attributes ___rev / ___uid used internally by the storage
     * are read-only and cannot be changed.
     * 
     * In general, if no target can be reached via XPath, no errors will occur.
     * The PUT method informs the client about changes made via the response
     * headers Storage-Effects and Storage-Revision. The header Storage-Effects
     * contains a list of the UIDs that were directly affected by the change
     * and also contains the UIDs of newly created elements. If no changes were
     * made because the XPath cannot find a target or the target is read-only,
     * the header Storage-Effects can be omitted completely in the response.
     * Also in this case the request is responded with status 204 as
     * successfully executed.
     * 
     * Syntactic and symantic errors in the request and/or XPath and/or value
     * can cause error status 400 and 415.
     * 
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Content-Length: (bytes)
     * Content-Type: application/xslt+xml
     *     Request-Body:
     * XML structure
     *
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Content-Length: (bytes)
     *  Content-Type: text/plain
     *     Request-Body:
     * Value as plain text
     * 
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Content-Length: (bytes)
     * Content-Type: text/xpath
     *     Request-Body:
     * name(/*)   
     *  
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage-Effects: ... (list of UIDs)
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)   
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
     * 
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Attributes successfully created or set
     *         HTTP/1.0 400 Bad Request
     * - XPath is missing or malformed
     * - XPath without addressing a target is responded with status 204
     *         HTTP/1.0 404 Resource Not Found
     * - Storage is invalid 
     *         HTTP/1.0 413 Payload Too Large
     * - Allowed size of the request(-body) and/or storage is exceeded
     *         HTTP/1.0 415 Unsupported Media Type
     * - Attribute request without Content-Type text/plain
     */
    function doPut() {
        
        // Without existing storage the request is not valid.
        if (!$this->exists()) {
            Storage::addHeaders(404, "Resource Not Found");
            exit();
        }

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath)) {
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
            exit();
        }    

        // Storage::SPACE also limits the maximum size of writing request(-body).
        // If the limit is exceeded, the request is quit with status 413. 
        if (strlen(file_get_contents('php://input')) > Storage::SPACE) {
            Storage::addHeaders(413, "Payload Too Large");
            exit();
        }

        // For all PUT requests the Content-Type is needed, because for putting
        // in XML structures and text is distinguished.
        if (!isset($_SERVER["CONTENT_TYPE"])) {
            Storage::addHeaders(415, "Unsupported Media Type");
            exit();                
        }   
        
        libxml_use_internal_errors(true);
        libxml_clear_errors();        

        // PUT requests can address attributes and elements via XPath.
        // Multi-axis XPaths allow multiple targets.
        // The method only supports these two possibilities, other requests are
        // responsed with an error, because this situation cannot occur because
        // the XPath is recognized as XPath for an attribute and otherwise an
        // element is assumed.
        // In this case it can only happen that the XPath does not address a
        // target, which is not an error in the true sense. It only affects the
        // Storage-Effects header.
        // Therefore there is only one decision here.

        // XPath can address elements and attributes.
        // If the XPath ends with /attribute::<attribute> or /@<attribute> an
        // attribute is expected, in all other cases a element.

        if (preg_match(Storage::PATTERN_XPATH_ATTRIBUTE, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL)) {

            // The following Conten-Type is supported for attributes:
            // - text/plain for static values (text)
            // - text/xpath for dynamic values, based on XPath functions            

            // For attributes only the Content-Type text/plain and text/xpath
            // are supported, for other Content-Types no conversion exists.
            if (!in_array(strtolower($_SERVER["CONTENT_TYPE"]), [Storage::CONTENT_TYPE_TEXT, Storage::CONTENT_TYPE_XPATH])) {
                Storage::addHeaders(415, "Unsupported Media Type");
                exit();                
            }

            $input = file_get_contents('php://input');
            
            // The Content-Type text/xpath is a special of the XMXE Storage.
            // It expects a plain text which is an XPath function.
            // The XPath function is first once applied to the current XML
            // document from the storage and the result is put like the
            // Content-Type text/plain. Even if the target is mutable, the
            // XPath function is executed only once and the result is put on
            // all targets.
            if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XPATH) === 0) {
                $input = (new DOMXpath($this->xml))->evaluate($input);
                if ($input === false) {
                    $message = "Invalid XPath";
                    if (Storage::fetchLastXmlErrorMessage())
                        $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                    Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
                    exit();
                }
            }

            // From here on it continues with a static value for the attribute.

            $xpath = $matches[1];
            $attribute = $matches[2];

            // The attributes ___rev and ___uid are essential for the internal
            // organization and management of the data and cannot be changed.
            // PUT requests for these attributes are ignored and behave as if
            // no matching node was found. It should say request understood and
            // executed but without effect.
            if (!in_array($attribute, ["___rev", "___uid"])) {
                $targets = (new DOMXpath($this->xml))->query($xpath);
                if (!empty($targets)) {
                    $this->revision = $this->getRevision() +1;
                    $serials = []; 
                    foreach ($targets as $target) {
                        // Only elements are supported, this prevents the
                        // addressing of the XML document by the XPath.
                        if ($target->nodeType != XML_ELEMENT_NODE)
                            continue;
                        $serials[] = $target->getAttribute("___uid");
                        $target->setAttribute($attribute, $input); 
                        // The revision is updated at the parent nodes, so you
                        // can later determine which nodes have changed and
                        // with which revision. Partial access allows the
                        // client to check if the data or a tree is still up to
                        // date, because he can compare the revision.
                        Storage::updateNodeRevision($target, $this->getRevision());
                    }

                    // Only the list of serials is an indicator that data has
                    // changed and whether the revision changes with it.
                    // If necessary the revision must be corrected if there are
                    // no data changes.
                    if (!empty($serials))
                        header("Storage-Effects: " . join(" ", $serials));
                    else $this->revision--;     
                }
            }

            Storage::addHeaders(204, "No Content", [
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
        
        // An XPath for element(s) is then expected here.
        // If this is not the case, the request is responded with status 400.

        if (!preg_match(Storage::PATTERN_XPATH_PSEUDO, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL)) {
            Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
            exit();              
        }

        $xpath = $matches[1];
        $pseudo = $matches[2];

        // The following Conten-Type is supported for elements:
        // - application/xslt+xml for XML structures
        // - text/plain for static values (text)
        // - text/xpath for dynamic values, based on XPath functions

        if (in_array(strtolower($_SERVER["CONTENT_TYPE"]), [Storage::CONTENT_TYPE_TEXT, "text/xpath"])) {

            // The combination with a pseudo element is not possible for a text
            // value. Response with status 415 (Unsupported Media Type).
            if (!empty($pseudo)) {
                Storage::addHeaders(415, "Unsupported Media Type");
                exit();
            }   

            $input = file_get_contents('php://input');
            
            // The Content-Type text/xpath is a special of the XMXE Storage.
            // It expects a plain text which is an XPath function.
            // The XPath function is first once applied to the current XML
            // document from the storage and the result is put like the
            // Content-Type text/plain. Even if the target is mutable, the
            // XPath function is executed only once and the result is put on
            // all targets.
            if (strcasecmp($_SERVER["CONTENT_TYPE"], "text/xpath") === 0) {
                $input = (new DOMXpath($this->xml))->evaluate($input);
                if ($input === false) {
                    $message = "Invalid XPath";
                    if (Storage::fetchLastXmlErrorMessage())
                        $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                    Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
                    exit();
                }
            }

            $serials = []; 
            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (!empty($targets)) {
                $this->revision = $this->getRevision() +1;
                foreach ($targets as $target) {
                    // Overwriting of the root element is not possible, as it
                    // is an essential part of the storage, and is ignored. It
                    // does not cause to an error, so the behaviour is
                    // analogous to putting attributes.
                    if ($target->nodeType != XML_ELEMENT_NODE)
                        continue;
                    $serials[] = $target->getAttribute("___uid");
                    $replace = $this->xml->createElement($target->nodeName, $input);
                    foreach ($target->attributes as $attribute)
                        $replace->setAttribute($attribute->nodeName, $attribute->nodeValue);
                    $target->parentNode->replaceChild($this->xml->importNode($replace), $target);
                    // The revision is updated at the parent nodes, so you can
                    // later determine which nodes have changed and with which
                    // revision. Partial access allows the client to check if
                    // the data or a tree is still up to date, because he can
                    // compare the revision.
                    Storage::updateNodeRevision($replace, $this->getRevision());                        
                }
            }    
            
            // Only the list of serials is an indicator that data has changed
            // and whether the revision changes with it. If necessary the
            // revision must be corrected if there are no data changes.
            if (!empty($serials))
                header("Storage-Effects: " . join(" ", $serials));
            else $this->revision--;

            Storage::addHeaders(204, "No Content", [
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

        // Only an XML structure can be inserted, nothing else is supported.
        // So only the Content-Type application/xslt+xml can be used.

        if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XML) !== 0) {
            Storage::addHeaders(415, "Unsupported Media Type");
            exit();
        }

        // The request body must also be a valid XML structure, otherwise the
        // request will be acknowledged with an error.
        $xml = file_get_contents('php://input');

        // The request body must also be a valid XML structure, otherwise the
        // request is quit with an error.
        $input = file_get_contents('php://input');
        $input = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><data>$input</data>";

        // The XML is loaded, but what happens if an error occurs during
        // parsing? Status 400 or 422 - The decision for 400, because 422 means
        // semantic errors, but the parser only finds structural or syntactic
        // errors
        $xml = new DOMDocument();
        if (!$xml->loadXML($input)) {
            $message = "Invalid XML document";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
            exit();
        }

        // The attributes ___rev and ___uid are essential for the internal
        // organization and management of the data and cannot be changed.
        // When inserting, the attributes ___rev and ___uid are set
        // automatically. These attributes must not be  contained in the XML
        // structure to be inserted, because all XML elements without ___uid
        // attributes are determined after insertion and it is assumed that
        // they have been newly inserted. This approach was chosen to avuid a
        // recursive search/iteration in the XML structure to be inserted.
        $nodes = (new DOMXpath($xml))->query("//*[@___rev|@___uid]");
        foreach ($nodes as $node) {
            $node->removeAttribute("___rev");
            $node->removeAttribute("___uid");
        }

        if ($xml->firstChild->hasChildNodes()) {
            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (!empty($targets)) {
                $this->revision = $this->getRevision() +1;
                foreach ($targets as $target) {

                    // Overwriting of the root element is not possible, as it
                    // is an essential part of the storage, and is ignored. It
                    // does not cause to an error, so the behaviour is
                    // analogous to putting attributes.
                    if ($target->nodeType != XML_ELEMENT_NODE)
                        continue;

                    // Pseudo elements can be used to put in an XML
                    // substructure relative to the selected element.
                    if (empty($pseudo)) {
                        $replace = $target->cloneNode(false);
                        foreach ($xml->firstChild->childNodes as $insert)
                            $replace->appendChild($this->xml->importNode($insert->cloneNode(true), true));  
                        $target->parentNode->replaceChild($this->xml->importNode($replace), $target);                        
                    } else if (strcasecmp($pseudo, "before") === 0) {
                        if ($target->parentNode->nodeType == XML_ELEMENT_NODE)
                            foreach ($xml->firstChild->childNodes as $insert)
                                $target->parentNode->insertBefore($this->xml->importNode($insert), $target);
                    } else if (strcasecmp($pseudo, "after") === 0) {
                        if ($target->parentNode->nodeType == XML_ELEMENT_NODE)
                            foreach ($xml->firstChild->childNodes as $insert)
                                $target->parentNode->appendChild($this->xml->importNode($insert));
                    } else if (strcasecmp($pseudo, "first") === 0) {
                        $inserts = $xml->firstChild->childNodes;  
                        for ($index = $inserts->length -1; $index >= 0; $index--)
                            $target->insertBefore($this->xml->importNode($inserts->item($index)), $target->firstChild);
                    } else if (strcasecmp($pseudo, "last") === 0) {                            
                        foreach ($xml->firstChild->childNodes as $insert)
                            $target->appendChild($this->xml->importNode($insert));
                    } else {
                        Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid XPath"]);
                        exit();
                    }
                }
            }
        }

        // The attribute ___uid of all newly inserted elements is set.
        // It is assumed that all elements without the  ___uid attribute are
        // new. The revision of all affected nodes are updated, so you can
        // later determine which nodes have changed and with which revision.
        // Partial access allows the client to check if the data or a tree is
        // still up to date, because he can compare the revision.

        $serials = []; 
        $nodes = (new DOMXpath($this->xml))->query("//*[not(@___uid)]");
        foreach ($nodes as $node) {
            $serial = $this->getSerial();
            $serials[] = $serial;
            $node->setAttribute("___uid", $serial); 
            Storage::updateNodeRevision($node, $this->getRevision());
            
            // Also the UID of the directly addressed element is transmitted to
            // the client in the response, because the element itself has not
            // changed, but its content has. Other parent elements are not
            // listed because they are only indirectly affected. So the
            // behaviour is analogous to putting attributes. 
            if ($node->parentNode->nodeType != XML_ELEMENT_NODE)
                continue;
            $serial = $node->parentNode->getAttribute("___uid");
            if (!empty($serial)
                    && !in_array($serial, $serials))
                $serials[] = $serial;
        }

        // Only the list of serials is an indicator that data has changed and
        // whether the revision changes with it. If necessary the revision must
        // be corrected if there are no data changes.
        if (!empty($serials))
            header("Storage-Effects: " . join(" ", $serials));
        else $this->revision--;

        Storage::addHeaders(204, "No Content", [
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
     * Another Content-Type are responsed with status 415 Unsupported Media Type.
     *       
     * Attributes supports only text/plain and replaces the value of the
     * attribute. If necessary, the value is escaped.
     * Another Content-Type are responsed with status 415 (Unsupported Media Type).
     *
     * The attributes ___rev / ___uid  are used internally and cannot be changed.
     * Write accesses cause the status 405. 
     */
    function doPatch() {
    
        // Request:
        //     PATCH /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
        //     Content-Length: 0 Bytes
        //     Content-Type: application/xslt+xml
        // Request-Body:
        //     XML fragment for the node to be added
        
        // Request:
        //     PATCH /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
        //     Content-Length: 0 Bytes
        //     Content-Type: text/plain
        // Request-Body:
        //     Value as plain text     
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
        //     Storage-Revision: Revision   
        //     Storage-Space: Total/Used (in bytes)
        
        // Error Status:
        //    404 Destination does not exist
        //    405 Write access to attribute ___rev / __uid
        //    415 Content-Type is not supported

        // PATCH works in principle like PUT.
        // In comparison PATCH only works for existing elements and attributes.
        // In addition, no pseudo elements are supported.
        // The easiest way for the implementation is to check the things that
        // are not allowed and quit the request with an error.
        // In all other cases the processing can be passed to PUT.

        // TODO:

        $this->doPut();
    }

    function doDelete() {
    
        // Request:
        //     DELETE /<xpath> HTTP/1.0
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ 
        
        // Response:
        //     HTTP/1.0 200 Successful
        //     Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
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
                header(trim("$key: " .  preg_replace("/[\r\n]+/", " ", $value)));

        header("Execution-Time: " . round((microtime(true) -$_SERVER["REQUEST_TIME_FLOAT"]) *1000)); 
        
        $headers = array_change_key_case(empty($headers) ? [] : $headers, CASE_LOWER);
        // Header Content-Type is not sent by default.
        // PHP has a habit of adding the header automatically, but this is not wanted here.
        // It is removed somewhat unconventionally.
        if (!in_array("content-type", array_keys($headers))) {
            header("Content-Type: none");
            header_remove("Content-Type"); 
        }
        // When responding to an error, the default Allow header is added.
        // But only if no Allow header was passed.
        // So the header does not always have to be added manually.
        if ($status >= 400
                && !array_keys($headers, "allow"))
            header("Allow: CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE");        
    }

    private static function fetchLastXmlErrorMessage() {
    
        if (empty(libxml_get_errors()))
            return false;
        $message = libxml_get_errors();
        $message = end($message)->message;
        $message = preg_replace("/[\r\n]+/", " ", $message);
        $message = preg_replace("/\.+$/", " ", $message);
        return trim($message); 
    }

    static function onError($error, $message, $file, $line, $vars = array()) {

        // Special case XSLTProcessor errors
        // These cannot be caught any other way. Therefore the error header
        // is implemented here.
        $filter = "XSLTProcessor::transformToXml()"; 
        if (substr($message, 0, strlen($filter)) === $filter) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            Storage::addHeaders(400, "Bad Request", ["Message" => $message]);
            exit;
        }

        $unique = "#" . Storage::uniqueId();
        $message = "$message" . PHP_EOL . "\tat $file $line";
        if (!is_numeric($error))
            $message = "$error:" . $message;
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
    Storage::addHeaders(400, "Bad Request", ["Message" => "Invalid storage identifier"]);
    exit();
}

// The XPath is determined from REQUEST_URI or alternatively from REQUEST
// because some servers normalize the paths and URI for the CGI.
// It was not easy to determine the context path for all servers safely and
// then extract the XPath from the request. Therefore it was decided that the
// context path and XPath are separated by a symbol or a symbol sequence.
// The behavior can be customized with Storage::PATTERN_HTTP_REQUEST_URI.
$xpath = $_SERVER["REQUEST_URI"];
if (isset($_SERVER["REQUEST"])
        && preg_match(Storage::PATTERN_HTTP_REQUEST, $_SERVER["REQUEST"], $xpath, PREG_UNMATCHED_AS_NULL))
    $xpath = $xpath[2];
$xpath = preg_match(Storage::PATTERN_HTTP_REQUEST_URI, $xpath, $xpath, PREG_UNMATCHED_AS_NULL) ? $xpath[2] : "";

// With the exception of CONNECT, OPTIONS and POST, all requests expect an
// XPath or XPath function.
// CONNECT and OPTIONS do not use an (X)Path to establish a storage.
// POST uses the XPath for transformation only optionally to delimit the XML
// data for the transformation and works also without.
// In the other cases an empty XPath is replaced by the root slash.
if (empty($xpath)
        && !in_array(strtoupper($_SERVER["REQUEST_METHOD"]), ["CONNECT", "OPTIONS", "POST"]))
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
        case "POST":
            $storage->doPost();
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