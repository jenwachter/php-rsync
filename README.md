# PHP Rsync

A simple rsync library for PHP, able to perform local, remote, and [Akamai NetStorage](https://techdocs.akamai.com/netstorage/docs/use-rsync) syncs. Note that this library is not all-inclusive of everything that can be done with the rsync command.

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)

## Requirements

* PHP >= 8.0

## Installation

To install the library, you will need to use [Composer](https://getcomposer.org/download/) in your project.

```bash
composer require jenwachter/php-rsync
```

## Usage

### Create a connection

To start using the library, you must first create a connection. This connection is then passed to the `Rsync` class.

```php
$connection = new PhpRsync\Connection($type);
$rsync = new Rsync($connection);
````

There are three types of connections:

### Local

Use this type of connection when the source and destination are on the same machine. Example: transferring files from one directory to another on your local machine.

```php
$local = new PhpRsync\Connection(
  'local',
  '/path/to/destination/root/dir'
);

$rsync = new Rsync($local);
````

### Remote

Use this type of connection when the source and destination are different machines. Example: transferring files from your local machine to a remote machine.

```php
$remote = new PhpRsync\Connection(
  'remote',
  '/path/to/destination/root/dir',
  'remote.host.com',
  'username',
  ['ssh_key' => '/path/to/ssh/key']
);

$rsync = new Rsync($remote);
````

Alternatively, you can use a password for authentication by passing `['password' => 'your_password']` as the fifth parameter.

### Akamai

Use this type of connection when the destination is Akamai NetStorage. For details on how to connect to NetStorage, see the [NetStorage Rsync documentation](https://techdocs.akamai.com/netstorage/docs/use-rsync)

```php
$akamai = new PhpRsync\Connection(
  'akamai',
  '/path/to/destination/root/dir',
  'cpcode.rsync.upload.akamai.com',
  'username',
  ['ssh_key' => '/path/to/ssh/key']
);

$rsync = new Rsync($akamai);
````

### Perform rsync transfers

```php
$options = [];

$rsync->run(
  '/path/to/source/dir',
  'relative/path/to/destination/dir',
  $options
);
```

#### Available options

* __archive__: Boolean. Adds `--archive` flag, if true. Default: true
* __compress__: Boolean. Adds `--compress` flag, if true. Default: true
* __cwd__: String. Changes the current working directory prior to running the `rsync` command. Default: null
* __delete__: Boolean. Adds `--delete` flag, if true. Default: false
* __dryrun__: Boolean. Adds `--dry-run` and `--verbose` flags, if true. Default: false
* __exclude__: Array|String. If a string is passed, adds `--exclude="<string>"` flag. if an array of strings is passed, adds multiple `--exclude="<string>"` flags. Default: null
* __include__: Array|String. If a string is passed, adds `--include="<string>"` flag. if an array of strings is passed, adds multiple `--include="<string>"` flags. Default: null
* __relative__: Boolean. Adds `--relative` flag, if true. Default: false
