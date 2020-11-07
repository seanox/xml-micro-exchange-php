<p>
  <a href="https://github.com/seanox/xml-online-storage/pulls">
    <img src="https://img.shields.io/badge/development-active-green?style=for-the-badge">
  </a>
  <a href="https://github.com/seanox/xml-online-storage/issues">
    <img src="https://img.shields.io/badge/maintenance-active-green?style=for-the-badge">
  </a>
  <a href="http://seanox.de/contact">
    <img src="https://img.shields.io/badge/support-active-green?style=for-the-badge">
  </a>
</p>


# Description
XML-Micro-Exchange is a volatile RESTful micro datasource.  
It is designed for easy communication and data exchange of web-based
applications and for IoT.  
The XML based datasource is volatile and lives through continuous use and
expires through inactivity. They are designed for active and near real-time data
exchange but not as a real-time capable long-term storage.  
Compared to a JSON storage, this datasource supports dynamic and partial access,
data transformation and a volatile short-term storage.  


# Features
TODO:

## RESTful
The REST API supports the HTTP methods CONNECT, OPTIONS, GET, POST, PUT, PATCH,
DELETE.  
The CONNECT method is not a standard and the function can be used
alternatively via OPTIONS.  

## XPath
TODO:

## Multible axes and targets
XPath can address multiple elements and attributes simultaneously via multiple
axes, which can be changed with one call.

## XPath Functions
PUT and PATCH support XML structures and static values (text), as well as XPath
functions that allow dynamic values. 

## Data Transformation
The POST method supports data transformation via XSLT.  
Similar to GET, data can be queried and then transformed with an XSLT template
transmitted via POST.

## Security
This aspect was deliberately considered and implemented here only in a very
rudimentary form. Only the storage(-key) with a length of 36 characters can be
regarded as secret.  
For further security the approach of Basic Authentication, Digest Access
Authentication and/or Server/Client certificates is followed, which is
configured outside of the XMEX (XML-Micro-Exchange) at the web server.


# Licence Agreement
LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 
Diese Software unterliegt der Version 2 der Apache License.

Copyright (C) 2020 Seanox Software Solutions

Licensed under the Apache License, Version 2.0 (the "License"); you may not use
this file except in compliance with the License. You may obtain a copy of the
License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed
under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
CONDITIONS OF ANY KIND, either express or implied. See the License for the
specific language governing permissions and limitations under the License.


# System Requirement
- PHP 7.x or higher


# Downloads
TODO:


# Installation
TODO:


# Configuration
The REST API is configured as an absolute (hungry) virtual path. So all requests
starting with the virtual path are redirected to the PHP script `./service.php`.  
Means that the script itself cannot be called.  
The paths of all requests are passed as path-info and thus as virtual paths.

The following HTTP methods must be allowed:  
`CONNECT`, `OPTIONS`, `GET`, `PUT`, `PATCH`, `POST`, `DELETE`  
The `CONNECT` method is not an HTTP standard, alternative `OPTIONS` can be used.

When using PHP as CGI, the HTTP methods may also need to be allowed. 


## Apache HTTPD

Direct to a physical or virtual host:

```
#httpd-ssl.conf
RewriteEngine on
RewriteRule ^/xmex(/.*)*$ service.php
```

Or in the .htaccess file:

```
#.htaccess
RewriteEngine on
RewriteRule (.*) service.php
```
Root can also be used.  
A context path is not required.

## Seanox Devwex
```
[SERVER:HTTP:CGI]
  ...
  PHP = CONNECT OPTIONS GET PUT PATCH POST DELETE > ...
  
[SERVER:HTTP:REF]
  ...
  XMEX = /xmex > /xml-micro-exchange/service.php [A]
```
Root can also be used.  
A context path is not required.

## Other HTTP servers
Something like Apache HTTPD or Seanox Devwex.  
Alternatively, the script can be called directly and passed to XPath as a query
string.


# Changes (Change Log)
TODO:


# Contact
[Issues](https://github.com/seanox/xml-online-storage/issues)  
[Requests](https://github.com/seanox/xml-online-storage/pulls)  
[Mail](http://seanox.de/contact)  


# Thanks!
<img src="https://raw.githubusercontent.com/seanox/seanox/master/sources/resources/images/thanks.png">

[JetBrains](https://www.jetbrains.com/?from=seanox)  
Sven Lorenz  
Andreas Mitterhofer  
[novaObjects GmbH](https://www.novaobjects.de)  
Leo Pelillo  
Gunter Pfannm&uuml;ller  
Annette und Steffen Pokel  
Edgar R&ouml;stle  
Michael S&auml;mann  
Markus Schlosneck  
[T-Systems International GmbH](https://www.t-systems.com)
