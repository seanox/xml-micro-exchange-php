[Motivation](motivation.md) | [TOC](README.md) | [Configuration](configuration.md)
- - -

# Installation

__This chapter is only relevant if you want to run the Datasource on your own
server.  
If you want to use an existing Datasource on the Internet, you can skip
this chapter.__

XML-Micro-Exchange consists of only one file.  
A release always contains with service.php and service-dev.php two versions.
Functionally, both are the same. One is compact without comments and one is
documented. Choose one the desired file and put it in your web space.

If possible, the script uses its own directory, but it also works together
alongside other scripts.  

XML-Micro-Exchange needs a location for the storages at runtime.  
By default the data-directory is created in the working directory by the
script. The required permissions are set automatically.  
The file name of the service.php or service-dev.php can be changed as desired.

__For PHP, the extensions xmlrpc and xsl must be enabled.__



- - -

[Motivation](motivation.md) | [TOC](README.md) | [Configuration](configuration.md)