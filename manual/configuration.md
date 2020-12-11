[Installation](installation.md) | [TOC](README.md) | [Terms](terms.md)
- - -

# Configuration

__This chapter is only relevant if you want to run the Datasource on your own
server. If you want to use an existing Datasource on the Internet, you can skip
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



- - -

[Installation](installation.md) | [TOC](README.md) | [Terms](terms.md)
