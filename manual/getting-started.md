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
Each storage has a 36-character name consisting only of numbers and letters.
Optionally there is a name for the root element, as default `data` is used.
Which are the two secrets a regulars' table can have.  


## Place and Address

Who wants to participate in the regulars' table needs the place and address.  
For the XML-Micro-Exchange, this is the URL of the API and a storage
identifier.

__URL:__ [https://seanox.com/xmex!](https://seanox.com/xmex!)  
Do not panic when opening the URL in the browser, the service is online and the
error status is normal for requests without storage identifier.

The storage identifier is exactly 36 characters long and consists only of
numbers and upper case letters. Any character string can be used.

__Storage Identifier:__ `THEREGULARSTABLEASVERYASIMPLEEXAMPLE`

Here it is important to understand that we are in a public space and cannot
exclude that the storage identifier is already used, which can be queried.  
If you need more exclusivity and privacy, you should run the XML-Micro-Exchange
yourself, which can then be secured with authorization and/or certificates.

Optionally, we can also want a name for the root element in the storage.

__Root-Element:__ `table`

Here it is important to know that all participants must know three things: URL,
Storage Identifier and Name of the root-element.


## The First Guest

There is still a simple table in the pub.  
Now comes the first guest. Opens the door to the pub, goes to the table and
thus opens the regular's table.

For the XML Micro-Exchange, this is a CONNECT request with the familiar things:
Address, Storage Identifier and name from the root element.

Here it is important to know that the example in the use the full URL instead
of the usual URI.

```
CONNECT https://seanox.com/xmex! HTTP/1.0
Storage: THEREGULARSTABLEASVERYASIMPLEEXAMPLE table
```

Because CONNECT is not a standard HTTP method, the OPTIONS method can also be
used.

```
OPTIONS https://seanox.com/xmex! HTTP/1.0
Storage: THEREGULARSTABLEASVERYASIMPLEEXAMPLE table
```

Everyone who knows the address and storage identifier and, as in our example,
the name of the root element can participate in the regulars' table.



Our first guest is John Doe and he can't know who is coming and if they will
know him. That's why he wants to publish his name at the regulars' table.  
He knows how to do it, because he knows the rituals and rules of the regulars'
table and the pub.  
So he knows by response status 201 that he opened the regulars' table, that
there is a guest list where he signs in as a guest and that there is also a
section for the conversations.  
His task now is to prepare the regulars' table.  
In other databases, this can be compared to preparing the schema.
What he does not know, because we are in the Internet, whether in the meantime
someone else has come to the regulars' table.  
The rules of the regulars' table say: Everyone who comes to the regulars'
table must check whether the regulars' table is prepared correctly. If not he
must do it, even if he is not the first. There are several reasons why the
preparation of the regulars' table was not completed by the first guest.

Now it gets a bit tricky because of the simultaneous accesses and because there
is no locking mechanism.

So John knows the schema:

```xml
<table>
  <guests/>
  <conversation/>
</table>
```

Because there is a risk that he is not alone and that his scheme could delete
already existing data, he proceeds relatively.  
Relative means, it does not create the complete schema, but it adds the
sections one by one.  
Because the rule of the regulars' table says: Always use the first element in
the storage for a section, all participants get along with it, if sections are
created several times.  
So Jon creates the sections at the end of the table element and then deletes
all superfluous elements if they are not the first.

TODO:



- - -

[Terms](terms.md) | [TOC](README.md) | [API](api.md)