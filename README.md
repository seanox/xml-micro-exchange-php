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
TODO:


# Features
TODO:

## Security
This aspect was deliberately considered and implemented here only in a very
rudimentary form. Only the storage(-key) with a length of 36 characters can be
regarded as secret.  
For further security the approach of Basic Authentication, Digest Access
Authentication and/or Server/Client certificates is followed, which is
configured outside of the XMDS (XML-Micro-Datasource) at the web server.


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
`CONNECT`, `OPTIONS`, `GET`, `PUT`, `PATCH`, `DELETE`  
The `CONNECT` method is not an HTTP standard, alternative `OPTIONS` can be used.

When using PHP as CGI, the HTTP methods may also need to be allowed. 


## Apache HTTPD
```
#.htaccess
RewriteEngine on
RewriteRule ^/xmds(/.*)*$ service.php
```
Root can also be used.  
A context path is not required.

## Seanox Devwex
```
[SERVER:HTTP:CGI]
  ...
  PHP = ALL > ...
  
[SERVER:HTTP:REF]
  ...
  XMDS = /xmds/ > /xml-micro-datasource/service.php [A]
```
Root can also be used.  
A context path is not required.

## Other HTTP servers
Something like Apache HTTPD or Seanox Devwex.


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
