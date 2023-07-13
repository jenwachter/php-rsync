<?php

namespace PhpRsync;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{
  public function testConnection__local(): void
  {
    $connection = new Connection('local');

    $this->assertEquals('', $connection->getSSHKey());
    $this->assertEquals('/', $connection->getDestination());
  }

  public function testConnection__remote_sshKey(): void
  {
    $home = $_SERVER['HOME'];
    $connection = new Connection('remote', '/dir2', 'hostname', 'username', ['ssh_key' => $home .'/.ssh/id_rsa']);

    $keyString = '-e "ssh -i '. $home .'/.ssh/id_rsa"';
    $this->assertEquals($keyString, $connection->getSSHKey());

    $this->assertFalse(getenv('RSYNC_PASSWORD'));

    $this->assertEquals('username@hostname:/dir2/', $connection->getDestination());
  }

  public function testConnection__akamai_sshKey(): void
  {
    $home = $_SERVER['HOME'];
    $connection = new Connection('akamai', '/dir2', 'hostname', 'username', ['ssh_key' => $home .'/.ssh/id_rsa']);

    $keyString = '-e "ssh -i '. $home .'/.ssh/id_rsa"';
    $this->assertEquals($keyString, $connection->getSSHKey());

    $this->assertFalse(getenv('RSYNC_PASSWORD'));

    $this->assertEquals('username@hostname::username/dir2/', $connection->getDestination());
  }

  public function testConnection__remote_userpass(): void
  {
    $connection = new Connection('remote', '/dir2', 'hostname', 'username', ['password' => 'the_password']);

    $this->assertEquals('', $connection->getSSHKey());
    $this->assertEquals('the_password', getenv('RSYNC_PASSWORD'));
    $this->assertEquals('username@hostname:/dir2/', $connection->getDestination());
  }

  public function testConnection__akamai_userpass(): void
  {
    $connection = new Connection('akamai', '/dir2', 'hostname', 'username', ['password' => 'the_password']);

    $this->assertEquals('', $connection->getSSHKey());
    $this->assertEquals('the_password', getenv('RSYNC_PASSWORD'));
    $this->assertEquals('username@hostname::username/dir2/', $connection->getDestination());
  }



  // cases that should cause exceptions

  public function testConnection__invalidType(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('test is not a valid value for connection type agument.');

    new Connection('test');
  }

  public function testConnection__invalidDestDirectory(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The destination root directory must be an absolute path.');

    new Connection('local', 'dir2');
  }

  public function testConnection__remote_missingHost(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Host is required.');

    new Connection('remote');
  }

  public function testConnection__akamai_missingHost(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Host is required.');

    new Connection('akamai');
  }

  public function testConnection__remote_missingSSHKey(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('SSH key path is invalid.');

    new Connection('remote', '/dir2/', 'hostname', 'username', ['ssh_key' => '/blah']);
  }

  public function testConnection__akamai_missingSSHKey(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('SSH key path is invalid.');

    new Connection('akamai', '/dir2/', 'hostname', 'username', ['ssh_key' => '/blah']);
  }

  public function testConnection__remote_missingUsername(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('User is required.');

    new Connection('remote', '/dir2/', 'hostname');
  }

  public function testConnection__akamai_missingUsername(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('User is required.');

    new Connection('akamai', '/dir2/', 'hostname');
  }

  public function testConnection__remote_missingPassword(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Password is required for non-SSH connections.');

    new Connection('remote', '/dir2/', 'hostname', 'username', ['password' => '']);
  }

  public function testConnection__akamai_missingPassword(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Password is required for non-SSH connections.');

    new Connection('akamai', '/dir2/', 'hostname', 'username', ['password' => '']);
  }
}
