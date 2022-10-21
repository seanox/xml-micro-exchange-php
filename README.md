<p>
  <a href="https://github.com/seanox/xml-micro-exchange/pulls
      title="Development is waiting for new issues / requests / ideas">
    <img src="https://img.shields.io/badge/development-passive-blue?style=for-the-badge">
  </a>
  <a href="https://github.com/seanox/xml-micro-exchange/issues">
    <img src="https://img.shields.io/badge/maintenance-active-green?style=for-the-badge">
  </a>
  <a href="http://seanox.de/contact">
    <img src="https://img.shields.io/badge/support-active-green?style=for-the-badge">
  </a>
</p>


# Description
The origin of the project is the desire for an easily accessible place for data
exchange on the Internet. Inspired by JSON-Storages the idea of a feature-rich
equivalent based on XML, XPath and XSLT was born. The focus should be on a
public, volatile and short-term data exchange for (static) web-applications and
IoT.

__Just exchange data without an own server landscape.__  
__Just exchange data without knowing and managing all clients.__

XML-Micro-Exchange is a volatile NoSQL stateless micro datasource for the
Internet. It is designed for easy communication and data exchange of
web-applications and for IoT or for other Internet-based modules and
components. The XML based datasource is volatile and lives through continuous
use and expires through inactivity. They are designed for active and near
real-time data exchange but not as a real-time capable long-term storage.
Compared to a JSON storage, this datasource supports more dynamics, partial
data access, data transformation, and volatile short-term storage. 

__Why all this?__

Static web-applications on different clients want to communicate with each
other, e.g. for games, chats and collaboration.

Smart sensors want to share their data and smart devices want to access this
data and also exchange data with each other.

Clients can establish dynamically volatile networks.

__In this communication are all participants.__  
__No one is a server or master, all are equal and no one has to know the other.__  
__All meet without obligation.__


# Features

- __RESTful__  
  The REST API supports the HTTP methods CONNECT, OPTIONS, GET, POST, PUT,
  PATCH, DELETE. The CONNECT method is not a standard and the function can be
  used alternatively via OPTIONS.  
- __XPath__  
  XPath axes and functions are used for access, navigation and addressing of
  targets in the data source and it is an integral part of the URI with dynamic
  and functional effects.
- __Multible axes and targets__  
  An XPath axis can address several elements and attributes simultaneously,
  which can be changed with one call.
- __XPath Functions__  
  PUT and PATCH support XML structures and static values (text), as well as
  XPath functions that allow dynamic values. 
- __Data Query__  
  Data can be queried in the form of XML structure or substructure if the XPath
  notation represents an axis, otherwise the XPath is interpreted as an XPath
  function and responded with the result as text. Thus XPath provides a dynamic
  syntax for queries.
- __Data Transformation__  
  The POST method supports data transformation via XSLT. Similar to GET, data
  can be queried and then transformed with an XSLT template transmitted via
  POST.
- __JSON Support__  
  All requests can be responded by the service in JSON format.
- __Security__  
  This aspect was deliberately considered and implemented here only in a very
  rudimentary form. The storage(-key) with a length of 1 - 64 characters and
  the individual root element can be regarded as secret.  
  For further security the approach of Basic Authentication, Digest Access
  Authentication and/or Server/Client certificates is followed, which is
  configured outside of the XMEX (XML-Micro-Exchange) at the web server.


# Licence Agreement
LIZENZBEDINGUNGEN - Seanox Software Solutions ist ein Open-Source-Projekt, im
Folgenden Seanox Software Solutions oder kurz Seanox genannt.
 
Diese Software unterliegt der Version 2 der Apache License.

Copyright (C) 2021 Seanox Software Solutions

Licensed under the Apache License, Version 2.0 (the "License"); you may not use
this file except in compliance with the License. You may obtain a copy of the
License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed
under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
CONDITIONS OF ANY KIND, either express or implied. See the License for the
specific language governing permissions and limitations under the License.


# System Requirement
- PHP 7.x or higher (+xsl)


# Downloads
[Seanox XML-Micro-Exchange 1.3.0](https://github.com/seanox/xml-micro-exchange/releases/download/1.3.0/seanox-xmex-1.3.0.zip)  


# Free XML-Micro-Exchange
[https://xmex.seanox.com/xmex!](https://xmex.seanox.com/xmex!)  
65536 Storages x 64 kB  
__Please do not get confused, the API is online.  
Requests without storage identifier (see [manual](manual/README.md#manual)) are
responded with status 400.__  

It is a small server (1 Core, 512 MB, 10 GB SSD) in a big datacenter -- but apparently enough.  
__Sponsors are always welcome.__  
The project needs ~~a good and simple domain~~ and if possible more power.  
It costs about 5 Euro per month for 65536 x 1024 kB with double CPU cores
and double memory.


# Manual
* [Table Of Contents](https://github.com/seanox/xml-micro-exchange/blob/master/manual/README.md#manual)
* [Motivation](https://github.com/seanox/xml-micro-exchange/blob/master/manual/motivation.md)
* [Installation](https://github.com/seanox/xml-micro-exchange/blob/master/manual/installation.md)
* [Configuration](https://github.com/seanox/xml-micro-exchange/blob/master/manual/configuration.md)
  * [Web Server](https://github.com/seanox/xml-micro-exchange/blob/master/manual/configuration.md#web-server)
  * [Parameters](https://github.com/seanox/xml-micro-exchange/blob/master/manual/configuration.md#parameters)
* [Terms](https://github.com/seanox/xml-micro-exchange/blob/master/manual/terms.md)
* [Getting Started](https://github.com/seanox/xml-micro-exchange/blob/master/manual/getting-started.md)
* [API](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api.md)
  * [CONNECT](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-connect.md)
  * [DELETE](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-delete.md)
  * [GET](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-get.md)
  * [OPTIONS](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-options.md)
  * [PATCH](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-patch.md)
  * [POST](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-post.md)
  * [PUT](https://github.com/seanox/xml-micro-exchange/blob/master/manual/api-put.md)
* [Error Handling](https://github.com/seanox/xml-micro-exchange/blob/master/manual/error-handling.md)
* [Development](https://github.com/seanox/xml-micro-exchange/blob/master/manual/development.md)
* [Test](https://github.com/seanox/xml-micro-exchange/blob/master/manual/test.md)


# Changes
## 1.3.0 20210525 
BF: Service: Uniform use of status 204 for 202 / 404  
BF: Service: Uniform use of status 204 for 404 in relation to targets in the storage (axes)  
BF: Service: Optimization/harmonization of content types for XML  
CR: Service: OPTIONS responds with 204 instead of 200  

[Read more](https://raw.githubusercontent.com/seanox/xml-micro-exchange/master/CHANGES)


# Contact
[Issues](https://github.com/seanox/xml-micro-exchange/issues)  
[Requests](https://github.com/seanox/xml-micro-exchange/pulls)  
[Mail](http://seanox.de/contact)  
