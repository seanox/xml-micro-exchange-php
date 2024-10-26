[Getting Started](getting-started.md) | [TOC](README.md) | [Error Handling](error-handling.md)
- - -

# API

The API uses the HTTP.  
The URI typically contains a noticeable separator to divide it into context
path and XPath.  
For the API only the XPath is relevant.

```
https://xmex.seanox.com/xmex!xpath(-function)
<-------------------------->|<-------------->
        Context Path              XPath
```

In some cases, the XPath syntax may not be supported as a URI by the client or
provider. In these cases, the XPath can alternatively be used as a query string
or hexadecimal or Base64 encoded.

```
https://xmex.seanox.com/xmex!count(//items[@id<0])
https://xmex.seanox.com/xmex!count(%2F%2Fitems[@id<0])
https://xmex.seanox.com/xmex!?636F756E74282F2F6974656D735B4069643C305D29
https://xmex.seanox.com/xmex!?Y291bnQoLy9pdGVtc1tAaWQ8MF0p
```

The request supports the following additional headers:

<table>
  <thead>
    <tr>
      <th>
        Request Header
      </th>
      <th>
        Description
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        Storage
      </td>
      <td>
        Storage identifier optional with  name of the root element
      </td>
    </tr>
  </tbody>
</table>

Also the reponse has additional headers:

<table>
  <thead>
    <tr>
      <th>
        Response Header
      </th>
      <th>
        Description
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        Storage
      </td>
      <td>
        Storage identifier without the name of the root element
      </td>
    </tr>
    <tr>
      <td>
        Storage-Revision
      </td>
      <td>
        Current revision of the storage
      </td>
    </tr>
    <tr>
      <td>
        Storage-Space
      </td>
      <td>
        Information about the capacity of the storage<br/>
        total/used in bytes
      </td>
    </tr>
    <tr>
      <td>
        Storage-Last-Modified
      </td>
      <td>
        Timestamp (RFC822) of the last access
      </td>
    </tr>
    <tr>
      <td>
        Storage-Expiration
      </td>
      <td>
        Timestamp (RFC822) when the storage will expire
      </td>
    </tr>
    <tr>
      <td>
        Storage-Expiration-Time
      </td>
      <td>
        Idle time in milliseconds until storage expires
      </td>
    </tr>
    <tr>
      <td>
        Storage-Effects
      </td>
      <td>
        Only for PUT, PATCH, DELETE<br/>
        UIDs that are directly affected by the request
      </td>
    </tr>
    <tr>
      <td>
        Execution-Time
      </td>
      <td>
        Duration of request processing in milliseconds<br/>
        During development, the creation of trace headers is not included
      </td>
    </tr>
    <tr>
      <td>
        Error
      </td>
      <td>
        Unique error number from the logging in combination with status 500
      </td>
    </tr>
    <tr>
      <td>
        Message
      </td>
      <td>
        Detailed error message in combination with status 400 / 422
      </td>
    </tr>
    <tr>
      <td>
        Trace-Request-Header-Hash
      </td>
      <td>
        only during development<br/>
        Hash value of the request header for the unit tests<br/>
        Contains only the headers relevant to the API 
      </td>
    </tr>
    <tr>
      <td>
        Trace-Request-Body-Hash
      </td>
      <td>
        only during development<br/>
        Hash value of the request body for the unit tests
      </td>
    </tr>
    <tr>
      <td>
        Trace-Response-Header-Hash
      </td>
      <td>
        Only during development<br/>
        Hash value of the response header for the unit tests<br/>
        Contains only the headers relevant to the API 
      </td>
    </tr>
    <tr>
      <td>
        Trace-Response-Body-Hash 
      </td>
      <td>
        Only during development<br/>
        Hash value of the response body for the unit tests
      </td>
    </tr>
    <tr>
      <td>
        Trace-Storage-Hash
      </td>
      <td>
        Only during development<br/>
        Hash value of the storage content for the unit tests
      </td>
    </tr>
  </tbody>
</table>

## Contents Overview

* [CONNECT](api-connect.md)
  * [Request](api-connect.md#request)
  * [Response](api-connect.md#response)
  * [Response codes / behavior](api-connect.md#response-codes--behavior)
* [DELETE](api-delete.md)
  * [Request](api-delete.md#request)
  * [Response](api-delete.md#response)
  * [Response codes / behavior](api-delete.md#response-codes--behavior)
* [GET](api-get.md)
  * [Request](api-get.md#request)
  * [Response](api-get.md#response)
  * [Response codes / behavior](api-get.md#response-codes--behavior)
* [OPTIONS](api-options.md)
  * [Request](api-options.md#request)
  * [Response](api-options.md#response)
  * [Response codes / behavior](api-options.md#response-codes--behavior)
* [PATCH](api-patch.md)
  * [Request](api-patch.md#request)
  * [Response](api-patch.md#response)
  * [Response codes / behavior](api-patch.md#response-codes--behavior)
* [POST](api-post.md)
  * [Request](api-post.md#request)
  * [Response](api-post.md#response)
  * [Response codes / behavior](api-post.md#response-codes--behavior)
* [PUT](api-put.md)
  * [Request](api-put.md#request)
  * [Response](api-put.md#response)
  * [Response codes / behavior](api-put.md#response-codes--behavior)



- - -

[Getting Started](getting-started.md) | [TOC](README.md) | [Error Handling](error-handling.md)