[Terms](terms.md) | [TOC](README.md) | [API](api.md)
- - -

# Getting Started

These instructions describe exclusively the use of the REST API.  
Installation and configuration are only for your own operation of the
datasource and are described in separate chapters.


## Preamble

What is and does XML-Micro-Exchange?

It is an volatile NoSQL datasource on the internet.  
The data source is a gathering place for data exchange for static
web-applications and IoT or for other Internet-based modules and components.

NoSQL is a hint at the feature set and support for querying and transforming
data, as well functions for the data access. Because the data source can do
more than just write and read data.  

Volatile means that the data is not stored permanently. The data source lives
on regular use, without this its stored data will expire.

Think of the data source as a regulars' table in a pub in their town.  
Anyone who knows the address can come.  
They are in the public space and yet private.  
Everyone is equal. Only the participants have their rituals and rules, but not
the place.  
Everyone can say and ask what he wants and everyone can decide for himself
which data and in which form he brings in or takes out.

In the following, we will take a closer look at the regulars' table and
understand, implement and use.


## The Regulars' Table

It in a simple table in a pub.
Only the presence of guests and their ritual of the regulars' table, make this
table to a regulars' table. Whether or not the table is already in the pub at
that time, or is another regular table for other guests, can be ignored.

In the context of the XML-Micro-Exchange, the regulars' table is an XML file
called Storage.  
Each storage has a 1 - 64 character name consisting only of numbers and letters.
Optionally there is a name for the root element, as default `data` is used.
Which are the two secrets a regulars' table can have.  


## Place and Address

Who wants to participate in the regulars' table needs the place and address.  
For the XML-Micro-Exchange, this is the URL of the API and a storage
identifier.

__URL:__ [https://seanox.com/xmex!](https://seanox.com/xmex!)  
Do not panic when opening the URL in the browser, the service is online and the
error status is normal for requests without storage identifier.

The storage identifier is 1 - 64 characters long and consists only of numbers,
upper/lower case letters and underscore. Any character string can be used.

For our example, we will derive the storage identifier from the following old
Berlin address.

&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Wagner-Eck  
&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Siegfriedstr. 1  
&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;1130 Berlin

__Storage Identifier:__ `DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01`

Here it is important to understand that we are in a public space and cannot
exclude that the storage identifier is already used, which can be queried. If
you need more exclusivity and privacy, you should run the XML-Micro-Exchange
yourself, which can then be secured with authorization and/or certificates.

Optionally, we can also want a name for the root element in the storage.

__Root-Element:__ `table`

Here it is important to know that all participants must know three things: URL,
Storage Identifier and Name of the root-element.


## The First Guest

There is still a simple table in the pub.  
Now comes the first guest. Opens the door to the pub, goes to the table and
thus opens the regular's table.

For XML-Micro-Exchange, this is a CONNECT request with the familiar things:
Address, Storage Identifier and name from the root element.

Here it is important to know that the example in the use the full URL instead
of the usual URI.

```
CONNECT https://seanox.com/xmex! HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

Because CONNECT is not a standard HTTP method, the OPTIONS method can also be
used.

```
OPTIONS https://seanox.com/xmex! HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

Everyone who knows the address and storage identifier and, as in our example,
the name of the root element can participate in the regulars' table.

Our first guest is John Doe.
John knows the rituals and rules of the regulars' table and the pub.  
___A rule from the regulars' table: Anyone joining the regulars' table must
check that it has been arranged correctly and do so when required.___  

John knows from the response status 201 that he was the first to create the
regulars' table, that there is a guest list where he signs in as a guest and
that there is also a section for the conversations. But he does not know
whether someone else has joined the regulars' table in the meantime and
followed the rule for arranging it. John's scheme could delete data that was
created in the meantime.

XML-Micro-Exchange supports simultaneous accesses, but no locking mechanism.  
The solution and also the reason why there is no locking mechanism can be found
in the XPath functions.  
We can initialize the regulars' table relative.  

```
PUT https://seanox.com/xmex!/table::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 49

<guests>
  <persons/>
  <conversation/>
</guests>
```

___A rule from the regulars' table: Always use only the first element of
elements of the sections.___
So we can completely put the scheme at the end of the table. Even though the
elements may exist multiple times, all participants will only use the first
one. Through the XPath we can delete all superfluous elements in a second
request, because we know there will be only two.  

```
DELETE https://seanox.com/xmex!/table/guests[position()>1] HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

Now John has arranged the regulars' table.

John is now waiting for more guests and the innkeeper.  
So that all notice him, he puts his name in the guest list.

```
PUT https://seanox.com/xmex!/table/guests[1]/persons::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 56

<person name="John Doe" email="john.doe@example.local"/>
```

John could make sure and check beforehand if there are people with the same
name and he could delete any duplicate entries.  
We ignore that in this example.

While John waits, he sends pull requests to keep the storage with the data and
to get the revision from the storage.

```
OPTIONS https://seanox.com/xmex! HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

The revision is applied to all elements in the storage. It is a counter of
changes in the storage per request. When the data in the storage is changed
with a request, the counter is incremented. The newly calculated revision is
then set for all changed elements and recursively for all parent elements. This
way the root element always has the latest revision. The clients can use the
revision to find out if there are changes on the element level.  
Technically, the revision is the `___rev` attribute in the XML file from the
storage. All elements have this attribute, which is managed by the storage. The
`___rev` attribute is read-only.

John also uses the revision.  
If the revision of the storage changes, he knows that the data for the
regulars' table has changed. This way he doesn't have to monitor all the data.


## More Guests are Coming

Three more guests enter the pub and go to the regulars' table.  
They are Jane Doe, Mike Ross and Dan Star.  
They are also all familiar with the rituals and rules of the regulars' table
and the pub and thus do the same as John.

They join the regulars' table with CONNECT or OPTIONS and are informed by the
server status 202 that the regulars' table already exists. Because they do not
know the state of the regulars' table, they arrange it in the same way as John.

```
OPTIONS https://seanox.com/xmex! HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```
```
PUT https://seanox.com/xmex!/table::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 49

<guests>
  <persons/>
  <conversation/>
</guests>
```
```
DELETE https://seanox.com/xmex!/table/guests[position()>1] HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

And they also put their names in the guest list.

```
PUT https://seanox.com/xmex!/table/guests[1]/persons::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 56

<person name="Jane Doe" email="jane.doe@example.local"/>
```
```
PUT https://seanox.com/xmex!/table/guests[1]/persons::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 5

<person name="Mike Ross" email="mike.ross@example.local"/>
```
```
PUT https://seanox.com/xmex!/table/guests[1]/persons::last HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
Content-Type: application/xslt+xml
Content-Lenght: 57

<person name="Dan Star" email="dan.star@@example.local"/>
```

John notices the new guests and greets everyone.

```
GET https://seanox.com/xmex!count(/table/guests[1]/persons/person)>1 HTTP/1.0
Storage: DE_1130_BERLIN_SIEGFRIESTR_1_WAGNER_ECK_T_01 table
```

With the support of XPath functions, the query for new guests can be
implemented like this.  
The response is `true` or `false`.  
A Content-Type is not required for the request. Return values of an XPath
function are always of type `text/plain`.

TODO:



- - -

[Terms](terms.md) | [TOC](README.md) | [API](api.md)