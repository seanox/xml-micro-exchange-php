[Installation](installation.md) | [TOC](README.md) | [Getting Started](getting-started.md)
- - -

# Terms


## Contents Overview

* [Datasource](#apache-httpd)
* [Storage](#seanox-devwex)
* [Storage Identifier](#other-http-servers)
* [Element(s)](#elements)
* [Attribute(s)](#attributes)
* [XPath](#xpath)
* [XPath Axis](#xpath-axis)
* [XPath Axis Pseudo Elements](#xpath-axis-pseudo-elements)
* [XPath Function](#xpath-function)
* [Revision](#revision)
* [UID](#uid)
* [Transaction / Simultaneous Access](#transaction--simultaneous-access)


## Datasource
XML-Micro-Exchange is a data service that manages different data areas.  
The entirety, so the service itself, is the datasource.
Physically this is the data directory.

## Storage
The data areas managed by the XML Micro-Exchange as a data service are called
storage areas. A storage area corresponds to an XML file in the data directory.

## Storage Identifier
Each storage has an identifier, the Storage Identifier.  
The Storage Identifier is used as the filename of the corresponding XML file and
must be specified with each request so that the datasource uses the correct
storage.

## Element(s)
The content of the XML file of a storage provide the data as object or tree
structure. The data entries are called elements.
Elements can enclose other elements.

## Attribute(s)
Elements can also contain direct values in the form of attributes.

## XPath
XPath is a notation for accessing and navigating the XML data structure.  
An XPath can be an axis or a function.

## XPath Axis
XPath axes address or select elements or attributes.  
The axes can have a multidimensional effect.

## XPath Axis Pseudo Elements
For PUT requests it is helpful to specify a relative navigation to an XPath
axis. For example first, last, before, after. This extension of the notation is
supported for PUT requests and is added to an XPath axis separated by two colons
at the end (e.g. `/root/element::end` - means put in element as last).

## XPath Function
+The XPath notation also supports functions that can be used in combination with
axes and standalone for dynamic data requests. In combination with XPath axes,
the addressing and selection of elements and attributes can be made dynamic.

## Revision
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

## UID
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
 
## Transaction / Simultaneous Access
XML-Micro-Exchange supports simultaneous access.  
Read accesses are executed simultaneously.  
Write accesses creates a lock and avoids dirty reading.



- - -

[Installation](installation.md) | [TOC](README.md) | [Getting Started](getting-started.md)
