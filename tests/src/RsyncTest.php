<?php

namespace PhpRsync;

class RsyncTest extends \PHPUnit\Framework\TestCase
{
  public function testCompileCommand_upload__directoryStandardization()
  {
    $connection = new Connection('local');
    $rsyncer = new Rsync($connection);

    // source and dest directories already have trailing slashes
    $this->assertEquals(
      'rsync -a --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ /dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'])
    );

    // automatically add trailing slashes to source and dest directories
    $this->assertEquals(
      'rsync -a --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ /dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory', 'dest/directory', ['file1.jpg', 'file2.gif']));
  }

  public function testCompileCommand_upload__destinationSameAsRoot_noDestRoot()
  {
    $connection = new Connection('local');
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ / 2>&1',
      $rsyncer->compileCommand('/source/directory/', '', ['file1.jpg', 'file2.gif'])
    );
  }

  public function testCompileCommand_upload__destinationSameAsRoot_hasDestRoot()
  {
    $connection = new Connection('local', '/dest/root');
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ /dest/root/dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'])
    );
  }

  public function testCompileCommand_upload__filenameEscaping()
  {
    $connection = new Connection('local', '/dest/root');
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --include="file1\'s.jpg" --include="file2\"test\".gif" --include="file\\[1\\].jpg" --include="file\\[\\?\\].jpg" --include="file\\[\\*\\].jpg" --exclude="*" /source/directory/ /dest/root/dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', [
        'file1\'s.jpg',
        'file2"test".gif',
        'file[1].jpg',
        'file[?].jpg',
        'file[*].jpg',
      ])
    );
  }

  public function testCompileCommand_upload__akamaiConnection_SSH()
  {
    $home = $_SERVER['HOME'];
    $connection = new Connection('akamai', '/12345', 'hostname', 'username', ['ssh_key' => $home .'/.ssh/id_rsa']);
    $rsyncer = new Rsync($connection);

    $keyString = '-e "ssh -i '. $home .'/.ssh/id_rsa"';

    $this->assertEquals(
      'rsync -a '. $keyString .' --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ username@hostname::username/12345/dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'])
    );
  }

  public function testCompileCommand_upload__akamaiConnection_password()
  {
    $connection = new Connection('akamai', '/12345', 'hostname', 'username', ['password' => 'the_password']);
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ username@hostname::username/12345/dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'])
    );
  }

  public function testCompileCommand_upload__dryRun()
  {
    $connection = new Connection('local');
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --dry-run --verbose --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ /dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'], false, true)
    );
  }

  public function testCompileCommand_delete()
  {
    $connection = new Connection('local');
    $rsyncer = new Rsync($connection);

    $this->assertEquals(
      'rsync -a --delete --include="file1.jpg" --include="file2.gif" --exclude="*" /source/directory/ /dest/directory/ 2>&1',
      $rsyncer->compileCommand('/source/directory/', 'dest/directory/', ['file1.jpg', 'file2.gif'], true)
    );
  }
}
