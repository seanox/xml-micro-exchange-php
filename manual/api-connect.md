[API](api.md) | [TOC](README.md) | [DELETE](api-delete.md)
- - -

# CONNECT

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

The response for a CONNECT always contains a Connection-Unique header. The Unique is unique in the Datasource and in the Storage and can be used by the
client e.g. in XML as attributes to locate his data faster.


## Contents Overview

* [Request](#request)
  * [Example](#example)
* [Response](#response)
  * [Example](#example-1)
* [Response codes / behavior](#response-codes--behavior)  
  * [HTTP/1.0 201 Resource Created](#http10-201-resource-created)
  * [HTTP/1.0 204 No Content](#http10-204-no-content)
  * [HTTP/1.0 400 Bad Request](#http10-400-bad-request)
  * [HTTP/1.0 500 Internal Server Error](#http10-500-internal-server-error)
  * [HTTP/1.0 507 Insufficient Storage](#http10-507-insufficient-storage)


## Request

```
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
```
``` 
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ root (identifier / root)
```

### Example

```
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ books
```
Creates a storage with the identifier 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ if
it does not yet exist.  
For the XML structure the default root named data is used.

``` 
CONNECT / HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ books
```
Creates a storage with the identifier 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ if
it does not yet exist.  
For the XML structure the root named books is used.


## Response

```
HTTP/1.0 201 Created
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number) 
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timestamp (RFC822)
Storage-Expiration-Time: Expiration (milliseconds)
Connection-Unique: UID
```
``` 
HTTP/1.0 204 No Content
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: Revision (number)
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timestamp (RFC822)
Storage-Expiration-Time: Expiration (milliseconds)
Connection-Unique: UID
```

### Example

```
HTTP/1.0 201 Resource Created
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 0
Storage-Space: 262144/87 bytes
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration-Time: 900000 ms
Connection-Unique: ABI0ZX99X13M
Execution-Time: 3 ms
```
Response if the storage was newly created.  
Also recognizable by the initial revision 0 in the Storage-Revision header.

```
HTTP/1.0 204 No Content
Date: Wed, 11 Nov 2020 12:00:00 GMT
Access-Control-Allow-Origin: *
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
Storage-Revision: 0
Storage-Space: 262144/87 bytes
Storage-Last-Modified: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration: Wed, 11 Nov 20 12:00:00 +0000
Storage-Expiration-Time: 900000 ms
Connection-Unique: ABI0ZX99X13M
Execution-Time: 3 ms
```
Response, if the storage already exists.  
The initial revision 0 in the storage revision header shows that this is still
the initial version without changes.


## Response codes / behavior

### HTTP/1.0 201 Resource Created
- Response can be status 201 if the storage was newly created

### HTTP/1.0 204 No Content
- Response can be status 204 if the storage already exists

### HTTP/1.0 400 Bad Request
- Storage header is invalid, 1 - 64 characters (0-9A-Z_) are expected
- XPath is missing or malformed
- XPath is used from PATH_INFO + QUERY_STRING, not the request URI

### HTTP/1.0 500 Internal Server Error
- An unexpected error has occurred.

### HTTP/1.0 507 Insufficient Storage
- Response can be status 507 if the storage is full



- - -

[API](api.md) | [TOC](README.md) | [DELETE](api-delete.md)