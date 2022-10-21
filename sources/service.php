<?php
/**
 * LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
 * Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 * Diese Software unterliegt der Version 2 der Apache License.
 *
 * XMEX XML-Micro-Exchange
 * Copyright (C) 2021 Seanox Software Solutions
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
 * It is designed for easy communication and data exchange of web-applications
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
 * The data areas managed by the XML-Micro-Exchange as a data service are
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
 *     REQUEST
 * The implementation works RESTfull and uses normal HTTP request.
 * For the addressing of targets XPath axes and XPath functions are used,
 * which are transmitted as part of the URI path.
 * Because XPath has a different structure than the URI, even if it uses
 * similar characters, clients and/or gateways may experience syntax problems
 * when optimizing the request.
 * For this reason different ways of transmission and escape are supported for
 * the XPath.
 *
 *        URI (not escaped)
 * e.g. /xmex!//book[last()]/chapter[last()]
 *
 *        URI + URL Encoding
 * The XPath is used URL encoding.
 * e.g. /xmex!//book%5Blast()%5D/chapter%5Blast()%5D
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *        XPath as query string and URL encoding
 * The XPath is transmitted as a query string.
 * e.g. /xmex!?//book[last()]/chapter[last()]
 *      /xmex!?//book%5Blast()%5D/chapter%5Blast()%5D
 *      /xmex?//book[last()]/chapter[last()]
 *      /xmex?//book%5Blast()%5D/chapter%5Blast()%5D
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *        XPath as hexadecimal string
 * The URI starts with 0x after the XPath separator:
 * e.g. /xmex!0x2f2f626f6f6b5b6c61737428295d2f636861707465725b6c61737428295d
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *        XPath as Base64 encoded string
 * The URI starts with Base64 after the XPath separator:
 * e.g. /xmex!Base64:Ly9ib29rW2xhc3QoKV0vY2hhcHRlcltsYXN0KCld
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *     TRANSACTION / SIMULTANEOUS ACCESS
 * XML-Micro-Exchange supports simultaneous access.
 * Read accesses are executed simultaneously.
 * Write accesses creates a lock and avoids dirty reading.
 *
 *     ERROR HANDLING
 * Errors are communicated via the server status 500 and the header 'Error'.
 * The header 'Error' contains only an error number, for security reasons no
 * details. The error number with details can be found in the log file of the
 * service.
 * In the case of status 400 and 422, XML-Micro-Exchange uses the additional
 * header Message in the response, which contains more details about the error.
 * The difference between status 400 and 422 is that status 400 always refers
 * to the request and 422 to the request body. With status 400, errors are
 * detected in the request itself, and with status 422, errors are detected in
 * the content of the request body.
 *
 *     SECURITY
 * This aspect was deliberately considered and implemented here only in a very
 * rudimentary form. Only the storage(-key) with a length of 1 - 64 characters
 * can be regarded as secret.
 * For further security the approach of Basic Authentication, Digest Access
 * Authentication and/or Server/Client certificates is followed, which is
 * configured outside of the XMEX (XML-Micro-Exchange) at the web server.
 *
 * @author  Seanox Software Solutions
 * @version 1.3.0 20210420
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
     * For the preflight OPTIONS the following headers are added automatically:
     *     Access-Control-Allow-Methods, Access-Control-Allow-Headers
     */
    const CORS = [
        "Access-Control-Allow-Origin" => "*",
        "Access-Control-Allow-Credentials" => "true",
        "Access-Control-Max-Age" => "86400",
        "Access-Control-Expose-Headers" => "*"
    ];

    /** Current Storage instance */
    private $storage;

    /** Current Name of the root element */
    private $root;

    /** Current name of the Storage */
    private $store;

    /** Current Storage instance */
    private $share;

    /** Current DOMDocument */
    private $xml;

    /** Current XPath */
    private $xpath;

    /** Current XPath options (array lowercase) */
    private $options;

    /** Revision of the storage */
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
     *     Group 3. Protocol
     */
    const PATTERN_HTTP_REQUEST = "/^([A-Z]+)\s+(.+)\s+(HtTP\/\d+(?:\.\d+)*)$/i";

    /**
     * Pattern for separating URI-Path and XPath.
     * If the pattern is empty, null or false, the request URI without context
     * path will be used. This is helpful when the service is used as a domain.
     *     Group 0. Full match
     *     Group 1. URI-Path
     *     Group 2. XPath
     */
    const PATTERN_HTTP_REQUEST_URI = "/^(.*?)[!#\$\*:\?@\|~]+(.*)$/i";

    /**
     * Pattern for the Storage header
     *     Group 0. Full match
     *     Group 1. Storage
     *     Group 2. Name of the root element (optional)
     */
    const PATTERN_HEADER_STORAGE = "/^(\w{1,64})(?:\s+(\w+)){0,1}$/";

    /**
     * Pattern to determine options (optional directives) at the end of XPath
     *     Group 0. Full match
     *     Group 1. XPath
     *     Group 2. options (optional)
     */
    const PATTERN_XPATH_OPTIONS = "/^(.*?)((?:!+\w+){0,})$/";

    /**
     * Pattern to determine the structure of XPath axis expressions for attributes
     *     Group 0. Full match
     *     Group 1. XPath axis
     *     Group 2. Attribute
     */
    const PATTERN_XPATH_ATTRIBUTE = "/((?:^\/+)|(?:^.*?))\/{0,}(?<=\/)(?:@|attribute::)(\w+)$/i";

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
     */
    const PATTERN_XPATH_FUNCTION = "/^[\(\s]*[^\/\.\s\(].*$/";

    /** Constants of used content types */
    const CONTENT_TYPE_TEXT  = "text/plain";
    const CONTENT_TYPE_XPATH = "text/xpath";
    const CONTENT_TYPE_HTML  = "text/html";
    const CONTENT_TYPE_XML   = "application/xml";
    const CONTENT_TYPE_XSLT  = "application/xslt+xml";
    const CONTENT_TYPE_JSON  = "application/json";

    /**
     * Constructor creates a new Storage object.
     * @param string $storage
     * @param string $root
     * @param string $xpath
     */
    function __construct($storage = null, $root = null, $xpath = null) {

        // The storage identifier is case-sensitive.
        // To ensure that this also works with Windows, Base64 encoding is used.

        $options = [];
        if (preg_match(Storage::PATTERN_XPATH_OPTIONS, $xpath, $matches, PREG_UNMATCHED_AS_NULL)) {
            $xpath = $matches[1];
            $options = array_merge(array_filter(explode("!", strtolower($matches[2]))));
        }

        $this->storage  = $storage;
        $this->root     = $root ? $root : "data";
        $this->store    = Storage::DIRECTORY . "/" . base64_encode($this->storage);
        $this->xpath    = $xpath;
        $this->options  = $options;
        $this->unique   = Storage::uniqueId();
        $this->serial   = 0;
        $this->revision = 0;
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

        // Structure of the Unique-Id [? MICROSECONDS][4 PORT]
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

    /**
     * Opens a storage with a XPath for the current request.
     * The storage can optionally be opened exclusively for write access.
     * If the storage to be opened does not yet exist, it is initialized.
     * Simultaneous requests must then wait through the file lock.
     * @param  string  $storage
     * @param  string  $xpath
     * @param  boolean $exclusive
     * @return Storage Instance of the Storage
     */
    static function share($storage, $xpath, $exclusive = true) {

        if (!preg_match(Storage::PATTERN_HEADER_STORAGE, $storage))
            (new Storage)->quit(400, "Bad Request", ["Message" => "Invalid storage identifier"]);

        $root = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$2", $storage);
        $storage = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$1", $storage);

        Storage::cleanUp();
        if (!file_exists(Storage::DIRECTORY)) {
             mkdir(Storage::DIRECTORY, true);
             chmod(Storage::DIRECTORY, 0755);
        }
        $storage = new Storage($storage, $root, $xpath);

        if ($storage->exists()) {
            $storage->open($exclusive);
            // Safe is safe, if not the default 'data' is used,
            // the name of the root element must be known.
            // Otherwise the request is quit with status 404 and terminated.
            if (($root ? $root : "data") != $storage->xml->documentElement->nodeName)
                $storage->quit(404, "Resource Not Found");
        }
        return $storage;
    }

    /**
     * Return TRUE if the storage already exists.
     * @return TRUE if the storage already exists
     */
    private function exists() {
        return file_exists($this->store)
                && filesize($this->store) > 0;
    }

    /**
     * Opens the storage for the current request.
     * The storage can optionally be opened exclusively for write access.
     * If the storage to be opened does not yet exist, it is initialized.
     * Simultaneous requests must then wait through the file lock.
     * @param boolean $exclusive
     */
    private function open($exclusive = true) {

        if ($this->share !== null)
            return;

        touch($this->store);
        $this->share = fopen($this->store, "c+");
        flock($this->share, filesize($this->store) <= 0 || $exclusive === true ? LOCK_EX : LOCK_SH);

        if (filesize($this->store) <= 0) {
            fwrite($this->share,
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
            "<" . $this->root . " ___rev=\"0\" ___uid=\"" . $this->getSerial() . "\"/>");
            rewind($this->share);
        }

        fseek($this->share, 0, SEEK_END);
        $size = ftell($this->share);
        rewind($this->share);
        $this->xml = new DOMDocument();
        $this->xml->loadXML(fread($this->share, $size));
        $this->revision = $this->xml->documentElement->getAttribute("___rev");
    }

    /**
     * Materializes the XML document from the memory in the file system.
     * Unlike save, the file is not closed and the data can be modified without
     * another (PHP)process being able to read the data before finalizing it by
     * closing it. Materialization is only executed if there are changes in the
     * XML document, which is determined by the revision of the root element.
     * The size of the storage is limited by Storage::SPACE because it is a
     * volatile micro datasource for short-term data exchange.
     * An overrun causes the status 413.
     */
    function materialize() {

        if ($this->share == null)
            return;
        if ($this->revision == $this->xml->documentElement->getAttribute("___rev"))
            return;

        $output = $this->xml->saveXML();
        if (strlen($output) > Storage::SPACE)
            $this->quit(413, "Payload Too Large");
        ftruncate($this->share, 0);
        rewind($this->share);
        fwrite($this->share, $output);
    }

    /** Closes the storage for the current request. */
    function close() {

        if ($this->share == null)
            return;

        flock($this->share, LOCK_UN);
        fclose($this->share);

        $this->share = null;
        $this->xml = null;
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
        if ($this->store !== null
                && file_exists($this->store))
            return filesize($this->store);
        return 0;
    }

    /**
     * Updates recursive the revision for an element and all parent elements.
     * @param DOMElement $node
     * @param string     $revision
     */
    private static function updateNodeRevision($node, $revision) {

        while ($node && $node->nodeType === XML_ELEMENT_NODE) {
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
     * The response for a CONNECT always contains a Connection-Unique header.
     * The Unique is unique in the Datasource and in the Storage and can be
     * used by the client e.g. in XML as attributes to locate his data faster.
     *
     * In addition, OPTIONS can also be used as an alternative to CONNECT,
     * because CONNECT is not an HTTP standard. For this purpose OPTIONS
     * without XPath, but with context path if necessary, is used. In this case
     * OPTIONS will hand over the work to CONNECT.
     *
     *     Request:
     * CONNECT / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Request:
     * CONNECT / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier / root)
     *
     *    Response:
     * HTTP/1.0 201 Created
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Connection-Unique: UID
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Connection-Unique: UID
     *
     *     Response codes / behavior:
     *         HTTP/1.0 201 Resource Created
     * - Response can be status 201 if the storage was newly created
     *         HTTP/1.0 204 No Content
     * - Response can be status 204 if the storage already exists
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - XPath is used from PATH_INFO + QUERY_STRING, not the request URI
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     *         HTTP/1.0 507 Insufficient Storage
     * - Response can be status 507 if the storage is full
     */
    function doConnect() {

        if (!empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        $response = [201, "Created"];
        if (!$this->exists()) {
            $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
            if (iterator_count($iterator) >= Storage::QUANTITY)
                $this->quit(507, "Insufficient Storage");
            $this->open();
        } else $response = [204, "No Content"];

        $this->materialize();
        $this->quit($response[0], $response[1], ["Connection-Unique" => $this->unique, "Allow" => "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE"]);
    }

    /**
     * OPTIONS is used to query the allowed HTTP methods for an XPath, which is
     * responded with the Allow-header. This method distinguishes between XPath
     * axis and XPath function and uses different Allow headers. Also the
     * existence of the target on an XPath axis has an influence on the
     * response. The method will not use status 404 in relation to non-existing
     * targets, but will offer the methods CONNECT, OPTIONS, PUT via
     * Allow-Header.
     * In the case of an XPath axis, the UIDs of the targets are returned in
     * the Storage-Effects header. Unlike modifier methods like PUT, PATCH and
     * DELETE, the effect suffix (:A/:M/:D) is omitted here.
     * If the XPath is a function, it is executed and thus validated, but
     * without returning the result.
     * The XPath processing is strict and does not accept unnecessary spaces.
     * Faulty XPath will cause the status 400.
     *
     *     Request:
     * OPTIONS /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage-Effects: ... (list of UIDs)
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Request was successfully executed
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     *
     * In addition, OPTIONS can also be used as an alternative to CONNECT,
     * because CONNECT is not an HTTP standard. For this purpose OPTIONS
     * without XPath, but with context path if necessary, is used. In this case
     * OPTIONS will hand over the work to CONNECT.
     *
     * The response for a CONNECT always contains a Connection-Unique header.
     * The Unique is unique in the Datasource and in the Storage and can be
     * used by the client e.g. in XML as attributes to locate his data faster.
     *
     *     Request:
     * OPTIONS / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Request:
     * OPTIONS / HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier)
     *
     *    Response:
     * HTTP/1.0 201 Created
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Connection-Unique: UID
     *
     *     Response:
     * HTTP/1.0 No Content
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Connection-Unique: UID
     *
     *     Response codes / behavior:
     *         HTTP/1.0 201 Resource Created
     * - Response can be status 201 if the storage was newly created
     *         HTTP/1.0 204 No Content
     * - Response can be status 204 if the storage already exists
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     *         HTTP/1.0 507 Insufficient Storage
     * - Response can be status 507 if the storage is full
     */
    function doOptions() {

        // Without XPath (PATH_INFO) behaves like CONNECT,
        // because CONNECT is no HTTP standard.
        // The function call is executed and the request is terminated.
        if (empty($this->xpath))
            $this->doConnect();

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $allow = "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE";
        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            (new DOMXpath($this->xml))->evaluate($this->xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath function (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            $allow = "CONNECT, OPTIONS, GET, POST";
        } else {
            $targets = (new DOMXpath($this->xml))->query($this->xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (!empty($targets) && $targets->length > 0) {
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

        $this->quit(204, "No Content", ["Allow" => $allow]);
    }

    /**
     * GET queries data about XPath axes and functions. For this, the XPath
     * axis or function is sent with URI. Depending on whether the request is
     * an XPath axis or an XPath function, different Content-Type are used for
     * the response.
     *
     *     application/xml
     * When the XPath axis addresses one target, the addressed target is the
     * root element of the returned XML structure. If the XPath addresses
     * multiple targets, their XML structure is combined in the root element
     * collection.
     *
     *     text/plain
     * If the XPath addresses only one attribute, the value is returned as
     * plain text. Also the result of XPath functions is returned as plain
     * text. Decimal results use float, booleans the values true and false.
     *
     * The XPath processing is strict and does not accept unnecessary spaces.
     *
     *     Request:
     * GET /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Response:
     * HTTP/1.0 200 Success
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Content-Length: (bytes)
     *     Response-Body:
     * The result of the XPath request
     *
     *     Response codes / behavior:
     *         HTTP/1.0 200 Success
     * - Request was successfully executed
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doGet() {

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath))
            $result = (new DOMXpath($this->xml))->evaluate($this->xpath);
        else $result = (new DOMXpath($this->xml))->query($this->xpath);
        if (Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XPath";
            if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath))
                $message = "Invalid XPath function";
            $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        } else if (!preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)
                &&  (!$result || empty($result) || $result->length <= 0)) {
            $this->quit(204, "No Content");
        } else if ($result instanceof DOMNodeList) {
            if ($result->length == 1) {
                if ($result[0] instanceof DOMDocument)
                    $result = [$result[0]->documentElement];
                if ($result[0] instanceof DOMAttr) {
                    $result = $result[0]->value;
                } else {
                    $xml = new DOMDocument();
                    $xml->appendChild($xml->importNode($result[0], true));
                    $result = $xml;
                }
            } else if ($result->length > 0) {
                $xml = new DOMDocument();
                $collection = $xml->createElement("collection");
                $xml->importNode($collection, true);
                foreach ($result as $entry) {
                    if ($entry instanceof DOMAttr)
                        $entry = $xml->createElement($entry->name, $entry->value);
                    $collection->appendChild($xml->importNode($entry, true));
                }
                $xml->appendChild($collection);
                $result = $xml;
            } else $result = "";
        } else if (is_bool($result)) {
            $result = $result ? "true" : "false";
        }

        $this->quit(200, "Success", null, $result);
    }

    /**
     * POST queries data about XPath axes and functions via transformation.
     * For this, an XSLT stylesheet is sent with the request-body, which is
     * then applied by the XSLT processor to the data in storage.
     * Thus the content type application/xslt+xml is always required.
     * The client defines the content type for the output with the output-tag
     * and the method-attribute.
     * The XPath is optional for this method and is used to limit and preselect
     * the data. The processing is strict and does not accept unnecessary
     * spaces.
     *
     *     Request:
     * POST /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
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
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     * Content-Length: (bytes)
     *     Response-Body:
     * The result of the transformation
     *
     *     Response codes / behavior:
     *         HTTP/1.0 200 Success
     * - Request was successfully executed
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - XSLT Stylesheet is erroneous
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 415 Unsupported Media Type
     * - Attribute request without Content-Type text/plain
     *         HTTP/1.0 422 Unprocessable Entity
     * - Data in the request body cannot be processed
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doPost() {

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // POST always expects an valid XSLT template for transformation.
        if (!isset($_SERVER["CONTENT_TYPE"])
                || strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XSLT) !== 0)
            $this->quit(415, "Unsupported Media Type");

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            $message = "Invalid XPath (Functions are not supported)";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // POST always expects an valid XSLT template for transformation.
        $style = new DOMDocument();
        $input = file_get_contents("php://input");
        if (empty($input)
                || !$style->loadXML($input)
                || Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        $processor = new XSLTProcessor();
        $processor->importStyleSheet($style);
        if (Storage::fetchLastXmlErrorMessage()) {
             $message = "Invalid XSLT stylesheet (" . Storage::fetchLastXmlErrorMessage() . ")";
             $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        $xml = $this->xml;
        if (!empty($this->xpath)) {
            $xml = new DOMDocument();
            $targets = (new DOMXpath($this->xml))->query($this->xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (!$targets || empty($targets) || $targets->length <= 0)
                $this->quit(204, "No Content");
            if ($targets->length == 1) {
                $target = $targets[0];
                if ($target instanceof DOMAttr)
                    $target = $xml->createElement($target->name, $target->value);
                $xml->appendChild($xml->importNode($target, true));
            } else {
                $collection = $xml->createElement("collection");
                $xml->importNode($collection, true);
                foreach ($targets as $target) {
                    if ($target instanceof DOMAttr)
                        $target = $xml->createElement($target->name, $target->value);
                    $collection->appendChild($xml->importNode($target, true));
                }
                $xml->appendChild($collection);
            }
        }

        $output = $processor->transformToXML($xml);
        if ($output === false
                || Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        $header = null;
        $method = (new DOMXpath($style))->evaluate("normalize-space(//*[local-name()='output']/@method)");
        if (!empty($output))
            if (strcasecmp($method, "xml") === 0
                    || empty($method))
                if (in_array("json", $this->options))
                    $output = simplexml_load_string($output);
                else $header = ["Content-Type" => Storage::CONTENT_TYPE_XML];
            else if (strcasecmp($method, "html") === 0)
                $header = ["Content-Type" => Storage::CONTENT_TYPE_HTML];
        $this->quit(200, "Success", $header, $output);
    }

    /**
     * PUT creates elements and attributes in storage and/or changes the value
     * of existing ones.
     * The position for the insert is defined via an XPath.
     * For better understanding, the method should be called PUT INTO, because
     * it is always based on an existing XPath axis as the parent target.
     * XPath uses different notations for elements and attributes.
     *
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     * The attribute values can be static (text) and dynamic (XPath function).
     * Values are send as request-body.
     * Whether they are used as text or XPath function is decided by the
     * Content-Type header of the request.
     *     text/plain: static text
     *     text/xpath: XPath function
     *
     * If the XPath notation does not match the attributes, elements are
     * assumed. For elements, the notation for pseudo elements is supported:
     *     <XPath>::first, <XPath>::last, <XPath>::before or <XPath>::after
     * Pseudo elements are a relative position specification to the selected
     * element.
     *
     * The value of elements can be static (text), dynamic (XPath function) or
     * be an XML structure. Also here the value is send with the request-body
     * and the type of processing is determined by the Content-Type:
     *     text/plain: static text
     *     text/xpath: XPath function
     *     application/xml: XML structure
     *
     * The PUT method works resolutely and inserts or overwrites existing data.
     * The XPath processing is strict and does not accept unnecessary spaces.
     * The attributes ___rev / ___uid used internally by the storage are
     * read-only and cannot be changed.
     *
     * In general, PUT requests are responded to with status 204. Status 404 is
     * used only with relation to the storage. In all other cases the PUT
     * method informs the client about changes also with status 204 and the
     * response headers Storage-Effects and Storage-Revision. The header
     * Storage-Effects contains a list of the UIDs that were directly affected
     * by the change and also contains the UIDs of newly created elements. If
     * no changes were made because the XPath cannot find a writable target,
     * the header Storage-Effects can be omitted completely in the response.
     *
     * Syntactic and semantic errors in the request and/or XPath and/or value
     * can cause error status 400 and 415. If errors occur due to the
     * transmitted request body, this causes status 422.
     *
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     * Content-Type: application/xml
     *     Request-Body:
     * XML structure
     *
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     *  Content-Type: text/plain
     *     Request-Body:
     * Value as plain text
     *
     *     Request:
     * PUT /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     * Content-Type: text/xpath
     *     Request-Body:
     * Value as XPath function
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage-Effects: ... (list of UIDs)
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Element(s) or attribute(s) successfully created or set
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - XPath without addressing a target is responded with status 204
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 413 Payload Too Large
     * - Allowed size of the request(-body) and/or storage is exceeded
     *         HTTP/1.0 415 Unsupported Media Type
     * - Attribute request without Content-Type text/plain
     *         HTTP/1.0 422 Unprocessable Entity
     * - Data in the request body cannot be processed
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doPut() {

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        // Storage::SPACE also limits the maximum size of writing request(-body).
        // If the limit is exceeded, the request is quit with status 413.
        if (strlen(file_get_contents("php://input")) > Storage::SPACE)
            $this->quit(413, "Payload Too Large");

        // For all PUT requests the Content-Type is needed, because for putting
        // in XML structures and text is distinguished.
        if (!isset($_SERVER["CONTENT_TYPE"]))
            $this->quit(415, "Unsupported Media Type");

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            $message = "Invalid XPath (Functions are not supported)";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // PUT requests can address attributes and elements via XPath.
        // Multi-axis XPaths allow multiple targets.
        // The method only supports these two possibilities, other requests are
        // responded with an error, because this situation cannot occur because
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

            // The following Content-Type is supported for attributes:
            // - text/plain for static values (text)
            // - text/xpath for dynamic values, based on XPath functions

            // For attributes only the Content-Type text/plain and text/xpath
            // are supported, for other Content-Types no conversion exists.
            if (!in_array(strtolower($_SERVER["CONTENT_TYPE"]), [Storage::CONTENT_TYPE_TEXT, Storage::CONTENT_TYPE_XPATH]))
                $this->quit(415, "Unsupported Media Type");

            $input = file_get_contents("php://input");

            // The Content-Type text/xpath is a special of the XMEX Storage.
            // It expects a plain text which is an XPath function.
            // The XPath function is first once applied to the current XML
            // document from the storage and the result is put like the
            // Content-Type text/plain. Even if the target is mutable, the
            // XPath function is executed only once and the result is put on
            // all targets.
            if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XPATH) === 0) {
                if (!preg_match(Storage::PATTERN_XPATH_FUNCTION, $input)) {
                    $message = "Invalid XPath (Axes are not supported)";
                    $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
                }
                $input = (new DOMXpath($this->xml))->evaluate($input);
                if ($input === false
                        || Storage::fetchLastXmlErrorMessage()) {
                    $message = "Invalid XPath function";
                    if (Storage::fetchLastXmlErrorMessage())
                        $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                    $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
                }
            }

            // From here on it continues with a static value for the attribute.

            $xpath = $matches[1];
            $attribute = $matches[2];

            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (!$targets || empty($targets) || $targets->length <= 0)
                $this->quit(204, "No Content");

            // The attributes ___rev and ___uid are essential for the internal
            // organization and management of the data and cannot be changed.
            // PUT requests for these attributes are ignored and behave as if
            // no matching node was found. It should say request understood and
            // executed but without effect.
            if (!in_array($attribute, ["___rev", "___uid"])) {
                $serials = [];
                foreach ($targets as $target) {
                    // Only elements are supported, this prevents the
                    // addressing of the XML document by the XPath.
                    if ($target->nodeType != XML_ELEMENT_NODE)
                        continue;
                    $serials[] = $target->getAttribute("___uid") . ":M";
                    $target->setAttribute($attribute, $input);
                    // The revision is updated at the parent nodes, so you
                    // can later determine which nodes have changed and
                    // with which revision. Partial access allows the
                    // client to check if the data or a tree is still up to
                    // date, because he can compare the revision.
                    Storage::updateNodeRevision($target, $this->revision +1);
                }

                // Only the list of serials is an indicator that data has
                // changed and whether the revision changes with it.
                // If necessary the revision must be corrected if there are
                // no data changes.
                if (!empty($serials))
                    header("Storage-Effects: " . join(" ", $serials));
            }

            $this->materialize();
            $this->quit(204, "No Content");
        }

        // An XPath for element(s) is then expected here.
        // If this is not the case, the request is responded with status 400.
        if (!preg_match(Storage::PATTERN_XPATH_PSEUDO, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath axis"]);

        $xpath = $matches[1];
        $pseudo = $matches[2];

        // The following Content-Type is supported for elements:
        // - application/xml for XML structures
        // - text/plain for static values (text)
        // - text/xpath for dynamic values, based on XPath functions

        if (in_array(strtolower($_SERVER["CONTENT_TYPE"]), [Storage::CONTENT_TYPE_TEXT, Storage::CONTENT_TYPE_XPATH])) {

            // The combination with a pseudo element is not possible for a text
            // value. Response with status 415 (Unsupported Media Type).
            if (!empty($pseudo))
                $this->quit(415, "Unsupported Media Type");

            $input = file_get_contents("php://input");

            // The Content-Type text/xpath is a special of the XMEX Storage.
            // It expects a plain text which is an XPath function.
            // The XPath function is first once applied to the current XML
            // document from the storage and the result is put like the
            // Content-Type text/plain. Even if the target is mutable, the
            // XPath function is executed only once and the result is put on
            // all targets.
            if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XPATH) === 0) {
                if (!preg_match(Storage::PATTERN_XPATH_FUNCTION, $input)) {
                    $message = "Invalid XPath (Axes are not supported)";
                    $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
                }
                $input = (new DOMXpath($this->xml))->evaluate($input);
                if ($input === false
                        || Storage::fetchLastXmlErrorMessage()) {
                    $message = "Invalid XPath function";
                    if (Storage::fetchLastXmlErrorMessage())
                        $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
                    $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
                }
            }

            $serials = [];
            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (!$targets || empty($targets) || $targets->length <= 0)
                $this->quit(204, "No Content");

            foreach ($targets as $target) {
                // Overwriting of the root element is not possible, as it
                // is an essential part of the storage, and is ignored. It
                // does not cause to an error, so the behaviour is
                // analogous to putting attributes.
                if ($target->nodeType != XML_ELEMENT_NODE)
                    continue;
                $serials[] = $target->getAttribute("___uid") . ":M";
                $replace = $target->cloneNode(false);
                $replace->appendChild($this->xml->createTextNode($input));
                $target->parentNode->replaceChild($this->xml->importNode($replace, true), $target);
                // The revision is updated at the parent nodes, so you can
                // later determine which nodes have changed and with which
                // revision. Partial access allows the client to check if
                // the data or a tree is still up to date, because he can
                // compare the revision.
                Storage::updateNodeRevision($replace, $this->revision +1);
            }

            // Only the list of serials is an indicator that data has changed
            // and whether the revision changes with it. If necessary the
            // revision must be corrected if there are no data changes.
            if (!empty($serials))
                header("Storage-Effects: " . join(" ", $serials));

            $this->materialize();
            $this->quit(204, "No Content");
        }

        // Only an XML structure can be inserted, nothing else is supported.
        // So only the Content-Type application/xml can be used.
        if (strcasecmp($_SERVER["CONTENT_TYPE"], Storage::CONTENT_TYPE_XML) !== 0)
            $this->quit(415, "Unsupported Media Type");

        // The request body must also be a valid XML structure, otherwise the
        // request is quit with an error.
        $input = file_get_contents("php://input");
        $input = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><data>$input</data>";

        // The XML is loaded, but what happens if an error occurs during
        // parsing? Status 400 or 422 - The decision for 422, because 400 means
        // faulty request. But this is a (semantic) error in the request body.
        $xml = new DOMDocument();
        if (!$xml->loadXML($input)
                || Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XML document";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        // The attributes ___rev and ___uid are essential for the internal
        // organization and management of the data and cannot be changed.
        // When inserting, the attributes ___rev and ___uid are set
        // automatically. These attributes must not be  contained in the XML
        // structure to be inserted, because all XML elements without ___uid
        // attributes are determined after insertion and it is assumed that
        // they have been newly inserted. This approach was chosen to avoid a
        // recursive search/iteration in the XML structure to be inserted.
        $nodes = (new DOMXpath($xml))->query("//*[@___rev|@___uid]");
        foreach ($nodes as $node) {
            $node->removeAttribute("___rev");
            $node->removeAttribute("___uid");
        }

        $serials = [];
        if ($xml->documentElement->hasChildNodes()) {
            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (!$targets || empty($targets) || $targets->length <= 0)
                $this->quit(204, "No Content");

            foreach ($targets as $target) {

                // Overwriting of the root element is not possible, as it
                // is an essential part of the storage, and is ignored. It
                // does not cause to an error, so the behaviour is
                // analogous to putting attributes.
                if ($target->nodeType != XML_ELEMENT_NODE)
                    continue;

                if (!empty($pseudo))
                    $pseudo = strtolower($pseudo);

                // Pseudo elements can be used to put in an XML
                // substructure relative to the selected element.
                if (empty($pseudo)) {
                    // The UIDs of the children that are removed by the
                    // replacement are determined for storage effects.
                    $childs = (new DOMXpath($this->xml))->query(".//*[@___uid]", $target);
                    foreach ($childs as $child)
                        $serials[] = $child->getAttribute("___uid") . ":D";
                    $replace = $target->cloneNode(false);
                    foreach ($xml->documentElement->childNodes as $insert)
                        $replace->appendChild($this->xml->importNode($insert->cloneNode(true), true));
                    $target->parentNode->replaceChild($this->xml->importNode($replace, true), $target);
                } else if (strcmp($pseudo, "before") === 0) {
                    if ($target->parentNode->nodeType == XML_ELEMENT_NODE)
                        foreach ($xml->documentElement->childNodes as $insert)
                            $target->parentNode->insertBefore($this->xml->importNode($insert, true), $target);
                } else if (strcmp($pseudo, "after") === 0) {
                    if ($target->parentNode->nodeType == XML_ELEMENT_NODE) {
                        $nodes = [];
                        foreach($xml->documentElement->childNodes as $node)
                            array_unshift($nodes, $node);
                        foreach ($nodes as $insert)
                            if ($target->nextSibling)
                                $target->parentNode->insertBefore($this->xml->importNode($insert, true), $target->nextSibling);
                            else $target->parentNode->appendChild($this->xml->importNode($insert, true));
                    }
                } else if (strcmp($pseudo, "first") === 0) {
                    $inserts = $xml->documentElement->childNodes;
                    for ($index = $inserts->length -1; $index >= 0; $index--)
                        $target->insertBefore($this->xml->importNode($inserts->item($index), true), $target->firstChild);
                } else if (strcmp($pseudo, "last") === 0) {
                    foreach ($xml->documentElement->childNodes as $insert)
                        $target->appendChild($this->xml->importNode($insert, true));
                } else $this->quit(400, "Bad Request", ["Message" => "Invalid XPath axis (Unsupported pseudo syntax found)"]);
            }
        }

        // The attribute ___uid of all newly inserted elements is set.
        // It is assumed that all elements without the  ___uid attribute are
        // new. The revision of all affected nodes are updated, so you can
        // later determine which nodes have changed and with which revision.
        // Partial access allows the client to check if the data or a tree is
        // still up to date, because he can compare the revision.

        $nodes = (new DOMXpath($this->xml))->query("//*[not(@___uid)]");
        foreach ($nodes as $node) {
            $serial = $this->getSerial();
            $serials[] = $serial . ":A";
            $node->setAttribute("___uid", $serial);
            Storage::updateNodeRevision($node, $this->revision +1);

            // Also the UID of the directly addressed element is transmitted to
            // the client in the response, because the element itself has not
            // changed, but its content has. Other parent elements are not
            // listed because they are only indirectly affected. So the
            // behaviour is analogous to putting attributes.
            if ($node->parentNode->nodeType != XML_ELEMENT_NODE)
                continue;
            $serial = $node->parentNode->getAttribute("___uid");
            if (!empty($serial)
                    && !in_array($serial . ":A", $serials)
                    && !in_array($serial . ":M", $serials))
                $serials[] = $serial . ":M";
        }

        // Only the list of serials is an indicator that data has changed and
        // whether the revision changes with it. If necessary the revision must
        // be corrected if there are no data changes.
        if (!empty($serials))
            header("Storage-Effects: " . join(" ", $serials));

        $this->materialize();
        $this->quit(204, "No Content");
    }

    /**
     * PATCH changes existing elements and attributes in storage.
     * The position for the insert is defined via an XPath.
     * The method works almost like PUT, but the XPath axis of the request
     * always expects an existing target.
     * XPath uses different notations for elements and attributes.
     *
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     * The attribute values can be static (text) and dynamic (XPath function).
     * Values are send as request-body.
     * Whether they are used as text or XPath function is decided by the
     * Content-Type header of the request.
     *     text/plain: static text
     *     text/xpath: XPath function
     *
     * If the XPath notation does not match the attributes, elements are
     * assumed. Unlike the PUT method, no pseudo elements are supported for
     * elements.
     *
     * The value of elements can be static (text), dynamic (XPath function) or
     * be an XML structure. Also here the value is send with the request-body
     * and the type of processing is determined by the Content-Type:
     *     text/plain: static text
     *     text/xpath: XPath function
     *     application/xml: XML structure
     *
     * The PATCH method works resolutely and  overwrites existing data.
     * The XPath processing is strict and does not accept unnecessary spaces.
     * The attributes ___rev / ___uid used internally by the storage are
     * read-only and cannot be changed.
     *
     * In general, PATCH requests are responded to with status 204. Status 404
     * is used only with relation to the storage. In all other cases the PATCH
     * method informs the client about changes with status 204 and the response
     * headers Storage-Effects and Storage-Revision. The header Storage-Effects
     * contains a list of the UIDs that were directly affected by the change
     * elements. If no changes were made because the XPath cannot find a
     * writable target, the header Storage-Effects can be omitted completely in
     * the response.
     *
     * Syntactic and semantics errors in the request and/or XPath and/or value
     * can cause error status 400 and 415. If errors occur due to the
     * transmitted request body, this causes status 422.
     *
     *     Request:
     * PATCH /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     * Content-Type: application/xml
     *     Request-Body:
     * XML structure
     *
     *     Request:
     * PATCH /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     *  Content-Type: text/plain
     *     Request-Body:
     * Value as plain text
     *
     *     Request:
     * PATCH /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     * Content-Length: (bytes)
     * Content-Type: text/xpath
     *     Request-Body:
     * Value as XPath function
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage-Effects: ... (list of UIDs)
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Element(s) or attribute(s) successfully created or set
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - XPath without addressing a target is responded with status 204
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 413 Payload Too Large
     * - Allowed size of the request(-body) and/or storage is exceeded
     *         HTTP/1.0 415 Unsupported Media Type
     * - Attribute request without Content-Type text/plain
     *         HTTP/1.0 422 Unprocessable Entity
     * - Data in the request body cannot be processed
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doPatch() {

        // PATCH is implemented like PUT.
        // There are some additional conditions and restrictions that will be
        // checked. After that the answer to the request can be passed to PUT.
        // - Pseudo elements are not supported
        // - Target must exist, particularly for attributes

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        // Storage::SPACE also limits the maximum size of writing request(-body).
        // If the limit is exceeded, the request is quit with status 413.
        if (strlen(file_get_contents("php://input")) > Storage::SPACE)
            $this->quit(413, "Payload Too Large");

        // For all PUT requests the Content-Type is needed, because for putting
        // in XML structures and text is distinguished.
        if (!isset($_SERVER["CONTENT_TYPE"]))
            $this->quit(415, "Unsupported Media Type");

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            $message = "Invalid XPath (Functions are not supported)";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $targets = (new DOMXpath($this->xml))->query($this->xpath);
        if (Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }
        if (!$targets || empty($targets) || $targets->length <= 0)
            $this->quit(204, "No Content");

        // The response to the request is delegated to PUT.
        // The function call is executed and the request is terminated.
        $this->doPut();
    }

    /**
     * DELETE deletes elements and attributes in the storage.
     * The position for deletion is defined via an XPath.
     * XPath uses different notations for elements and attributes.
     *
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     *
     * If the XPath notation does not match the attributes, elements are
     * assumed. For elements, the notation for pseudo elements is supported:
     *     <XPath>::first, <XPath>::last, <XPath>::before or <XPath>::after
     * Pseudo elements are a relative position specification to the selected
     * element.
     *
     * The DELETE method works resolutely and deletes existing data.
     * The XPath processing is strict and does not accept unnecessary spaces.
     * The attributes ___rev / ___uid used internally by the storage are
     * read-only and cannot be changed.
     *
     * In general, DELETE requests are responded to with status 204. Status 404
     * is used only with relation to the storage. In all other cases the DELETE
     * method informs the client about changes with status 204 and the response
     * headers Storage-Effects and Storage-Revision. The header Storage-Effects
     * contains a list of the UIDs that were directly affected by the change
     * and also contains the UIDs of newly created elements (e.g. when the root
     * element is deleted, a new one is automatically created). If no changes
     * were made because the XPath cannot find a writable target, the header
     * Storage-Effects can be omitted completely in the response.
     *
     * Syntactic and semantic errors in the request and/or XPath can cause
     * error status 400.
     *
     *     Request:
     * DELETE /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage-Effects: ... (list of UIDs)
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Timeout (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Element(s) or attribute(s) successfully deleted
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - XPath without addressing a target is responded with status 204
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doDelete() {

        // Without existing storage the request is not valid.
        if (!$this->exists())
            $this->quit(404, "Resource Not Found");

        // In any case an XPath is required for a valid request.
        if (empty($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        if (preg_match(Storage::PATTERN_XPATH_FUNCTION, $this->xpath)) {
            $message = "Invalid XPath (Functions are not supported)";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $pseudo = false;
        if (preg_match(Storage::PATTERN_XPATH_ATTRIBUTE, $this->xpath)) {
            $xpath = $this->xpath;
        } else {
            // An XPath for element(s) is then expected here.
            // If this is not the case, the request is responded with status 400.
            if (!preg_match(Storage::PATTERN_XPATH_PSEUDO, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL))
                $this->quit(400, "Bad Request", ["Message" => "Invalid XPath axis"]);
            $xpath = $matches[1];
            $pseudo = $matches[2];
        }

        $targets = (new DOMXpath($this->xml))->query($xpath);
        if (Storage::fetchLastXmlErrorMessage()) {
            $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
            $this->quit(400, "Bad Request", ["Message" => $message]);
        }

        if (!$targets || empty($targets) || $targets->length <= 0)
            $this->quit(204, "No Content");

        // Pseudo elements can be used to delete in an XML substructure
        // relative to the selected element.
        if ($pseudo) {
            if (strcasecmp($pseudo, "before") === 0) {
                $childs = [];
                foreach ($targets as $target) {
                    if (!$target->previousSibling)
                        continue;
                    for ($previous = $target->previousSibling; $previous; $previous = $previous->previousSibling)
                        $childs[] = $previous;
                }
                $targets = $childs;
            } else if (strcasecmp($pseudo, "after") === 0) {
                $childs = [];
                foreach ($targets as $target) {
                    if (!$target->nextSibling)
                        continue;
                    for ($next = $target->nextSibling; $next; $next = $next->nextSibling)
                        $childs[] = $next;
                }
                $targets = $childs;
            } else if (strcasecmp($pseudo, "first") === 0) {
                $childs = [];
                foreach ($targets as $target)
                    if ($target->firstChild)
                        $childs[] = $target->firstChild;
                $targets = $childs;
            } else if (strcasecmp($pseudo, "last") === 0) {
                $childs = [];
                foreach ($targets as $target)
                    if ($target->lastChild)
                        $childs[] = $target->lastChild;
                $targets = $childs;
            } else $this->quit(400, "Bad Request", ["Message" => "Invalid XPath axis (Unsupported pseudo syntax found)"]);
        }

        $serials = [];
        foreach ($targets as $target) {
            if ($target->nodeType === XML_ATTRIBUTE_NODE) {
                if (!$target->parentNode
                        || $target->parentNode->nodeType !== XML_ELEMENT_NODE
                        || in_array($target->name, ["___rev", "___uid"]))
                    continue;
                $parent = $target->parentNode;
                $parent->removeAttribute($target->name);
                $serials[] = $parent->getAttribute("___uid") . ":M";
                Storage::updateNodeRevision($parent, $this->revision +1);
            } else if ($target->nodeType !== XML_DOCUMENT_NODE) {
                if (!$target->parentNode
                        || !in_array($target->parentNode->nodeType, [XML_ELEMENT_NODE, XML_DOCUMENT_NODE]))
                    continue;
                if ($target instanceof DOMElement) {
                    $serials[] = $target->getAttribute("___uid") . ":D";
                    $nodes = (new DOMXpath($this->xml))->query(".//*[@___uid]", $target);
                    foreach ($nodes as $node)
                        $serials[] = $node->getAttribute("___uid") . ":D";
                }
                $parent = $target->parentNode;
                $parent->removeChild($target);
                if ($parent->nodeType === XML_DOCUMENT_NODE) {
                    $target = $this->xml->createElement($this->root);
                    $target = $this->xml->appendChild($target);
                    Storage::updateNodeRevision($target, $this->revision +1);
                    $serial = $this->getSerial();
                    $serials[] = $serial . ":A";
                    $target->setAttribute("___uid", $serial);
                } else {
                    $serials[] = $parent->getAttribute("___uid") . ":M";
                    Storage::updateNodeRevision($parent, $this->revision +1);
                }
            }
        }

        // Only the list of serials is an indicator that data has changed and
        // whether the revision changes with it. If necessary the revision must
        // be corrected if there are no data changes.
        if (!empty($serials))
            header("Storage-Effects: " . join(" ", $serials));

        $this->materialize();
        $this->quit(204, "No Content");
    }

    /**
     * Quit sends a response and ends the connection and closes the storage.
     * The behavior of the method is hard.
     * A response status and a response message are expected.
     * Optionally, additional headers and data for the response body can be
     * passed. Headers for storage and data length are set automatically. Data
     * from the response body is only sent to the client if the response status
     * is in class 2xx. This also affects the dependent headers Content-Type
     * and Content-Length.
     * @param int    $status
     * @param string $message
     * @param array  $headers
     * @param string $data
     */
    function quit($status, $message, $headers = null, $data = null) {

        if (headers_sent()) {
            // The response are already complete.
            // The storage can be closed and the requests can be terminated.
            $this->close();
            exit;
        }

        // This is implemented for scanning and modification of headers.
        // To remove, the headers are set before, so that standard headers like
        // Content-Type are also removed correctly.
        $fetchHeader = function($name, $remove = false) {
            $result = false;
            foreach (headers_list() as $header) {
                if (!preg_match("/^\s*" . $name . "\s*:/i", $header))
                    continue;
                preg_match("/^\s*(.*?)\s*:\s*(.*)\s*$/", $header, $header, PREG_UNMATCHED_AS_NULL);
                if ($remove) {
                    header($header[1] . ":");
                    header_remove($header[1]);
                }
                $result = (object)["name" => $header[1], "value" => $header[2]];
            }
            return $result;
        };

        header(trim("HTTP/1.0 $status $message"));

        // Workaround to remove all default headers.
        // Some must be set explicitly before removing works.
        header("Content-Type:");
        header("Content-Length:");

        // Not relevant headers are removed.
        $filter = ["X-Powered-By", "Content-Type", "Content-Length"];
        foreach ($filter as $header)
            $fetchHeader($header, true);

        if (!empty(Storage::CORS))
            foreach (Storage::CORS as $key => $value)
                header("$key: $value");

        // Access-Control headers are received during preflight OPTIONS request
        if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
            if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
                header("Access-Control-Allow-Methods: CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE");
            if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]))
                header("Access-Control-Allow-Headers: {$_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]}");
        }

        if (!$headers)
            $headers = [];

        // For status class 2xx the storage headers are added.
        // The revision is read from the current storage because it can change.
        if ($status >= 200 && $status < 300
                && $this->storage
                && $this->xml) {
            $expiration = new DateTime();
            $expiration->add(new DateInterval("PT" . Storage::TIMEOUT . "S"));
            $expiration = $expiration->format("D, d M Y H:i:s T");
            $headers = array_merge($headers, [
                "Storage" => $this->storage,
                "Storage-Revision" => $this->xml->documentElement->getAttribute("___rev"),
                "Storage-Space" => Storage::SPACE . "/" . $this->getSize() . " bytes",
                "Storage-Last-Modified" => date("D, d M Y H:i:s T"),
                "Storage-Expiration" => $expiration,
                "Storage-Expiration-Time" => (Storage::TIMEOUT *1000) . " ms"
            ]);
        }

        // The response from the Storage-Effects header can be very extensive.
        // With the Request-Header Accept-Effects you can define which classes
        // of UIDs are returned to the client, comparable to a filter.
        // There are the classes case-insensitive  ADD, MODIFIED and DELETED
        // and the pseudonym NONE, which deselects all classes and ALL, which
        // selects all classes.
        // If no Accept-Effects header is specified, the default is:
        //     ADDED MODIFIED
        // Except for the DELETE method, which is the default:
        //     MODIFIED DELETED
        // Sorting of efficacy / priority (1 is highest):
        //     1:ALL 2:NONE 3:DELETED 3:MODIFIED 3:ADDED

        // Before that, the effects are minimized by removing obsolete entries.
        // Obsolete entries are caused by a relative XPath in the PUT, PATCH
        // and DELETE methods when elements are recursively modified and then
        // also deleted.

        $serials = $fetchHeader("Storage-Effects", true);
        $serials = $serials ? $serials->value : "";
        if (!empty($serials)) {
            $serials = preg_split("/\s+/", $serials);
            $serials = array_unique($serials);
            foreach ($serials as $serial) {
                if (substr($serial, -2) !== ":D")
                    continue;
                $search = substr($serial, 0, -2) . ":M";
                if (in_array($search, $serials))
                    unset($serials[array_search($search, $serials)]);
                $search = substr($serial, 0, -2) . ":A";
                if (in_array($search, $serials))
                    unset($serials[array_search($search, $serials)]);
            }
            $serials = implode(" ", $serials);
        }

        $accepts = isset($_SERVER["HTTP_ACCEPT_EFFECTS"]) ? strtolower(trim($_SERVER["HTTP_ACCEPT_EFFECTS"])) : "";
        $accepts = !empty($accepts) ? preg_split("/\s+/", $accepts) : [];
        $pattern = [];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== "DELETE") {
            if (!empty($accepts)
                    && !in_array("added", $accepts))
                $pattern[] = "A";
            if (empty($accepts)
                    || !in_array("deleted", $accepts))
                $pattern[] = "D";
        } else {
            if (empty($accepts)
                    || !in_array("added", $accepts))
                $pattern[] = "A";
            if (!empty($accepts)
                    && !in_array("deleted", $accepts))
                $pattern[] = "D";
        }
        if (!empty($accepts)
                && !in_array("modified", $accepts))
            $pattern[] = "M";
        if (!empty($accepts)
                && in_array("none", $accepts))
            $pattern = ["A", "M", "D"];
        if (!empty($accepts)
                && in_array("all", $accepts))
            $pattern = [];
        if (!empty($pattern))
            $serials = preg_replace("/\s*\w+:\w+:[" . implode("|", $pattern) . "]\s*/i", " ", $serials);
        $serials = trim(preg_replace("/\s{2,}/", " ", $serials));
        if (!empty($serials))
            header("Storage-Effects: " . $serials);

        foreach ($headers as $key => $value) {
            if (strcasecmp($key, "Content-Length") === 0)
                continue;
            $value = trim(preg_replace("/[\r\n]+/", " ", $value));
            if (strlen(trim($value)) > 0)
                header("$key: $value");
            else header_remove($key);
        }

        $media = $fetchHeader("Content-Type", true);
        if ($status == 200
                && $data !== ""
                && $data !== null) {
            if (!$media) {
                if (in_array("json", $this->options)) {
                    $media = Storage::CONTENT_TYPE_JSON;
                    if ($data instanceof DOMDocument
                            || $data instanceof SimpleXMLElement)
                        $data = simplexml_import_dom($data);
                    $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                } else {
                    if ($data instanceof DOMDocument
                            || $data instanceof SimpleXMLElement) {
                        $media = Storage::CONTENT_TYPE_XML;
                        $data = $data->saveXML();
                    } else $media = Storage::CONTENT_TYPE_TEXT;
                }
            } else $media = $media->value;
            header("Content-Type: $media");
        }

        if ($status >= 200 && $status < 300)
            if (($data !== "" && $data !== null)
                    || $status == 200)
                header("Content-Length: " . strlen($data));

        header("Execution-Time: " . round((microtime(true) -$_SERVER["REQUEST_TIME_FLOAT"]) *1000) . " ms");

        {{{

        // Trace is primarily intended to simplify the validation of requests,
        // their impact on storage and responses during testing.
        // Based on hash values the correct function can be checked.
        // In the released versions the implementation is completely removed.
        // Therefore the code may use computing time or the implementation may
        // not be perfect.

        $fetchRequestHeader = function(...$names) {
            foreach ($names as $name)
                if (isset($_SERVER[$name])
                        && !empty($_SERVER[$name]))
                  return $_SERVER[$name];
            return "";
        };

        // Request-Header-Hash
        $uri = $_SERVER["REQUEST_URI"];
        if (isset($_SERVER["REQUEST"])
                && preg_match(Storage::PATTERN_HTTP_REQUEST, $_SERVER["REQUEST"], $uri, PREG_UNMATCHED_AS_NULL))
            $uri = $uri[2];

        $hash = [];
        if ($fetchRequestHeader("REQUEST_METHOD"))
            $hash["Method"] = strtoupper($fetchRequestHeader("REQUEST_METHOD"));
        if (urldecode($uri))
            $hash["URI"] = urldecode($uri);
        if ($fetchRequestHeader("HTTP_STORAGE"))
            $hash["Storage"] = $fetchRequestHeader("HTTP_STORAGE");
        if ($fetchRequestHeader("HTTP_CONTENT_LENGTH", "CONTENT_LENGTH"))
            $hash["Content-Length"] = strtoupper($fetchRequestHeader("HTTP_CONTENT_LENGTH", "CONTENT_LENGTH"));
        if ($fetchRequestHeader("HTTP_CONTENT_TYPE", "CONTENT_TYPE"))
            $hash["Content-Type"] = strtoupper($fetchRequestHeader("HTTP_CONTENT_TYPE", "CONTENT_TYPE"));
        $hash = json_encode($hash, JSON_UNESCAPED_SLASHES);

        header("Trace-Request-Header-Hash: " . hash("md5", $hash));
        $trace = [hash("md5", $hash) . " Trace-Request-Header-Hash", $hash];

        // Request-Body-Hash
        $hash = file_get_contents("php://input");
        $hash = preg_replace("/((\r\n)|(\n\r)|\r)+/", "\n", $hash);
        $hash = preg_replace("/\t/", " ", $hash);
        header("Trace-Request-Body-Hash: " . hash("md5", $hash));
        $trace = array_merge($trace, [hash("md5", $hash) . " Trace-Request-Body-Hash"]);

        // Response-Header-Hash
        // Only the XMEX relevant headers are used.
        $filter = ["Allow", "Storage", "Storage-Revision", "Storage-Space", "Content-Length", "Message"];
        $filter = array_map("strtolower", $filter);
        $headers = headers_list();
        foreach ($headers as $header)
            if (!in_array(strtolower(trim(preg_replace("/:.*$/", " ", $header))), $filter)) {
                $index = array_search($header, $headers);
                if ($index !== false)
                    unset($headers[$index]);
            }

        // Optional meta info like charset or encoding in the Content-Type are removed
        $header = $fetchHeader("Content-Type", false);
        if ($header)
            $headers[] = "Content-Type: " . preg_replace("/^(.*?)(\;.*)?$/", "$1", $header->value);
        asort($headers);

        $headers = array_merge($headers);

        // Storage-Effects are never the same with UIDs.
        // Therefore, the UIDs are normalized and the header is simplified to
        // make it comparable. To do this, it is only determined how many
        // uniques there are, in which order they are arranged and which
        // serials each unique has.
        $header = $fetchHeader("Storage-Effects");
        if (!empty($header)
                && !empty($header->value)) {
            $counter = [0, 0, 0, 0];
            $effects = [];
            foreach (preg_split("/\s+/", $header->value) as $uid) {
                $uid = preg_split("/:/", $uid);
                if (!array_key_exists($uid[0], $effects))
                    $effects[$uid[0]] = [];
                $effects[$uid[0]][] = $uid[1];
                if (count($uid) < 3)
                    $counter[3]++;
                else if (strcasecmp($uid[2], "A") === 0)
                    $counter[0]++;
                else if (strcasecmp($uid[2], "M") === 0)
                    $counter[1]++;
                else if (strcasecmp($uid[2], "D") === 0)
                    $counter[2]++;
            }
            ksort($effects);
            foreach($effects as $serial => $index) {
                asort($effects[$serial]);
                $effects[$serial] = implode(":", $effects[$serial]);
            }
            $headers[] = "Storage-Effects: " . $counter[0] . "xA/" . $counter[1] . "xM/" . $counter[2] . "xD/" . $counter[3] . "xN"
                . " #" . implode(" #", array_values($effects));
        }

        // Connection-Unique header is unique and only checked for presence.
        if ($fetchHeader("Connection-Unique"))
            $headers[] = "Connection-Unique";

        // Status Message should not be used because different hashes may be
        // calculated for tests on different web servers.
        $headers[] = $status;
        asort($headers, SORT_STRING);
        $headers = array_merge($headers);
        header("Trace-Response-Header-Hash: " . hash("md5", implode("\n", $headers)));
        $trace = array_merge($trace, [hash("md5", implode("\n", $headers)) . " Trace-Response-Header-Hash", json_encode($headers, JSON_UNESCAPED_SLASHES)]);

        // Response-Body-Hash
        $hash = $data;
        $hash = preg_replace("/((\r\n)|(\n\r)|\r)+/", "\n", $hash);
        $hash = preg_replace("/\t/", " ", $hash);
        // The UID is variable and must be normalized so that the hash can be
        // compared later. Therefore the uniques of the UIDs are collected in
        // an array. The index in the array is then the new unique.
        if (preg_match_all("/\b___uid(?:(?:=)|(?:\"\s*:\s*))\"[A-Z\d\:]+\"/i", $hash, $matches, PREG_PATTERN_ORDER )) {
            $uniques = [];
            foreach ($matches[0] as $unique) {
                if (preg_match("/\b(___uid(?:(?:=)|(?:\"\s*:\s*))\")([A-Z\d]+)(:[A-Z\d]+\")/i", $unique, $match)) {
                    if (!in_array($match[2], $uniques))
                        $uniques[] = $match[2];
                    $unique = array_search($match[2], $uniques);
                    $hash = str_replace($match[0], $match[1] . $unique . $match[3], $hash);
                }
            }
        }
        header("Trace-Response-Body-Hash: " . hash("md5", $hash));
        $trace = array_merge($trace, [hash("md5", $hash) . " Trace-Response-Body-Hash"]);

        // Storage-Hash
        // Also the storage cannot be compared directly, because here the UID's
        // use a unique changeable prefix. Therefore the XML is reloaded and
        // all ___uid attributes are normalized. For this purpose, the unique
        // of the UIDs is determined, sorted and then replaced by the index
        // during sorting.
        $hash = $this->xml ? $this->xml->saveXml() : "";
        if ($hash) {
            $xml = new DOMDocument();
            $xml->loadXML($hash);
            $targets = (new DOMXpath($xml))->query("//*[@___uid]");
            $uniques = [];
            foreach ($targets as $target)
                $uniques[] = $target->getAttribute("___uid");
            asort($uniques);
            foreach ($uniques as $index => $uid) {
                $target = (new DOMXpath($xml))->query("//*[@___uid=\"$uid\"]")->item(0);
                $target->setAttribute("___uid", preg_replace("/^.*(?=:)/", $index, $uid));
            }
            $hash = $xml->saveXml();
        }
        $hash = preg_replace("/\s+/", " ", $hash);
        $hash = preg_replace("/\s+(?=[<>])/", "", $hash);
        $hash = trim($hash);
        header("Trace-Storage-Hash: " . hash("md5", $hash));
        $trace = array_merge($trace, [hash("md5", $hash) . " Trace-Storage-Hash"]);

        header("Trace-XPath-Hash: " . hash("md5", $this->xpath));
        $trace = array_merge($trace, [hash("md5", $this->xpath) . " Trace-XPath-Hash", $this->xpath]);

        $hash = [
            $fetchHeader("Trace-Request-Header-Hash")->value,
            $fetchHeader("Trace-Request-Body-Hash")->value,
            $fetchHeader("Trace-Response-Header-Hash")->value,
            $fetchHeader("Trace-Response-Body-Hash")->value,
            $fetchHeader("Trace-Storage-Hash")->value,
            $fetchHeader("Trace-XPath-Hash")->value
        ];
        header("Trace-Composite-Hash: " . hash("md5", implode(" ", $hash)));
        $trace = array_merge($trace, [hash("md5", implode(" ", $hash)) . " Trace-Composite-Hash"]);
        $trace = array_filter($trace, function($entry) {
            return $entry !== null && strlen(trim($entry)) > 0;
        });

        $trace = "\t" . implode(PHP_EOL . "\t", $trace) . PHP_EOL;
        if ($this->xml && $this->xml->documentElement)
            $trace = "\tStorage Identifier: " . $this->storage . " Revision:" . $this->xml->documentElement->getAttribute("___rev") . " Space:" . $this->getSize() . PHP_EOL . $trace;
        $trace = "\tResponse Status:" . $status . " Length:" . strlen($data) . PHP_EOL . $trace;
        $trace = "\tRequest Method:" . strtoupper($_SERVER["REQUEST_METHOD"]) . " XPath:" . $this->xpath . " Length:" . strlen(file_get_contents("php://input")) . PHP_EOL . $trace;
        $trace = hash("md5", implode(" ", $hash)) . PHP_EOL . $trace;

        if (file_exists("trace.log")
                && filesize("trace.log") > 0
                && (time() -filemtime("trace.log")) > 1)
            file_put_contents("trace.log", PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents("trace.log", $trace, FILE_APPEND | LOCK_EX);

        }}}

        if ($status >= 200 && $status < 300
                && $data !== "" && $data !== null)
            print($data);

        // The function and the response are complete.
        // The storage can be closed and the requests can be terminated.
        $this->close();
        exit;
    }

    /**
     * Returns the last caused XML error, otherwise FALSE
     * @return mixed the last caused XML error, otherwise FALSE
     */
    private static function fetchLastXmlErrorMessage() {

        if (empty(libxml_get_errors()))
            return false;
        $message = libxml_get_errors();
        $message = end($message)->message;
        $message = preg_replace("/[\r\n]+/", " ", $message);
        $message = preg_replace("/\.+$/", " ", $message);
        return trim($message);
    }

    /**
     * General error handling.
     * Writes a formatted log file in the working directory and quits the
     * request with an error status.
     * @param string  $error
     * @param string  $message
     * @param string  $file
     * @param integer $line
     */
    static function onError($error, $message, $file, $line) {

        // Special case XSLTProcessor errors
        // These cannot be caught any other way. Therefore the error header
        // is implemented here.
        $filter = "XSLTProcessor::transformToXml()";
        if (substr($message, 0, strlen($filter)) === $filter) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            (new Storage)->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        $unique = "#" . Storage::uniqueId();
        $message = "$message" . PHP_EOL . "\tat $file $line";
        if (!is_numeric($error))
            $message = "$error:" . $message;
        $time = time();
        file_put_contents(date("Ymd", $time) . ".log", date("Y-m-d H:i:s", $time) . " $unique $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
        (new Storage)->quit(500, "Internal Server Error", ["Error" => $unique]);
    }

    /**
     * General exception handling.
     * Writes a formatted log file in the working directory and quits the
     * request with an error status.
     * @param Exception $exception
     */
    static function onException($exception) {
        Storage::onError(get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }
}

date_default_timezone_set ("GMT");
set_error_handler("Storage::onError");
set_exception_handler("Storage::onException");

// The API should always be used by URI mapping, so that a separation between
// URI and XPath is also visually recognizable. The direct call from the script
// is responded with status 404.
$script = basename(__FILE__);
if (isset($_SERVER["PHP_SELF"])
        && preg_match("/\/" . str_replace(".", "\\.", $script) . "([\/\?].*)?$/", $_SERVER["PHP_SELF"])
        && (!isset($_SERVER["REDIRECT_URL"])
                || empty($_SERVER["REDIRECT_URL"])))
    (new Storage)->quit(404, "Resource Not Found");

// Request method is determined
$method = strtoupper($_SERVER["REQUEST_METHOD"]);

// Access-Control headers are received during preflight OPTIONS request
if ($method === "OPTIONS"
        && isset($_SERVER["HTTP_ORIGIN"])
        && !isset($_SERVER["HTTP_STORAGE"]))
    (new Storage)->quit(204, "No Content");

$storage = null;
if (isset($_SERVER["HTTP_STORAGE"]))
    $storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match(Storage::PATTERN_HEADER_STORAGE, $storage))
    (new Storage)->quit(400, "Bad Request", ["Message" => "Invalid storage identifier"]);

// The XPath is determined from REQUEST_URI or alternatively from REQUEST
// because some servers normalize the paths and URI for the CGI.
// It was not easy to determine the context path for all servers safely and
// then extract the XPath from the request. Therefore it was decided that the
// context path and XPath are separated by a symbol or a symbol sequence.
// The behavior can be customized with Storage::PATTERN_HTTP_REQUEST_URI.
// If the pattern is empty, null or false, the request URI without context path
// will be used. This is helpful when the service is used as a domain.
$xpath = $_SERVER["REQUEST_URI"];
if (Storage::PATTERN_HTTP_REQUEST_URI) {
    if (isset($_SERVER["REQUEST"])
            && preg_match(Storage::PATTERN_HTTP_REQUEST, $_SERVER["REQUEST"], $xpath, PREG_UNMATCHED_AS_NULL))
        $xpath = $xpath[2];
    $xpath = preg_match(Storage::PATTERN_HTTP_REQUEST_URI, $xpath, $xpath, PREG_UNMATCHED_AS_NULL) ? $xpath[2] : "";
}
if (preg_match("/^0x([A-Fa-f0-9]{2})+$/", $xpath))
    $xpath = hex2bin(substr($xpath, 2));
else if (preg_match("/^Base64:[A-Za-z0-9\+\/]+=*$/", $xpath))
    $xpath = base64_decode(substr($xpath, 7));
else $xpath = urldecode($xpath);

// With the exception of CONNECT, OPTIONS and POST, all requests expect an
// XPath or XPath function.
// CONNECT and OPTIONS do not use an (X)Path to establish a storage.
// POST uses the XPath for transformation only optionally to delimit the XML
// data for the transformation and works also without.
// In the other cases an empty XPath is replaced by the root slash.
if (empty($xpath)
        && !in_array($method, ["CONNECT", "OPTIONS", "POST"]))
    $xpath = "/";
$exclusive = in_array($method, ["DELETE", "PATCH", "PUT"]);
$storage = Storage::share($storage, $xpath, $exclusive);

try {
    switch ($method) {
        case "CONNECT":
            $storage->doConnect();
        case "OPTIONS":
            $storage->doOptions();
        case "GET":
            $storage->doGet();
        case "POST":
            $storage->doPost();
        case "PUT":
            $storage->doPut();
        case "PATCH":
            $storage->doPatch();
        case "DELETE":
            $storage->doDelete();
        default:
            $storage->quit(405, "Method Not Allowed", ["Allow" => "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE"]);
    }
} finally {
    $storage->close();
}
?>