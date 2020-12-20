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

* [Apache HTTPD](#apache-httpd)
* [Seanox Devwex](#seanox-devwex)
* [Other HTTP Servers](#other-http-servers)


## Apache HTTPD

```
#.htaccess
RewriteEngine on
RewriteRule (^xmex!.*) service.php [L]
RewriteRule service.php - [L]
RewriteRule (.*) - [R=404,L]
```

Root can also be used.  
A context path is not required, but it is recommended to use a context path
that ends with a non-alphanumeric character to make the separation between URL
and XPath more visible.  


## Seanox Devwex

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

## Other HTTP Servers

Something like Apache HTTPD or Seanox Devwex.  
Alternatively, the script can be called directly and passed to XPath as a query
string.

## Parameter

Overview of the configurable parameters / constants:

<table>
  <thead>
    <tr>
      <th>
        Parameter
      </th>
      <th>
        Default
      </th>
      <th>
        Description
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <code>Storage::DIRECTORY</code>
      </td>
      <td>
        <code>./data</code>
      </td>
      <td>
        Directory of the data storage, which is configured with the required
        permissions by the script at runtime.
      </td>
    </tr>
    <tr>
      <td>
        <code>Storage::QUANTITY</code>
      </td>
      <td>
        <code>65535</code>
      </td>
      <td>
        Maximum number of files in data storage.<br/>
        Exceeding the limit causes the status 507 - Insufficient Storage.
      </td>
    </tr>
    <tr>
      <td>
        <code>Storage::SPACE</code>
      </td>
      <td>
        <code>256 *1024</code>
      </td>
      <td>
        Maximum data size of files in data storage in bytes.<br/>
        The value also limits the size of the requests(-body).
      </td>
    </tr>
    <tr>
      <td>
        <code>Storage::TIMEOUT</code>
      </td>
      <td>
        <code>15 *60</code>
      </td>
      <td>
        Maximum idle time of the files in seconds.<br/>
        If the inactivity exceeds this time for a Storage , it expires.
      </td>
    </tr>
    <tr>
      <td>
        <code>Storage::CORS</code>
      </td>
      <td>
        <code>["Allow-Origin" =&gt; "*"]</code>
      </td>
      <td>
        Optional CORS response headers as associative array.<br/>
        e.g. Allow-Origin, Allow-Credentials, Allow-Methods, Allow-Headers,
        Max-Age, Expose-Headers<br/> 
        The prefix Access-Control is added automatically.
        e.g. Allow-Origin -&rarr; Access-Control-Allow-Origin
      </td>
    </tr>
    <tr>
      <td>
        <code>Storage::PATTERN_HTTP_REQUEST_URI</code>
      </td>
      <td>
        <code>/^(.*?)[!#\$\*:\?@\|~]+(.*)$/i</code>
      </td>
      <td>
        Pattern for separating URI-Path and XPath.<br/>
        If the pattern is empty, null or false, the request URI without context
        path will be used. This is helpful when the service is used as a domain.<br/>
        <br/>
        Group 0. Full match<br/>
        Group 1. URI-Path<br/>
        Group 2. XPath<br/>
      </td>
    </tr>
  </tbody>
</table>



- - -

[Installation](installation.md) | [TOC](README.md) | [Terms](terms.md)
