[GET](api-get.md) | [TOC](README.md) | [PATCH](api-patch.md)
- - -

# OPTIONS

OPTIONS is used to request the functions to an XPath, which is responded with
the Allow-header.  
This method distinguishes between XPath axis and XPath function and uses
different Allow headers. Also the existence of the target on an XPath axis has
an influence on the response. The method will not use status 404 in relation to
non-existing targets, but will offer the methods `CONNECT`, `OPTIONS`, `PUT`
via Allow-Header.  
If the XPath is a function, it is executed and thus validated, but without
returning the result.  
The XPath processing is strict and does not accept unnecessary spaces.  
Faulty XPath will cause the status 400.


## Contents Overview

* [XPath axis](#xpath-axis)
* [XPath function](#xpath-function)
* [Request](#request)
  * [Example](#example)
* [Response](#response)
  * [Example](#example-1)
* [Response codes / behavior](#response-codes--behavior)  
  * [HTTP/1.0 204 No Content](#http10-204-no-content)
  * [HTTP/1.0 400 Bad Request](#http10-400-bad-request)
  * [HTTP/1.0 404 Resource Not Found](#http10-404-resource-not-found)
* [Request](#request-1)
  * [Example](#example-2)
* [Response](#response-1)
  * [Example](#example-3)
* [Response codes / behavior](#response-codes--behavior-1)  
  * [HTTP/1.0 201 Resource Created](#http10-201-resource-created)
  * [HTTP/1.0 202 Accepted](#http10-202-accepted)
  * [HTTP/1.0 400 Bad Request](#http10-400-bad-request-1)
  * [HTTP/1.0 507 Insufficient Storage](#http10-507-insufficient-storage)


## Request

```
OPTIONS /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```

### Example

TODO:


## Response

```
HTTP/1.0 204 Success
Storage-Effects: ... (list of UIDs)
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
```

### Example

TODO:


## Response codes / behavior

### HTTP/1.0 204 No Content
- Request was successfully executed

###  HTTP/1.0 400 Bad Request
- Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
- XPath is missing or malformed

### HTTP/1.0 404 Resource Not Found
- Storage does not exist

In addition, OPTIONS can also be used as an alternative to CONNECT, because
CONNECT is not an HTTP standard. For this purpose OPTIONS without XPath, but
with context path if necessary, is used. In this case OPTIONS will hand over
the work to CONNECT.

The response for a CONNECT always contains a Connection-Unique header.  
The Unique is unique in the Datasource and in the Storage and can be used by the
client e.g. in XML as attributes to locate his data faster.


## Request
```
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```
``` 
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier / root)
```

### Example
```
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
```
``` 
OPTIONS / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root
```


## Response
```
HTTP/1.0 201 Created
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number) 
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
Connection-Unique: UID
```
``` 
HTTP/1.0 202 Accepted
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
Connection-Unique: UID
```

### Example
```
HTTP/1.0 201 Resource Created
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 0
Storage-Space: 262144/87
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: 900/Wed, 11 Nov 20 12:00:00 +0000
Connection-Unique: ABI0ZX99X13M
Execution-Time: 3 ms
```


## Response codes / behavior

### HTTP/1.0 201 Resource Created
- Response can be status 201 if the storage was newly created

### HTTP/1.0 202 Accepted
- Response can be status 202 if the storage already exists

### HTTP/1.0 400 Bad Request
- Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
- XPath is missing or malformed
- XPath is used from PATH_INFO + QUERY_STRING, not the request URI

### HTTP/1.0 507 Insufficient Storage
- Response can be status 507 if the storage is full



- - -

[GET](api-get.md) | [TOC](README.md) | [PATCH](api-patch.md)