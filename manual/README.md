# Manual

Machine translation with [DeepL](https://deepl.com).


## Table Of Contents 

* [Motivation](#motivation)
* [Installation](#installation)
* [Configuration](#configuration)
* [Terms](#terms)
  * [Datasource](#datasource)
  * [Storage](#storage)
  * [Storage Identifier](#storage-identifier)
  * [Element(s)](#elements)
  * [Attribute(s)](#attributes)
  * [XPath](#xpath)
  * [XPath Axis](#xpath-axis)
  * [XPath Axis Pseudo Elements](#xpath-axis-pseudo-elements)
  * [XPath Function](#xpath-function)
  * [Revision](#revision)
  * [UID](#uid)
  * [Transaction / Concurrent Access](#transaction--concurrent-access)
* [Getting Started](#getting-started)
* [API](#api)
  * [CONNECT](#connect)
    * [Request](#request)
    * [Reponse](#reponse)
    * [Response codes / behavior](#response-codes--behavior)
  * [GET](#get)
    * [Request](#request-1)
    * [Reponse](#reponse-1)
    * [Response codes / behavior](#response-codes--behavior-1)
  * [DELETE](#delete)
    * [Request](#request-2)
    * [Reponse](#reponse-2)
    * [Response codes / behavior](#response-codes--behavior-2)
  * [OPTIONS](#options)
    * [Request](#request-3)
    * [Reponse](#reponse-3)
    * [Response codes / behavior](#response-codes--behavior-3)
  * [PATCH](#patch)
    * [Request](#request-4)
    * [Reponse](#reponse-4)
    * [Response codes / behavior](#response-codes--behavior-4)
  * [POST](#post)
    * [Request](#request-5)
    * [Reponse](#reponse-5)
    * [Response codes / behavior](#response-codes--behavior-5)
  * [PUT](#put)
    * [Request](#request-6)
    * [Reponse](#reponse-6)
    * [Response codes / behavior](#response-codes--behavior-6)
* [Error Handling](#error-handling)    
* [Development](#development)
* [Test](#test)


## Motivation

The origin of the project is the desire for an easily accessible place for data
exchange on the Internet. Inspired by JSON-Storages the idea of a feature-rich
equivalent based on XML, XPath and XSLT was born.  
The focus should be on a public, volatile and short-term data exchange for
(static) web applications and IoT.

__Just exchange data without a own server landscape.__  
__Just exchange data without knowing and managing all clients.__


## Installation

TODO:


## Configuration
The REST API is configured as an absolute (hungry) virtual path. So all requests
starting with the virtual path are redirected to the PHP script `./service.php`.  
Means that the script itself cannot be called.  
The paths of all requests are passed as path-info and thus as virtual paths.

The following HTTP methods must be allowed:  
`CONNECT`, `OPTIONS`, `GET`, `PUT`, `PATCH`, `POST`, `DELETE`  
The `CONNECT` method is not an HTTP standard, alternative `OPTIONS` can be used.

When using PHP as CGI, the HTTP methods may also need to be allowed. 


### Apache HTTPD

Direct for a physical or virtual host:

```
#httpd-ssl.conf
RewriteEngine on
RewriteRule ^/xmex!(.*)$ service.php
```

Or in the .htaccess file:

```
#.htaccess
RewriteEngine on
RewriteRule (.*) service.php
```
Root can also be used.  
A context path is not required, but it is recommended to use a context path
that ends with a non-alphanumeric character to make the separation between URL
and XPath more visible.  


### Seanox Devwex
```
[SERVER:HTTP:CGI]
  ...
  PHP = CONNECT OPTIONS GET PUT PATCH POST DELETE > ...
  
[SERVER:HTTP:REF]
  ...
  XMEX = /xmex! > /xml-micro-exchange/service.php [A]
```
Root can also be used.  
A context path is not required, but it is recommended to use a context path
that ends with a non-alphanumeric character to make the separation between URL
and XPath more visible.  

### Other HTTP servers
Something like Apache HTTPD or Seanox Devwex.  
Alternatively, the script can be called directly and passed to XPath as a query
string.


## Terms

### Datasource
XML-Micro-Exchange is a data service that manages different data areas.  
The entirety, so the service itself, is the datasource.
Physically this is the data directory.

### Storage
The data areas managed by the XML Micro-Exchange as a data service are called
storage areas. A storage area corresponds to an XML file in the data directory.

### Storage Identifier
Each storage has an identifier, the Storage Identifier.  
The Storage Identifier is used as the filename of the corresponding XML file and
must be specified with each request so that the datasource uses the correct
storage.

### Element(s)
The content of the XML file of a storage provide the data as object or tree
structure. The data entries are called elements.
Elements can enclose other elements.

### Attribute(s)
Elements can also contain direct values in the form of attributes.

### XPath
XPath is a notation for accessing and navigating the XML data structure.  
An XPath can be an axis or a function.

### XPath Axis
XPath axes address or select elements or attributes.  
The axes can have a multidimensional effect.

### XPath Axis Pseudo Elements
For PUT requests it is helpful to specify a relative navigation to an XPath
axis. For example first, last, before, after. This extension of the notation is
supported for PUT requests and is added to an XPath axis separated by two colons
at the end (e.g. `/root/element::end` - means put in element as last).

### XPath Function
+The XPath notation also supports functions that can be used in combination with
axes and standalone for dynamic data requests. In combination with XPath axes,
the addressing and selection of elements and attributes can be made dynamic.

### Revision
Every change in a storage is expressed as a revision.  
This should make it easier for the client to determine whether data has changed,
even for partial requests.  
The revision is a counter of changes per request, without any claim of version
management of past revisions.  
It starts with initial revision 0 when a storage is created on the first call.
The first change already uses revision 1. 

Each element uses a revision in the read-only attribute `___rev`, which, as
with all parent revision attributes, is automatically incremented when it
changes.  
A change can affect the element itself or the change to its children.  
Because the revision is passed up, the root element automatically always uses
the current revision.

Changes are: PUT, PATCH, DELETE

Write accesses to attribute `___rev` are accepted with status 204, will have no
effect from then on and are therefore not listed in the response header
`Storage-Effects`. 

### UID
Each element uses a unique identifier in the form of the read-only attribute
`___uid`. The unique identifier is automatically created when an element is put
into storage and never changes.  
If elements are created or modified by a request, the created or affected unique
identifiers are sent to the client in the response header `Storage-Effects`.

The UID uses an alphanumeric format based on radix 36 which, when converted into
a number, gives the timestamps of the creation in milliseconds since 01/01/2000.  
The UID is thus also sortable and provides information about the order in which
elements are created.

Write accesses to attribute `___uid` are accepted with status 204, will have no
effect from then on and are therefore not listed in the response header
`Storage-Effects`. 
 
### Transaction / Concurrent Access
In the first version of XML Micro-Exchange all requests to a storage use flock +
LOCK_EX / LOCK_UN, that should change later. So that also simultaneous accesses
are supported, but no dirty reading.


## Getting Started

TODO:


## API

### CONNECT
CONNECT initiates the use of a storage.  
A storage is a volatile XML construct that is used via a datasource URL.  
The datasource managed several independent storages.  
Each storage has a name specified by the client, which must be sent with each
request. This is similar to the header host for virtual servers.  
Optionally, the name of the root element can also be defined by the client.

Each client can create a new storage at any time.  
Communication is established when all parties use the same name.  
There are no rules, only the clients know the rules.  
A storage expires with all information if it is not used (read/write).

In addition, OPTIONS can also be used as an alternative to CONNECT, because
CONNECT is not an HTTP standard. For this purpose OPTIONS without XPath, but
with context path if necessary, is used. In this case OPTIONS will hand over
the work to CONNECT.

#### Request
```
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```
``` 
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier / root)
```

##### Example
```
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
```
``` 
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root
```

#### Response
```
HTTP/1.0 201 Created
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number) 
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```
``` 
HTTP/1.0 202 Accepted
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```

##### Example
```
HTTP/1.0 201 Resource Created
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 0
Storage-Space: 262144/87
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: 900/Wed, 11 Nov 20 12:00:00 +0000
Execution-Time: 3
```

#### Response codes / behavior

##### HTTP/1.0 201 Resource Created
- Response can be status 201 if the storage was newly created

##### HTTP/1.0 202 Accepted
- Response can be status 202 if the storage already exists

##### HTTP/1.0 400 Bad Request
- Requests without XPath are responded with status 400 Bad Request
- Requests with a invalid Storage header are responded with status 400  
  Bad Request, exactly 36 characters are expected - Pattern [0-9A-Z]{36}
- XPath is used from PATH_INFO + QUERY_STRING, not the request URI

##### HTTP/1.0 507 Insufficient Storage
- Response can be status 507 if the storage is full


### GET
GET queries data about XPath axes and functions.  
For this, the XPath axis or function is sent with URI.  
Depending on whether the request is an XPath axis or an XPath function,
different Content-Type are used for the response.

#### XPath axis
Conent-Type: `application/xslt+xml`
When the XPath axis addresses one target, the addressed target is the root
element of the returned XML structure.  
If the XPath addresses multiple targets, their XML structure is combined in the
root element collection.

#### XPath function
Conent-Type: `text/plain`  
The result of XPath functions is returned as plain text.  
Decimal results use float, booleans the values true and false.

#### Request
```
GET /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```

#### Example
TODO

#### Response
```
HTTP/1.0 200 Success
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
Content-Length: (bytes)
    Response-Body:
The result of the XPath request
```

#### Example
TODO

#### Response codes / behavior:

##### HTTP/1.0 200 Success
- Request was successfully executed

##### HTTP/1.0 400 Bad Request
- XPath is malformed

##### HTTP/1.0 404 Resource Not Found
- Storage is invalid 
- XPath axis finds no target


### DELETE
TODO:


### OPTIONS
OPTIONS is used to request the functions to an XPath, which is responded with
the Allow-header.  
This method distinguishes between XPath axis and XPath function and uses
different Allow headers. Also the existence of the target on an XPath axis has
an influence on the response. The method will not use status 404 in relation to
non-existing targets, but will offer the methods `CONNECT`, `OPTIONS`, `PUT`
via Allow-Header.  
If the XPath is a function, it is executed and thus validated, but without
returning the result.  
Faulty XPath will cause the status 400.

#### Request
```
OPTIONS /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```

#### Example
TODO:

#### Response
```
HTTP/1.0 204 Success
Storage-Effects: ... (list of UIDs)
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```

#### Example
TODO:

#### Response codes / behavior

##### HTTP/1.0 204 No Content
- Request was successfully executed

#####  HTTP/1.0 400 Bad Request
- XPath is malformed

##### HTTP/1.0 404 Resource Not Found
- Storage is invalid 

In addition, OPTIONS can also be used as an alternative to CONNECT, because
CONNECT is not an HTTP standard. For this purpose OPTIONS without XPath, but
with context path if necessary, is used. In this case OPTIONS will hand over
the work to CONNECT.

#### Request
```
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```
``` 
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier / root)
```

##### Example
```
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
```
``` 
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root
```

#### Response
```
HTTP/1.0 201 Created
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number) 
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```
``` 
HTTP/1.0 202 Accepted
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```

##### Example
```
HTTP/1.0 201 Resource Created
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 0
Storage-Space: 262144/87
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: 900/Wed, 11 Nov 20 12:00:00 +0000
Execution-Time: 3
```

#### Response codes / behavior

##### HTTP/1.0 201 Resource Created
- Response can be status 201 if the storage was newly created

##### HTTP/1.0 202 Accepted
- Response can be status 202 if the storage already exists

##### HTTP/1.0 400 Bad Request
- Requests without XPath are responded with status 400 Bad Request
- Requests with a invalid Storage header are responded with status 400  
  Bad Request, exactly 36 characters are expected - Pattern [0-9A-Z]{36}
- XPath is used from PATH_INFO + QUERY_STRING, not the request URI

##### HTTP/1.0 507 Insufficient Storage
- Response can be status 507 if the storage is full


### PATCH
TODO:


### POST
POST queries data about XPath axes and functions via transformation.
For this, an XSLT stylesheet is sent with the request-body, which is then
applied by the XSLT processor to the data in storage.  
Thus the content type `application/xslt+xml` is always required.  
The client defines the content type for the output with the output-tag and the
method-attribute.  
The XPath is optional for this method and is used to limit and preselect the
data.

#### Request
```
POST /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Content-Length: (bytes)
Content-Type: application/xslt+xml
    Request-Body
XSLT stylesheet
```

##### Example
```
POST /xmex/ HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Content-Type: application/xslt+xml
Content-Length: 212

<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml"/>
  <xsl:template match="/">
    ...
  </xsl:template>
</xsl:stylesheet>
```

#### Response
```
HTTP/1.0 200 Success
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
Content-Length: (bytes)
```

##### Example
```
HTTP/1.0 200 Success
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 1
Storage-Space: 262144/14384
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: 900/Wed, 11 Nov 20 12:00:00 +0000
Content-Length: 26
Execution-Time: 4

<?xml version="1.0"?>
...
```

#### Response codes / behavior

##### HTTP/1.0 200 Success
- Request was successfully executed

##### HTTP/1.0 400 Bad Request
- XPath is malformed
- XSLT Stylesheet is erroneous

##### HTTP/1.0 404 Resource Not Found
- Storage is invalid 
- XPath axis finds no target

##### HTTP/1.0 415 Unsupported Media Type
- Attribute request without Content-Type text/plain


### PUT
PUT inserts new elements and attributes into the storage.  
The position for the insert is defined via an XPath.  
XPath uses different notations for elements and attributes.  
The notation for attributes use the following structure at the end.  
    `<XPath>/@<attribute>` or `<XPath>/attribute::<attribute>`
The attribute values can be static (text) and dynamic (XPath function).  
Values are send as request-body.
Whether they are used as text or XPath function is decided by the
Content-Type header of the request:
- `text/plain`: static text
- `text/xpath`: XPath function

If the XPath notation corresponds to attributes, elements are assumed.  
For elements, the notation for pseudo elements is also supported:  
    `<XPath>::first, <XPath>::last, <XPath>::before or <XPath>::after
Pseudo elements are a relative position specification to the selected element.

The value of elements can be static (text), dynamic (XPath function) or be an
XML structure. Again, the value is transmitted with the request-body and the
type of processing is determined by the Content-Type:  
- `text/plain`: static text
- `text/xpath`: XPath function
- `application/xslt+xml`: XML structure

The PUT method works resolutely and inserts or overwrites existing data.  
The processing of the XPath is strict and dispenses with superfluous spaces.
The attributes `___rev` / `___uid` used internally by the storage are read-only
and cannot be changed.

In general, if no target can be reached via XPath, no errors will occur.  
The PUT method informs the client about changes made via the response headers
`Storage-Effects` and `Storage-Revision`. The header `Storage-Effects` contains
a list of the UIDs that were directly affected by the change and also contains
the UIDs of newly created elements. If no changes were made because the XPath
cannot find a target or the target is read-only, the header Storage-Effects can
be omitted completely in the response.  
Also in this case the request is responded with status 204 as successfully
executed.

Syntactic and symantic errors in the request and/or XPath and/or value can
cause error status 400 and 415.

#### Request
```
PUT /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Content-Length: (bytes)
Content-Type: application/xslt+xml
     Request-Body:
XML structure
```
```
PUT /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Content-Length: (bytes)
Content-Type: text/plain
    Request-Body:
Value as plain text
```
```
PUT /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Content-Length: (bytes)
Content-Type: text/xpath
    Request-Body:
Value as XPath function 
```

##### Example
```
PUT /xmex/books/attribute::attrA HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Content-Type: text/plain
Content-Length: 5

Value
```
```
PUT /xmex/books/@attrA HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Content-Type: text/xpath
Content-Length: 25

concat(name(/*), "-Test")
```
```
PUT /xmex/books HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Content-Type: application/xslt+xml
Content-Length: 70

<book title="Book A"/>
<book title="Book B"/>
<book title="Book C"/>
```

#### Response
```
HTTP/1.0 204 No Content
Storage-Effects: ... (list of UIDs)
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```

##### Example
```
HTTP/1.0 204 No Content
Date: Wed, 11 Nov 2020 12:00:00 GMT
Storage-Effects: KHDHLTQW18U4:0 KHDHLQJU18U2:0 KHDHLTQW18U4:1 KHDHLTQW18U4:2
Access-Control-Allow-Origin: *
Storage: 0000000000000000000000000000000000PE
Storage-Revision: 1
Storage-Space: 262144/305
Storage-Last-Modified: Wed, 11 Nov 12:00:00 +0000
Storage-Expiration: 900/Wed, 11 Nov 12:00:00 +0000
Execution-Time: 3
```

#### Response codes / behavior

##### HTTP/1.0 204 No Content
- Attributes successfully created or set

##### HTTP/1.0 400 Bad Request
- XPath is missing or malformed
- XPath without addressing a target is responded with status 204

##### HTTP/1.0 404 Resource Not Found
- Storage is invalid  

##### HTTP/1.0 413 Payload Too Large
- Allowed size of the request(-body) and/or storage is exceeded

##### HTTP/1.0 415 Unsupported Media Type
- Attribute request without Content-Type text/plain


## Error Handling

The error return is not always so easy with REST services.  
Also with XML Micro-Exchange, the recommendation to use server status 400 as
well as 500 is often not helpful for the client or developer.

In the case of status 400, XML-Micro-Exchange uses the additional header Message
in the response, which contains more details about the error.

```
HTTP/1.0 400 Bad Request
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Message: Invalid expression
Execution-Time: 3
Allow: CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE
```

In case of status 500, the additional header Error is used in the response,
which contains a unique error number.  
More details about the error or the number can then be found in the log file.  
Internal errors generally do not contain details in the response, this prevents
the publication of internal details.

```
HTTP/1.0 500 Internal Server Error
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Error: #KHF8KO9715S2
Execution-Time: 16
Allow: CONNECT, OPTIONS, GET, POST, PUT, PATCH, DELETE
```


## Development

The following is used to develop the XML Micro-Exchange:

- [Windows 10 Pro](https://www.microsoft.com/de-de/software-download/windows10) Operating system
- [PHP 7.4.x](https://www.php.net/downloads) Runtime
- [Seanox Devwex 5.3.x](https://github.com/seanox/devwex) Primary Web Server
- [Apache HTTP Server 2.4.x](https://httpd.apache.org/) Secondary Web Server
- [Visual Studio Code](https://code.visualstudio.com) Editor
- [REST Client](https://marketplace.visualstudio.com/items?itemName=humao.rest-client) Testing
- [Notepad++](https://notepad-plus-plus.org/) Editor for XPath testing
- [XML Tools for Notepad++](https://community.notepad-plus-plus.org/topic/16983/xml-tools) Editor for XPath testing
- [Git 2.29.x](https://git-scm.com/downloads) SCM
- [Apache Ant 1.10.x](https://ant.apache.org/) Build

The specifications are not binding, but should only give an orientation and help
in understanding.


## Test

TODO:
