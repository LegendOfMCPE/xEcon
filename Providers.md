Data providers
===
A comparison of data providers available in the core.

## Types
Currently, there are three types of data providers available. They are, respectively:
* SQLite3 database (named `SQLite3`)
* Easy-read database (named `Disk`)
* MySQL database (named `MySQLi`)

The following will be a brief introduction and comparison of these data providers and a quick guide to how to configure them.

## Introduction
### SQLite3 databases
An SQLite3 database is a single file on the disk. It is saved in binary, which means, it is hard or even impossible to edit it with a text editor. However, the database is much smaller in size, and it only takes one file. The bad side of it, however, is that it reads/writes the whole database every time data have to be loaded/modified. Even though, it is fast when a lot of data is collected or modified at the same time.

### Easy-read databases
As the name suggests, an easy-read database is easy to read and modify. It has one JSON file per economic entity, plus one LIST file for a list of all IPs. By default the pretty print option is enabled to allow easy reading and editing of the data. The bad side of it is the large amount of files and that it is a generally very bulky task to modify a large amount, or all, of the data in the database. This database is most favorable for developers who want to have easy debugging.

### MySQL databases
A MySQL database connects to an online database and all tasks are done online. Data are fetched from the online database. It is favorable when a pattern of modifications has to be completed, or when you host your server on multiple machines that you want to share the same data.
