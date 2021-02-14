[Installation](installation.md) | [TOC](README.md) | [Terms](terms.md)
- - -

# Configuration

__This chapter is only relevant if you want to run the Datasource on your own
server.  
If you want to use an existing Datasource on the Internet, you can skip
this chapter.__

The REST API is configured as an absolute (hungry) virtual path. So all requests
starting with the virtual path are redirected to the PHP script `./service.php`.  
Means that the script itself cannot be called.  
The paths of all requests are passed as path-info and thus as virtual paths.

The following HTTP methods must be allowed:  
`CONNECT`, `OPTIONS`, `GET`, `PUT`, `PATCH`, `POST`, `DELETE`  
The `CONNECT` method is not an HTTP standard, alternative `OPTIONS` can be used.

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
RewriteRule (^xmex!.*) service.php [L]
RewriteRule service.php - [L]
RewriteRule (.*) - [R=404,L]
```

Root can also be used. A context path is not required, but it is recommended to
use a context path that ends with a non-alphanumeric character to make the
separation between URL and XPath more visible.  


### Seanox Devwex

```
[SERVER:HTTP:CGI]
  ...
  PHP = CONNECT OPTIONS GET PUT PATCH POST DELETE > ...
  
[SERVER:HTTP:REF]
  ...
  XMEX = /xmex! > /xml-micro-exchange/service.php [A]
```

Root can also be used. A context path is not required, but it is recommended to
use a context path that ends with a non-alphanumeric character to make the
separation between URL and XPath more visible.  


### Others

Something like Apache HTTPD or Seanox Devwex.  
Alternatively, the script can be called directly and passed to XPath as a query
string.


## Parameters

Overview of the configurable parameters / constants:


### Storage::DIRECTORY

Default: `./data`  
Directory of the data storage, which is configured with the required
permissions by the script at runtime.


### Storage::QUANTITY

Default: `65535`  
Maximum number of files in data storage.  
Exceeding the limit causes the status 507 - Insufficient Storage.


### Storage::SPACE

Default: `256 *1024`  
Maximum data size of files in data storage in bytes.
The value also limits the size of the requests(-body).


### Storage::TIMEOUT

Default: `15 *60`  
Maximum idle time of the files in seconds.  
If the inactivity exceeds this time for a Storage, it expires.


### Storage::CORS

Default: `[`  
`"Access-Control-Allow-Origin" => "*",`  
`"Access-Control-Allow-Credentials" => "true",`  
`"Access-Control-Max-Age" => "86400",`
`"Access-Control-Expose-Headers" => "*"`  
`]`  

Optional CORS response headers as associative array.  
For the preflight OPTIONS the following headers are added automatically:  
`Access-Control-Allow-Methods`, `Access-Control-Allow-Headers`


### Storage::PATTERN_HTTP_REQUEST_URI

Default: `/^(.*?)[!#\$\*:\?@\|~]+(.*)$/i`  
Pattern for separating URI-Path and XPath.<br/>
If the pattern is empty, null or false, the request URI without context
path will be used. This is helpful when the service is used as a domain.

Expected structure:
* Group 0. Full match  
* Group 1. URI-Path  
* Group 2. XPath



- - -

[Installation](installation.md) | [TOC](README.md) | [Terms](terms.md)
