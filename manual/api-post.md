[PATCH](api-patch.md) | [TOC](README.md) | [PUT](api-put.md)
- - -

# POST

POST queries data about XPath axes and functions via transformation.
For this, an XSLT stylesheet is sent with the request-body, which is then
applied by the XSLT processor to the data in storage.  
Thus the content type `application/xslt+xml` is always required.  
The client defines the content type for the output with the output-tag and the
method-attribute.  
The XPath is optional for this method and is used to limit and preselect the
data. The processing is strict and does not accept unnecessary spaces.


## Contents Overview

* [Request](#request)
  * [Example](#example)
* [Response](#response)
  * [Example](#example-1)
* [Response codes / behavior](#response-codes--behavior)  
  * [HTTP/1.0 200 Success](#http10-202-success)
  * [HTTP/1.0 400 Bad Request](#http10-400-bad-request)
  * [HTTP/1.0 404 Resource Not Found](#http10-404-resource-not-found)
  * [HTTP/1.0 415 Unsupported Media Type](#http10-415-unsupported-media-type)
  * [HTTP/1.0 422 Unprocessable Entity](#http10-422-unprocessable-entity)
  

## Request

```
POST /<xpath> HTTP/1.0
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Content-Length: (bytes)
Content-Type: application/xslt+xml
    Request-Body
XSLT stylesheet
```

### Example

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


## Response

```
HTTP/1.0 200 Success
Storage: 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ (identifier)
Storage-Revision: Revision (number)   
Storage-Space: Total/Used (bytes)
Storage-Last-Modified: Timestamp (RFC822)
Storage-Expiration: Timeout/Timestamp (seconds/RFC822)
Content-Length: (bytes)
```

### Example

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


## Response codes / behavior

### HTTP/1.0 200 Success
- Request was successfully executed

### HTTP/1.0 400 Bad Request
- XPath is malformed
- XSLT Stylesheet is erroneous

### HTTP/1.0 404 Resource Not Found
- Storage is invalid 
- XPath axis finds no target

### HTTP/1.0 415 Unsupported Media Type
- Attribute request without Content-Type text/plain

### HTTP/1.0 422 Unprocessable Entity
- Data in the request body cannot be processed



- - -

[PATCH](api-patch.md) | [TOC](README.md) | [PUT](api-put.md)