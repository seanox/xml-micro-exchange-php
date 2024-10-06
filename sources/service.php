<?php
/**
 * LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
 * Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 * Diese Software unterliegt der Version 2 der Apache License.
 *
 * XMEX XML-Micro-Exchange
 * Copyright (C) 2024 Seanox Software Solutions
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
 * XML-Micro-Exchange is a volatile RESTful micro datasource. It is designed for
 * easy communication and data exchange of web-applications and for IoT. The XML
 * based datasource is volatile and lives through continuous use and expires
 * through inactivity. They are designed for active and near real-time data
 * exchange but not as a real-time capable long-term storage. Compared to a JSON
 * storage, this datasource supports more dynamics, partial data access, data
 * transformation, and volatile short-term storage.
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
 * The data areas managed by the XML-Micro-Exchange as a data service are called
 * storage areas. A storage area corresponds to an XML file in the data
 * directory.
 *
 *         Storage Identifier
 * Each storage has an identifier, the Storage Identifier. The Storage
 * Identifier is used as the filename of the corresponding XML file and must be
 * specified with each request so that the datasource uses the correct storage.
 *
 *         Element(s)
 * The content of the XML file of a storage provide the data as object or tree
 * structure. The data entries are called elements. Elements can enclose other
 * elements.
 *
 *         Attribute(s)
 * Elements can also contain direct values in the form of attributes.
 *
 *         XPath
 * XPath is a notation for accessing and navigating the XML data structure. An
 * XPath can be an axis or a function.
 *
 *         XPath Axis
 * XPath axes address or select elements or attributes. The axes can have a
 * multidimensional effect.
 *
 *         XPath Axis Pseudo Elements
 * For PUT requests it is helpful to specify a relative navigation to an XPath
 * axis. For example first, last, before, after. This extension of the notation
 * is supported for PUT requests and is added to an XPath axis separated by two
 * colons at the end (e.g. /root/element::end - means put in element as last).
 *
 *         XPath Function
 * The XPath notation also supports functions that can be used in combination
 * with axes and standalone for dynamic data requests. In combination with XPath
 * axes, the addressing and selection of elements and attributes can be made
 * dynamic.
 *
 *        Revision
 * Every change in a storage is expressed as a revision. This should make it
 * easier for the client to determine whether data has changed, even for partial
 * requests. Depending on the configuration (XMEX_STORAGE_REVISION_TYPE), the
 * revision is an auto-incremental integer starting with 1 or an alphanumeric
 * timestamp.
 *
 * Each element uses a revision in the read-only attribute ___rev, which, as
 * with all parent revision attributes, is automatically update when it changes.
 * A change can affect the element itself or the change to its children. Because
 * the revision is passed up, the root element automatically always uses the
 * current revision.
 *
 * Changes are: PUT, PATCH, DELETE
 *
 * Write accesses to attribute ___rev are accepted with status 204.
 *
 *       UID
 * Each element uses a unique identifier in the form of the read-only attribute
 * ___uid. The unique identifier is automatically created when an element is put
 * into storage and never changes. The UID is based on the current revision,
 * which, depending on the configuration (XMEX_STORAGE_REVISION_TYPE), is an
 * alphanumeric timestamp or an automatically incremented integer. The UID is
 * thus also sortable and provides information about the order in which elements
 * are created.
 *
 * Write accesses to attribute ___uid are accepted with status 204.
 *
 *     REQUEST
 * The implementation works RESTfull and uses normal HTTP request. For the
 * addressing of targets XPath axes and XPath functions are used, which are
 * transmitted as part of the URI path. Because XPath has a different structure
 * than the URI, even if it uses similar characters, clients and/or gateways may
 * experience syntax problems when optimizing the request. For this reason
 * different ways of transmission and escape are supported for the XPath.
 *
 *        URI (not escaped)
 * e.g. /xmex!//book[last()]/chapter[last()]
 *
 *        URI + URL Encoding
 * The XPath is used URL encoding.
 * e.g. /xmex!//book%5Blast()%5D/chapter%5Blast()%5D
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *        XPath as hexadecimal string
 * The URI starts with  after the XPath separator:
 * e.g. /xmex!2f2f626f6f6b5b6c61737428295d2f636861707465725b6c61737428295d
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *        XPath as Base64 encoded string
 * The URI starts with Base64 after the XPath separator:
 * e.g. /xmex!Ly9ib29rW2xhc3QoKV0vY2hhcHRlcltsYXN0KCld
 * is equivalent to: /xmex!//book[last()]/chapter[last()]
 *
 *     TRANSACTION / SIMULTANEOUS ACCESS
 * XML-Micro-Exchange supports simultaneous access. Read accesses are executed
 * simultaneously. Write accesses creates a lock and avoids dirty reading.
 *
 *     ERROR HANDLING
 * Errors are communicated via the server status 500 and the header 'Error'. The
 * header 'Error' contains only an error number, for security reasons no
 * details. The error number with details can be found in the log file of the
 * service. In the case of status 400 and 422, XML-Micro-Exchange uses the
 * additional header Message in the response, which contains more details about
 * the error. The difference between status 400 and 422 is that status 400
 * always refers to the request and 422 to the request body. With status 400
 * errors are detected in the request itself, and with status 422, errors are
 * detected in the content of the request body.
 *
 *     SECURITY
 * This aspect was deliberately considered and implemented here only in a very
 * rudimentary form. Only the storage(-key) with a length of 1 - 64 characters
 * can be regarded as secret. For further security the approach of Basic
 * Authentication, Digest Access Authentication and/or Server/Clien
 * certificates is followed, which is configured at the web server and outside
 * of the XMEX (XML-Micro-Exchange) .
 */

// For the environment variables, PHP constants are created so that they can be
// assigned as static values to the constants in the class!

/** TODO */
define("XMEX_DEBUG_MODE", in_array(strtolower(getenv("XMEX_DEBUG_MODE", true)), array("on", "true", "1")));

/** Directory of the data storage */
define("XMEX_STORAGE_DIRECTORY", getenv("XMEX_STORAGE_DIRECTORY", true) ?: "./data");

/** Maximum number of files in data storage */
define("XMEX_STORAGE_QUANTITY", getenv("XMEX_STORAGE_QUANTITY", true) ?: 65535);

/**
 * Maximum data size of files in data storage in bytes.
 * The value also limits the size of the requests(-body).
 */
define("XMEX_STORAGE_SPACE", getenv("XMEX_STORAGE_SPACE", true) ?: 256 *1024);

/** Maximum idle time of the files in seconds */
define("XMEX_STORAGE_EXPIRATION", getenv("XMEX_STORAGE_EXPIRATION", true) ?: 15 *60);

/** TODO */
define("XMEX_STORAGE_REVISION_TYPE", (XMEX_DEBUG_MODE ? "serial" : strcasecmp(getenv("XMEX_STORAGE_REVISION_TYPE", true), "serial") === 0) ? "serial" : "timestamp");

/** Character or character sequence of the XPath delimiter in the URI */
define("XMEX_URI_XPATH_DELIMITER", getenv("XMEX_URI_XPATH_DELIMITER", true) ?: "!");

class Storage {

    /** Directory of the data storage */
    const DIRECTORY = XMEX_STORAGE_DIRECTORY;

    /** Maximum number of files in data storage */
    const QUANTITY = XMEX_STORAGE_QUANTITY;

    /**
     * Maximum data size of files in data storage in bytes.
     * The value also limits the size of the requests(-body).
     */
    const SPACE = XMEX_STORAGE_SPACE;

    /** Maximum idle time of the files in seconds */
    const EXPIRATION = XMEX_STORAGE_EXPIRATION;

    /** Character or character sequence of the XPath delimiter in the URI */
    const DELIMITER = XMEX_URI_XPATH_DELIMITER;

    /** TODO */
    const DEBUG_MODE = XMEX_DEBUG_MODE;

    /** TODO */
    const REVISION_TYPE = XMEX_STORAGE_REVISION_TYPE;

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

    /** Pattern for detecting Base64 decoding */
    const PATTERN_BASE64 = "/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/";

    /** Pattern for detecting HEX decoding */
    const PATTERN_HEX = "/^([A-Fa-f0-9]{2})+$/";

    /** Pattern for recognizing non-numerical values */
    const PATTERN_NON_NUMERICAL = "/^.*\D/";

    /**
     * Pattern to determine a HTTP request
     *     Group 0. Full match
     *     Group 1. Method
     *     Group 2. URI
     *     Group 3. Protocol
     */
    const PATTERN_HTTP_REQUEST = "/^([A-Z]+)\s+(.+)\s+(HTTP\/\d+(?:\.\d+)*)$/i";

    /**
     * Pattern for separating URI-Path and XPath.
     * If the pattern is empty, null or false, the request URI without context
     * path will be used. This is helpful when the service is used as a domain.
     *     Group 0. Full match
     *     Group 1. URI-Path
     *     Group 2. XPath
     */
    const PATTERN_HTTP_REQUEST_URI = "/^(.*?)" . Storage::DELIMITER . "(.*)$/i";

    /**
     * Pattern for the Storage header
     *     Group 0. Full match
     *     Group 1. Storage
     *     Group 2. Name of the root element (optional)
     */
    const PATTERN_HEADER_STORAGE = "/^(\w(?:[-\w]{0,62}\w)?)(?:\s+(\w{1,64}))?$/";

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
     * notation, the round brackets can be ignored, the question remains, if the
     * XPath starts with an axis symbol, then it is an axis, with other
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

    /** Constants of share options */
    const STORAGE_SHARE_NONE      = 0;
    const STORAGE_SHARE_EXCLUSIVE = 1;
    const STORAGE_SHARE_INITIAL   = 2;

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
        if (preg_match(Storage::PATTERN_XPATH_OPTIONS, $xpath ?: "", $matches, PREG_UNMATCHED_AS_NULL)) {
            $xpath = $matches[1];
            $options = array_merge(array_filter(explode("!", strtolower($matches[2]))));
        }

        if (!empty($storage))
            $root = $root ?: "data";
        else $root = null;
        $store = null;
        if (!empty($storage)) {
            // The file name from the storage is case-sensitive, which is not
            // automatically supported by Windows by default. The file name must
            // therefore be formatted so that case-sensitive characteristics are
            // retained but the spelling is case-insensitive. For this purpose,
            // the lower case transitions are marked with a special character.
            // Afterwards, the file name can be used in lower case letters. The
            // idea of simply converting everything to hexadecimal was rejected
            // due to the length of the file name.
            $store = $storage . "[" . $root . "]";
            $store = preg_replace("/(^|[^a-z])([a-z])/", "$1'$2", $store);
            $store = preg_replace("/([a-z])([^a-z]|$)/", "$1'$2", $store);
            $store = Storage::DIRECTORY . "/" . strtolower($store);
        }

        $this->storage  = $storage;
        $this->root     = $root;
        $this->store    = $store;
        $this->xpath    = $xpath;
        $this->options  = $options;
        $this->serial   = 0;
        $this->unique   = null;
        $this->revision = null;
    }

    /** Cleans up all files that have exceeded the maximum idle time. */
    private static function cleanUp() {

        if (!is_dir(Storage::DIRECTORY))
            return;

        // To reduce the execution time of the requests, the cleanup is only
        // carried out every minute. Parallel cleanup due to parallel requests
        // cannot be excluded, but this should not be a problem.
        $marker = Storage::DIRECTORY . "/cleanup";
        if (file_exists($marker)
                && time() -filemtime($marker) < 60)
            return;
        touch($marker);

        if ($handle = opendir(Storage::DIRECTORY)) {
            $timeout = time() -Storage::EXPIRATION;
            while (($entry = readdir($handle)) !== false) {
                if ($entry == "."
                        || $entry == ".."
                        || $entry == "cleanup")
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
     * Opens a storage with a XPath for the current request. The storage can be
     * opened with various options, which are passed as a bit mask. If the
     * storage to be opened does not yet exist, it is initialized with option
     * Storage::STORAGE_SHARE_INITIAL, otherwise the request will be terminated.
     * With option Storage::STORAGE_SHARE_EXCLUSIVE, simultaneous
     * requests must wait for a file lock.
     * @param  string  $storage
     * @param  string  $xpath
     * @param  int     $options
     * @return Storage Instance of the Storage
     */
    static function share($storage, $xpath, $options = Storage::STORAGE_SHARE_NONE) {

        $root = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$2", $storage);
        $storage = preg_replace(Storage::PATTERN_HEADER_STORAGE, "$1", $storage);
        if (!file_exists(Storage::DIRECTORY))
            mkdir(Storage::DIRECTORY, 0755, true);
        $storage = new Storage($storage, $root, $xpath);

        // The cleanup does not run permanently, so the possible expiry is
        // checked before access and the storage is deleted if necessary.
        $expiration = time() -Storage::EXPIRATION;
        if (file_exists($storage->store)
                && (filemtime($storage->store) < $expiration
                        || filesize($storage->store) <= 0))
            @unlink($storage->store);

        $initial = ($options & Storage::STORAGE_SHARE_INITIAL) == Storage::STORAGE_SHARE_INITIAL;
        if (!$initial
                && !$storage->exists())
            $storage->quit(404, "Resource Not Found");

        $storage->share = fopen($storage->store, "c+");
        $exclusive = ($options & Storage::STORAGE_SHARE_EXCLUSIVE) == Storage::STORAGE_SHARE_EXCLUSIVE;
        flock($storage->share, filesize($storage->store) <= 0 || $exclusive ? LOCK_EX : LOCK_SH);

        if (strcasecmp(Storage::REVISION_TYPE, "serial") !== 0) {
            $storage->unique = round(microtime(true) *1000);
            while ($storage->unique == round(microtime(true) *1000))
                usleep(1);
            $storage->unique = base_convert($storage->unique, 10, 36);
            $storage->unique = strtoupper($storage->unique);
        } else $storage->unique = 1;

        if ($initial
                && filesize($storage->store) <= 0) {
            $iterator = new FilesystemIterator(Storage::DIRECTORY, FilesystemIterator::SKIP_DOTS);
            if (iterator_count($iterator) >= Storage::QUANTITY)
                $storage->quit(507, "Insufficient Storage");
            fwrite($storage->share,
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
                "<" . $storage->root . " ___rev=\"" . $storage->unique . "\" ___uid=\"" . $storage->getSerial() ."\"/>");
            rewind($storage->share);
            if (strcasecmp(Storage::REVISION_TYPE, "serial") === 0)
                $storage->unique = 0;
        }

        fseek($storage->share, 0, SEEK_END);
        $size = ftell($storage->share);
        rewind($storage->share);
        $storage->xml = new DOMDocument();
        $storage->xml->loadXML(fread($storage->share, $size));
        $storage->revision = $storage->xml->documentElement->getAttribute("___rev");
        if (strcasecmp(Storage::REVISION_TYPE, "serial") === 0) {
            if (preg_match(Storage::PATTERN_NON_NUMERICAL, $storage->revision))
                $storage->quit(503, "Resource revision conflict");
            $storage->unique += $storage->revision;
        }

        return $storage;
    }

    /**
     * Return TRUE if the storage already exists.
     * @return bool TRUE if the storage already exists
     */
    private function exists() {
        return file_exists($this->store)
                && filesize($this->store) > 0;
    }

    /**
     * Materializes the XML document from the memory in the file system. Unlike
     * save, the file is not closed and the data can be modified without another
     * (PHP)process being able to read the data before finalizing it by closing
     * it. Materialization is only executed if there are changes in the XML
     * document, which is determined by the revision of the root element. The
     * size of the storage is limited by Storage::SPACE because it is a volatile
     * micro datasource for short-term data exchange. An overrun causes the
     * status 413.
     */
    function materialize() {

        if ($this->share == null)
            return;
        if ($this->revision == $this->xml->documentElement->getAttribute("___rev")
                && $this->revision != $this->unique)
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
        return $this->unique . ":" . ++$this->serial;
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
     * CONNECT initiates the use of a storage. A storage is a volatile XML
     * construct that is used via a datasource URL. The datasource managed
     * several independent storages. Each storage has a name specified by the
     * client, which must be sent with each request. This is similar to the
     * header host for virtual servers. Optionally, the name of the root element
     * can also be defined by the client.
     *
     * Each client can create a new storage at any time. Communication is
     * established when all parties use the same name. There are no rules, only
     * the clients know the rules. A storage expires with all information if it
     * is not used (read/write).
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
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 201 Resource Created
     * - Response can be status 201 if the storage was newly created
     *         HTTP/1.0 204 No Content
     * - Response can be status 204 if the storage already exists
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     * - An unexpected error has occurred
     *         HTTP/1.0 507 Insufficient Storage
     * - Response can be status 507 if the storage is full
     */
    function doConnect() {

        // Cleaning up can run longer and delay the request. It is least
        // disruptive during the connect. Threads were deliberately omitted here
        // to keep the service simple.
        Storage::cleanUp();

        if ($this->xpath !== null
                && strlen($this->xpath))
            $this->quit(400, "Bad Request", ["Message" => "Unexpected XPath"]);

        $response = [201, "Created"];
        if ($this->revision != $this->unique)
            $response = [204, "No Content"];

        $this->materialize();
        $this->quit($response[0], $response[1], ["Allow" => "CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE"]);
    }

    /**
     * OPTIONS is used to query the allowed HTTP methods for an XPath, which is
     * responded with the header Allow. This method distinguishes between XPath
     * function and XPath axis and for an XPath axis the target exists or not
     * and uses different Allow headers accordingly.
     *
     * Overview of header Allow
     * - XPath function: CONNECT, OPTIONS, GET, POST
     * - XPath axis without target: CONNECT, OPTIONS, PUT
     * - XPath axis with target: CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE
     *
     * The method always executes a transmitted XPath, but does not return the
     * result directly, but reflects the result via different header Allow. The
     * status 404 is not used in relation to the XPath, but only in relation to
     * the storage. The XPath processing is strict and does not accept
     * unnecessary spaces. Faulty XPath will cause the status 400.
     *
     *     Request:
     * OPTIONS /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
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
     */
    function doOptions() {

        // In any case an XPath is required for a valid request.
        if ($this->xpath === null
                || strlen($this->xpath) <= 0)
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
            if (empty($targets) && $targets->length <= 0)
                $allow = "CONNECT, OPTIONS, PUT";
        }

        $this->quit(204, "No Content", ["Allow" => $allow]);
    }

    /**
     * GET queries data about XPath axes and functions. For this, the XPath axis
     * or function is sent with URI. Depending on whether the request is an
     * XPath axis or an XPath function, different Content-Type are used for the
     * response.
     *
     *     application/xml
     * When the XPath axis addresses one target, the addressed target is the
     * root element of the returned XML structure. If the XPath addresses
     * multiple targets, their XML structure is combined in the root element
     * collection.
     *
     *     text/plain
     * If the XPath addresses only one attribute, the value is returned as plain
     * text. Also the result of XPath functions is returned as plain text.
     * Decimal results use float, booleans the values true and false.
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
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
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

        // In any case an XPath is required for a valid request.
        if ($this->xpath === null
                || strlen($this->xpath) <= 0)
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
                &&  (empty($result)
                        || $result->length <= 0)) {
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
     * POST queries data about XPath axes and functions via transformation. For
     * this, an XSLT stylesheet is sent with the request-body, which is then
     * applied by the XSLT processor to the data in storage. Thus the content
     * type application/xslt+xml is always required. The client defines the
     * content type for the output with the output-tag and the method-attribute.
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
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
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
        if ($this->xpath !== null
                && strlen($this->xpath) > 0) {
            $xml = new DOMDocument();
            $targets = (new DOMXpath($this->xml))->query($this->xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (empty($targets)
                    || $targets->length <= 0)
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
     * of existing ones. The position for the insert is defined via an XPath.
     * For better understanding, the method should be called PUT INTO, because
     * it is always based on an existing XPath axis as the parent target. XPath
     * uses different notations for elements and attributes.
     *
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     * The attribute values can be static (text) and dynamic (XPath function).
     * Values are send as request-body. Whether they are used as text or XPath
     * function is decided by the Content-Type header of the request.
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
     * In general, PUT requests are responded to with status 204. Changes at the
     * storage are indicated by the two-part response header Storage-Revision.
     * Status 404 is used only with relation to the storage.
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
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
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

        // In any case an XPath is required for a valid request.
        if ($this->xpath === null
                || strlen($this->xpath) <= 0)
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
        // Multi-axis XPaths allow multiple targets. The method only supports
        // these two possibilities, other requests are responded with an error,
        // because this situation cannot occur because the XPath is recognized
        // as XPath for an attribute and otherwise an element is assumed. In
        // this case it can only happen that the XPath does not address target,
        // which is not an error in the true sense. Therefore there is only one
        // decision here.

        // XPath can address elements and attributes. If the XPath ends with
        // /attribute::<attribute> or /@<attribute> an attribute is expected,
        // in all other cases a element.

        if (preg_match(Storage::PATTERN_XPATH_ATTRIBUTE, $this->xpath, $matches, PREG_UNMATCHED_AS_NULL)) {

            // The following Content-Type is supported for attributes:
            // - text/plain for static values (text)
            // - text/xpath for dynamic values, based on XPath functions

            // For attributes only the Content-Type text/plain and text/xpath
            // are supported, for other Content-Types no conversion exists.
            if (!in_array(strtolower($_SERVER["CONTENT_TYPE"]), [Storage::CONTENT_TYPE_TEXT, Storage::CONTENT_TYPE_XPATH]))
                $this->quit(415, "Unsupported Media Type");

            $input = file_get_contents("php://input");

            // The Content-Type text/xpath is a special of the XMEX Storage. It
            // expects a plain text which is an XPath function. The XPath
            // function is first once applied to the current XML document from
            // the storage and the result is put like the Content-Type
            // text/plain. Even if the target is mutable, the XPath function is
            // executed only once and the result is put on all targets.
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
            if (empty($targets)
                    || $targets->length <= 0)
                $this->quit(204, "No Content");

            // The attributes ___rev and ___uid are essential for the internal
            // organization and management of the data and cannot be changed.
            // PUT requests for these attributes are ignored and behave as if no
            // matching node was found. It should say request understood and
            // executed but without effect.
            if (!in_array($attribute, ["___rev", "___uid"])) {
                foreach ($targets as $target) {
                    // Only elements are supported, this prevents the addressing
                    // of the XML document by the XPath.
                    if ($target->nodeType != XML_ELEMENT_NODE)
                        continue;
                    $target->setAttribute($attribute, $input);
                    $this->serial++;
                    // The revision is updated at the parent nodes, so you can
                    // later determine which nodes have changed and with which
                    // revision. Partial access allows the client to check if
                    // the data or a tree is still up to date, because he can
                    // compare the revision.
                    Storage::updateNodeRevision($target, $this->unique);
                }
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

            // The Content-Type text/xpath is a special of the XMEX Storage. It
            // expects a plain text which is an XPath function. The XPath
            // function is first once applied to the current XML document from
            // the storage and the result is put like the Content-Type
            // text/plain. Even if the target is mutable, the XPath function is
            // executed only once and the result is put on all targets.
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

            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (empty($targets)
                    || $targets->length <= 0)
                $this->quit(204, "No Content");

            foreach ($targets as $target) {
                // Overwriting of the root element is not possible, as it is an
                // essential part of the storage, and is ignored. It does not
                // cause to an error, so the behaviour is analogous to putting
                // attributes.
                if ($target->nodeType != XML_ELEMENT_NODE)
                    continue;
                $replace = $target->cloneNode(false);
                $replace->appendChild($this->xml->createTextNode($input));
                $target->parentNode->replaceChild($this->xml->importNode($replace, true), $target);
                // The revision is updated at the parent nodes, so you can later
                // determine which nodes have changed and with which revision.
                // Partial access allows the client to check if the data or a
                // tree is still up to date, because he can compare the
                // revision.
                Storage::updateNodeRevision($replace, $this->unique);
            }

            $this->materialize();
            $this->quit(204, "No Content");
        }

        // Only an XML structure can be inserted, nothing else is supported. So
        // only the Content-Type application/xml can be used.
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
        // organization and management of the data and cannot be changed. When
        // inserting, the attributes ___rev and ___uid are set automatically.
        // These attributes must not be  contained in the XML structure to be
        // inserted, because all XML elements without ___uid attributes are
        // determined after insertion and it is assumed that they have been
        // newly inserted. This approach was chosen to avoid a recursive
        // search/iteration in the XML structure to be inserted.
        $nodes = (new DOMXpath($xml))->query("//*[@___rev|@___uid]");
        foreach ($nodes as $node) {
            $node->removeAttribute("___rev");
            $node->removeAttribute("___uid");
        }

        if ($xml->documentElement->hasChildNodes()) {
            $targets = (new DOMXpath($this->xml))->query($xpath);
            if (Storage::fetchLastXmlErrorMessage()) {
                $message = "Invalid XPath axis (" . Storage::fetchLastXmlErrorMessage() . ")";
                $this->quit(400, "Bad Request", ["Message" => $message]);
            }
            if (empty($targets)
                    || $targets->length <= 0)
                $this->quit(204, "No Content");

            foreach ($targets as $target) {

                // Overwriting of the root element is not possible, as it is an
                // essential part of the storage, and is ignored. It does not
                // cause to an error, so the behaviour is analogous to putting
                // attributes.
                if ($target->nodeType != XML_ELEMENT_NODE)
                    continue;

                if (!empty($pseudo))
                    $pseudo = strtolower($pseudo);

                // Pseudo elements can be used to put in an XML substructure
                // relative to the selected element.
                if (empty($pseudo)) {
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

        // The attribute ___uid of all newly inserted elements is set. It is
        // assumed that all elements without the ___uid attribute are new. The
        // revision of all affected nodes are updated, so you can later
        // determine which nodes have changed and with which revision. Partial
        // access allows the client to check if the data or a tree is still up
        // to date, because he can compare the revision.
        $nodes = (new DOMXpath($this->xml))->query("//*[not(@___uid)]");
        foreach ($nodes as $node) {
            $node->setAttribute("___uid", $this->getSerial());
            Storage::updateNodeRevision($node, $this->unique);
        }

        $this->materialize();
        $this->quit(204, "No Content");
    }

    /**
     * PATCH changes existing elements and attributes in storage. The position
     * for the insert is defined via an XPath. The method works almost like PUT,
     * but the XPath axis of the request always expects an existing target.
     * XPath uses different notations for elements and attributes.
     *
     * The notation for attributes use the following structure at the end.
     *     <XPath>/@<attribute> or <XPath>/attribute::<attribute>
     * The attribute values can be static (text) and dynamic (XPath function).
     * Values are send as request-body. Whether they are used as text or XPath
     * function is decided by the Content-Type header of the request.
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
     * The PATCH method works resolutely and  overwrites existing data. The
     * XPath processing is strict and does not accept unnecessary spaces. The
     * attributes ___rev / ___uid used internally by the storage are read-only
     * and cannot be changed.
     *
     * In general, PATCH requests are responded to with status 204. Changes at
     * the storage are indicated by the two-part response header
     * Storage-Revision. Status 404 is used only with relation to the storage.
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
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
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

        // PATCH is implemented like PUT. There are some additional conditions
        // and restrictions that will be checked. After that the answer to the
        // request can be passed to PUT.
        // - Pseudo elements are not supported
        // - Target must exist, particularly for attributes

        // In any case an XPath is required for a valid request.
        if ($this->xpath === null
                || strlen($this->xpath) <= 0)
            $this->quit(400, "Bad Request", ["Message" => "Invalid XPath"]);

        // Storage::SPACE also limits the maximum size of writing
        // request(-body). If the limit is exceeded, the request is quit with
        // status 413.
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
        if (empty($targets)
                || $targets->length <= 0)
            $this->quit(204, "No Content");

        // The response to the request is delegated to PUT.
        // The function call is executed and the request is terminated.
        $this->doPut();
    }

    /**
     * DELETE deletes elements and attributes in the storage. The position for
     * deletion is defined via an XPath. XPath uses different notations for
     * elements and attributes.
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
     * The DELETE method works resolutely and deletes existing data. The XPath
     * processing is strict and does not accept unnecessary spaces. The
     * attributes ___rev / ___uid used internally by the storage are read-only
     * and cannot be changed.
     *
     * In general, DELETE requests are responded to with status 204. Changes at
     * the storage are indicated by the two-part response header
     * Storage-Revision. Status 404 is used only with relation to the storage.
     *
     * Syntactic and semantic errors in the request and/or XPath can cause error
     * status 400.
     *
     *     Request:
     * DELETE /<xpath> HTTP/1.0
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
     *
     *     Response:
     * HTTP/1.0 204 No Content
     * Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * Storage-Revision: Revision (number/changes)
     * Storage-Space: Total/Used (bytes)
     * Storage-Last-Modified: Timestamp (RFC822)
     * Storage-Expiration: Timestamp (RFC822)
     * Storage-Expiration-Time: Expiration (milliseconds)
     *
     *     Response codes / behavior:
     *         HTTP/1.0 204 No Content
     * - Element(s) or attribute(s) successfully deleted
     *         HTTP/1.0 400 Bad Request
     * - Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
     * - XPath is missing or malformed
     *         HTTP/1.0 304 Not Modified
     * - XPath without addressing a target has no effect on the storage
     *         HTTP/1.0 404 Resource Not Found
     * - Storage does not exist
     *         HTTP/1.0 500 Internal Server Error
     * - An unexpected error has occurred
     */
    function doDelete() {

        // In any case an XPath is required for a valid request.
        if ($this->xpath === null
                || strlen($this->xpath) <= 0)
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

        if (empty($targets)
                || $targets->length <= 0)
            $this->quit(304, "Not Modified");

        // Pseudo elements can be used to delete in an XML substructure relative
        // to the selected element.
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

        foreach ($targets as $target) {
            if ($target->nodeType === XML_ATTRIBUTE_NODE) {
                if (!$target->parentNode
                        || $target->parentNode->nodeType !== XML_ELEMENT_NODE
                        || in_array($target->name, ["___rev", "___uid"]))
                    continue;
                $parent = $target->parentNode;
                $parent->removeAttribute($target->name);
                $this->serial++;
                Storage::updateNodeRevision($parent, $this->unique);
            } else if ($target->nodeType !== XML_DOCUMENT_NODE) {
                if (!$target->parentNode
                        || !in_array($target->parentNode->nodeType, [XML_ELEMENT_NODE, XML_DOCUMENT_NODE]))
                    continue;
                $parent = $target->parentNode;
                $parent->removeChild($target);
                $this->serial++;
                if ($parent->nodeType === XML_DOCUMENT_NODE) {
                    $target = $this->xml->createElement($this->root);
                    $target = $this->xml->appendChild($target);
                    Storage::updateNodeRevision($target, $this->unique);
                    $target->setAttribute("___uid", $this->getSerial());
                } else Storage::updateNodeRevision($parent, $this->unique);
            }
        }

        $this->materialize();
        $this->quit(204, "No Content");
    }

    /**
     * Quit sends a response and ends the connection and closes the storage. The
     * behavior of the method is hard. A response status and a response message
     * are expected. Optionally, additional headers and data for the response
     * body can be passed. Headers for storage and data length are set
     * automatically. Data from the response body is only sent to the client if
     * the response status is in class 2xx. This also affects the dependent
     * headers Content-Type and Content-Length.
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

        header(trim("HTTP/1.0 $status $message"));

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

        // For status class 2xx + 304 the storage headers are added.
        // The revision is read from the current storage because it can change.
        if ((($status >= 200 && $status < 300) || $status == 304)
                && $this->storage
                && $this->xml) {
            $expiration = new DateTime();
            $expiration->add(new DateInterval("PT" . Storage::EXPIRATION . "S"));
            $expiration = $expiration->format("D, d M Y H:i:s T");
            $headers = array_merge($headers, [
                "Storage" => $this->storage,
                "Storage-Revision" => $this->xml->documentElement->getAttribute("___rev") . "/" . $this->serial,
                "Storage-Space" => Storage::SPACE . "/" . $this->getSize() . " bytes",
                "Storage-Last-Modified" => date("D, d M Y H:i:s T"),
                "Storage-Expiration" => $expiration,
                "Storage-Expiration-Time" => (Storage::EXPIRATION *1000) . " ms"
            ]);

            if ($status != 200
                    || $data === null
                    || strlen($data) <= 0)
                $data = null;

            if ($data !== null
                    && strlen($data) > 0) {
                if (in_array("json", $this->options)) {
                    $media = Storage::CONTENT_TYPE_JSON;
                    if ($data instanceof DOMDocument
                            || $data instanceof SimpleXMLElement)
                        $data = simplexml_import_dom($data);
                    $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                } else {
                    $media = Storage::CONTENT_TYPE_TEXT;
                    if ($data instanceof DOMDocument
                            || $data instanceof SimpleXMLElement) {
                        $media = Storage::CONTENT_TYPE_XML;
                        $data = $data->saveXML();
                    }
                }
                $headers["Content-Type"] = $media;
                $headers["Content-Length"] = strlen($data);
            }
        } else $data = null;

        // Workaround to remove all default headers.
        // Some must be set explicitly before removing works.
        // Not relevant headers are removed.
        $filter = ["X-Powered-By", "Content-Type", "Content-Length"];
        foreach ($filter as $header) {
            header("$header:");
            header_remove($header);
        }

        foreach ($headers as $key => $value) {
            $value = trim(preg_replace("/[\r\n]+/", " ", $value));
            if (strlen(trim($value)) > 0)
                header("$key: $value");
            else header_remove($key);
        }

        if (Storage::DEBUG_MODE) {
            header("Trace-Request-Hash: " . hash("md5", $_SERVER["REQUEST"] ?: ""));
            $header = join("\t",
                array(
                    $_SERVER["HTTP_STORAGE"] ?? "null",
                    $_SERVER["CONTENT_TYPE"] ?? "null",
                    $_SERVER["CONTENT_LENGTH"] ?? "null"
                )
            );
            header("Trace-Request-Header-Hash: " . hash("md5", $header));
            header("Trace-Request-Data-Hash: " . hash("md5", @file_get_contents("php://input") ?: ""));
            header("Trace-Response-Hash: " . hash("md5", $status . " " . $message));
            $header = join("\t",
                array(
                    $headers["Storage"] ?? "null",
                    $headers["Storage-Revision"] ?? "null",
                    $headers["Storage-Space"] ?? "null",
                    $headers["Error"] ?? "null",
                    $headers["Message"] ?? "null",
                    $headers["Content-Type"] ?? "null",
                    $headers["Content-Length"] ?? "null"
                )
            );
            header("Trace-Response-Header-Hash: " . hash("md5", $header));
            header("Trace-Response-Data-Hash: " . hash("md5", strval($data)));
            $header = $this->storage && $this->xml
                ? $this->xml?->saveXML() ?: "" : "";
            header("Trace-Storage-Hash: " . hash("md5", $header));
        }

        header("Execution-Time: " . round((microtime(true) -$_SERVER["REQUEST_TIME_FLOAT"]) *1000) . " ms");

        if ($data !== null
                && strlen($data) > 0)
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
        // These cannot be caught any other way. Therefore the error header is
        // implemented here.
        $filter = "XSLTProcessor::transformToXml()";
        if (str_starts_with($message, $filter)) {
            $message = "Invalid XSLT stylesheet";
            if (Storage::fetchLastXmlErrorMessage())
                $message .= " (" . Storage::fetchLastXmlErrorMessage() . ")";
            (new Storage)->quit(422, "Unprocessable Entity", ["Message" => $message]);
        }

        $unique = round(microtime(true) *1000);
        $unique = base_convert($unique, 10, 36);
        $unique = "#" . strtoupper($unique);
        $message = "$message" . PHP_EOL . "\tat $file $line";
        if (!is_numeric($error))
            $message = "$error: " . $message;
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
        && (empty($_SERVER["REDIRECT_URL"])))
    (new Storage)->quit(404, "Resource Not Found");

// Request method is determined
$method = strtoupper($_SERVER["REQUEST_METHOD"]);

// Access-Control headers are received during preflight OPTIONS request
if ($method === "OPTIONS"
        && isset($_SERVER["HTTP_ORIGIN"])
        && !isset($_SERVER["HTTP_STORAGE"]))
    (new Storage)->quit(204, "No Content");

if (!isset($_SERVER["HTTP_STORAGE"]))
    (new Storage)->quit(400, "Bad Request", ["Message" => "Missing storage identifier"]);
$storage = $_SERVER["HTTP_STORAGE"];
if (!preg_match(Storage::PATTERN_HEADER_STORAGE, $storage))
    (new Storage)->quit(400, "Bad Request", ["Message" => "Invalid storage identifier"]);

// The XPath is determined from REQUEST_URI or alternatively from REQUEST
// because some servers normalize the paths and URI for the CGI. It was not easy
// to determine the context path for all servers safely and then extract the
// XPath from the request. Therefore it was decided that the context path and
// XPath are separated by a symbol or a symbol sequence. The behavior can be
// customized with Storage::PATTERN_HTTP_REQUEST_URI. If the pattern is empty,
// null or false, the request URI without context path will be used. This is
// helpful when the service is used as a domain.
$xpath = $_SERVER["REQUEST_URI"];
if (Storage::PATTERN_HTTP_REQUEST_URI) {
    if (isset($_SERVER["REQUEST"])
            && preg_match(Storage::PATTERN_HTTP_REQUEST, $_SERVER["REQUEST"], $xpath, PREG_UNMATCHED_AS_NULL))
        $xpath = $xpath[2];
    $xpath = preg_match(Storage::PATTERN_HTTP_REQUEST_URI, $xpath, $xpath, PREG_UNMATCHED_AS_NULL) ? $xpath[2] : "";
}
if (preg_match(Storage::PATTERN_HEX, $xpath))
    $xpath = hex2bin($xpath);
else if (preg_match(Storage::PATTERN_BASE64, $xpath))
    $xpath = base64_decode($xpath);
else $xpath = urldecode($xpath);

// With the exception of CONNECT, OPTIONS and POST, all requests expect an XPath
// or XPath function. CONNECT does not use an (X)Path to establish a storage.
// POST uses the XPath for transformation only optionally to delimit the XML
// data for the transformation and works also without. In the other cases an
// empty XPath is replaced by the root slash.
if (empty($xpath)
        && !in_array($method, ["CONNECT", "OPTIONS", "POST"]))
    $xpath = "/";
$options = Storage::STORAGE_SHARE_NONE;
if (in_array($method, ["CONNECT", "DELETE", "PATCH", "PUT"]))
    $options |= Storage::STORAGE_SHARE_EXCLUSIVE;
if (in_array($method, ["CONNECT"]))
    $options |= Storage::STORAGE_SHARE_INITIAL;
$storage = Storage::share($storage, $xpath, $options);

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