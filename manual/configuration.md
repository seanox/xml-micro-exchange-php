&#9665; [Installation](installation.md)
&nbsp;&nbsp;&nbsp;&nbsp; &#8801; [Table of Contents](README.md)
&nbsp;&nbsp;&nbsp;&nbsp; [Terms](terms.md) &#9655;
- - -

# Configuration

> [!NOTE]
> __This chapter is only relevant if you want to run the Datasource on your own
> server. If you want to use an existing Datasource on the Internet, you can
> skip this chapter.__

The REST API is configured as an absolute (hungry) virtual path. So all requests
starting with the virtual path are redirected to the PHP script `./service.php`.
Means that the script itself cannot be called. The paths of all requests are
passed as path-info and thus as virtual paths.

The following HTTP methods must be allowed: `CONNECT`, `OPTIONS`, `GET`, `PUT`,
`PATCH`, `POST`, `DELETE` The `CONNECT` method is not an HTTP standard,
alternative `TOUCH` or `PUT` can be used.

> [!IMPORTANT]
> __When using PHP as CGI, the HTTP methods may also need to be allowed.__


## Contents Overview

* [Web Server](#web-server)
  * [Apache HTTPD](#apache-httpd)
  * [Seanox Devwex](#seanox-devwex)
* [Parameters](#parameters)
  * [XMEX_CONTAINER_MODE](#xmex_container_mode)
  * [XMEX_DEBUG_MODE](#xmex_debug_mode)
  * [XMEX_STORAGE_DIRECTORY](#xmex_storage_directory)
  * [XMEX_STORAGE_EXPIRATION](#xmex_storage_expiration)
  * [XMEX_STORAGE_QUANTITY](#xmex_storage_quantity)
  * [XMEX_STORAGE_REVISION_TYPE](#xmex_storage_revision_type)
  * [XMEX_STORAGE_SPACE](#xmex_storage_space)
  * [XMEX_URI_XPATH_DELIMITER](#xmex_uri_xpath_delimiter)
* [Docker Image](#docker-image) 


## Web Server

### Apache HTTPD

```
#.htaccess

SetEnv XMEX_CONTAINER_MODE        off
SetEnv XMEX_DEBUG_MODE            off
SetEnv XMEX_STORAGE_DIRECTORY     ./data
SetEnv XMEX_STORAGE_QUANTITY      65535
SetEnv XMEX_STORAGE_SPACE         262144
SetEnv XMEX_STORAGE_EXPIRATION    900
SetEnv XMEX_STORAGE_REVISION_TYPE timestamp
SetEnv XMEX_URI_XPATH_DELIMITER   !

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


## Environment Variables

XML-Micro-Exchange is configured via environment variables. The existing default
values are suitable for productive use.

### XMEX_CONTAINER_MODE
Activates optimizations for use as a container.

- Redirect the log output to /dev/stdout

Supported values: `on`, `true`, `1`.

Default: `off`

### XMEX_DEBUG_MODE
Activates optimizations for debugging and testing.

- Enforces the serial revision type
- Extends the response with additional trace headers
- Uses the file extension xml for the XML storage files
- Beautifies the XML storage files (indentation and line breaks)
- Saves each revision in consecutive XML files

Supported values: `on`, `true`, `1`.
 
Default: `off`

### XMEX_STORAGE_EXPIRATION
Maximum time of inactivity of the storage files in seconds. Without file access
during this time, the storage files are deleted.

Default: `900` (15 min, 15 x 60 sec)

### XMEX_STORAGE_DIRECTORY
Directory of the data storage, which is configured with the required permissions
by the script at runtime.

Default: `./data`

### XMEX_STORAGE_QUANTITY
Maximum number of files in data storage. Exceeding the limit causes the status
507 - Insufficient Storage.

Default: `65535`

### XMEX_STORAGE_REVISION_TYPE
Defines the revision type. Supported values: `serial` (starting with 1),
`timestamp` (alphanumeric).

Default: `timestamp`

### XMEX_STORAGE_SPACE
Maximum data size of files in data storage in bytes. The value also limits the
size of the requests(-body).

Default: `262144` (256 kB, 256 x 1024 kB)

### XMEX_URI_XPATH_DELIMITER
Character or character sequence of the XPath delimiter in the URI. Changing this
value often also requires changes to the web server configuration.

Default: `!`


## Docker Image

__Application relevant directories:__
- `/usr/local/xmex`
- `/usr/local/xmex/conf.d`
- `/usr/local/xmex/content`
- `/usr/local/xmex/data`

__Apache relevant directories:__
- `/etc/apache2`
- `/etc/apache2/conf.d`
- `/usr/lib/apache2`
- `/usr/local/apache2`
- `/var/logs/apache2`

__PHP relevant directories:__
- `/etc/php83`
- `/usr/lib/php83`



- - -
&#9665; [Installation](installation.md)
&nbsp;&nbsp;&nbsp;&nbsp; &#8801; [Table of Contents](README.md)
&nbsp;&nbsp;&nbsp;&nbsp; [Terms](terms.md) &#9655;
