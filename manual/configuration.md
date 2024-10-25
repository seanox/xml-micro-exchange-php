&#9665; [Installation](installation.md)
&nbsp;&nbsp;&nbsp;&nbsp; &#8801; [Table of Contents](README.md)
&nbsp;&nbsp;&nbsp;&nbsp; [Terms](terms.md) &#9655;
- - -

# Configuration

__This chapter is only relevant if you want to run the Datasource on your own
server. If you want to use an existing Datasource on the Internet, you can skip
this chapter.__

The REST API is configured as an absolute (hungry) virtual path. So all requests
starting with the virtual path are redirected to the PHP script `./service.php`.
Means that the script itself cannot be called. The paths of all requests are
passed as path-info and thus as virtual paths.

The following HTTP methods must be allowed:  
`CONNECT`, `OPTIONS`, `GET`, `PUT`, `PATCH`, `POST`, `DELETE`  
The `CONNECT` method is not an HTTP standard, alternative `TOUCH` or `PUT` can
be used.

When using PHP as CGI, the HTTP methods may also need to be allowed.


## Contents Overview

* [Web Server](#web-server)
  * [Apache HTTPD](#apache-httpd)
  * [Seanox Devwex](#seanox-devwex)
  * [Others](#others)
* [Parameters](#parameters)
  * [Storage::DIRECTORY](#storagedirectory)
  * [Storage::QUANTITY](#storagequantity)
  * [Storage::SPACE](#storagespace)
  * [Storage::TIMEOUT](#storagetimeout)
  * [Storage::CORS](#storagecors)
  * [Storage::PATTERN_HTTP_REQUEST_URI](#storagepattern_http_request_uri)


## Web Server

### Apache HTTPD

```
#.htaccess
RewriteEngine on
RewriteRule (^/xmex!.*$) service.php [L]
RewriteRule (^.*$) content/$1 [L]
```

Root can also be used. A context path is not required, but it is recommended to
use a context path that ends with a non-alphanumeric character to make the
separation between URL and XPath more visible.  

### Seanox Devwex

```
[SERVER:HTTP:CGI]
  PHP = CONNECT OPTIONS GET PUT PATCH POST DELETE > ...
  
[SERVER:HTTP:REF]
  XMEX = /xmex! > .../xmex/service.php [A]
```

Root can also be used. A context path is not required, but it is recommended to
use a context path that ends with a non-alphanumeric character to make the
separation between URL and XPath more visible.  

### nginx

TODO:

## Parameters

XML-Micro-Exchange is configured via environment variables. With the prepared
default values, the service can be started.

### XMEX_DEBUG_MODE
Activates the debug and test mode, which enforces the serial revision type and
extends the response with additional trace headers. Supported values: `on`,
`true`, `1`.

Default: `off`

### XMEX_STORAGE_EXPIRATION
Maximum time of inactivity of the storage files in seconds. Without file access
during this time, the storage files are deleted.

Default: `15 *60`

### XMEX_STORAGE_DIRECTORY
Directory of the data storage, which is configured with the required permissions
by the script at runtime.

Default: `./data`

### XMEX_STORAGE_QUANTITY
Maximum number of files in data storage. Exceeding the limit causes the status
507 - Insufficient Storage.

Default: `65535`

### XMEX_STORAGE_REVISION_TYPE
Defines the revision type. Supported values: `0` (serial, starting with 1),
`1` (alphanumeric timestamp).

Default: `1`

### XMEX_STORAGE_SPACE
Maximum data size of files in data storage in bytes. The value also limits the
size of the requests(-body).

Default: `256 *1024`

### XMEX_URI_XPATH_DELIMITER
Character or character sequence of the XPath delimiter in the URI. Changing this
value often also requires changes to the web server configuration.

Default: `!`



- - -
&#9665; [Installation](installation.md)
&nbsp;&nbsp;&nbsp;&nbsp; &#8801; [Table of Contents](README.md)
&nbsp;&nbsp;&nbsp;&nbsp; [Terms](terms.md) &#9655;
