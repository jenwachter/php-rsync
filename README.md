# PHP Rsync

A simple rsync library for PHP, able to perform to local, remote, and [Akamai NetStorage](https://techdocs.akamai.com/netstorage/docs/use-rsync) hosts.

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

#### Upload files

```php
$rsync->upload(
  '/path/to/source/dir',
  'relative/path/to/destination/dir',
  ['file1.jpg', 'file2.jpg', 'file3.jpg']
);
```

### Delete files

Note: the files must first be removed from the source directory in order for them to be deleted in the destination directory.

```php
$rsync->delete(
  '/path/to/source/dir',
  'relative/path/to/destination/dir',
  ['file1.jpg', 'file2.jpg', 'file3.jpg']
);
```

