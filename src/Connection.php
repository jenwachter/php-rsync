<?php

namespace PhpRsync;

class Connection
{
  /**
   * @param string $type               Type of connection. Options: local, remote, akamai (akamai daemon)
   * @param string $destinationRootDir Speciify directory on the remote system that this connection should have access to.
   * @param string|null $host          Remote host (not required for local connections)
   * @param string|null $user          Remote user (not required for local connections)
   * @param array|null $auth           For SSH key authentication: ['ssh_key' => '/path/to/ssh/key']
   *                                   For username/password authentication: ['password' => 'the_password']
   */
  public function __construct(
    protected string $type = 'local',
    protected string $destinationRootDir = '/',
    protected string|null $host = null,
    protected string|null $user = null,
    protected array|null $auth = null
  )
  {
    $this->validateType();
    $this->validateDestinationRootDir();
    $this->validateHost();
    $this->validateAuth();

    if (!empty($this->auth['password'])) {
      putenv("RSYNC_PASSWORD={$this->auth['password']}");
    }
  }

  public function getSSHKey(): string
  {
    return isset($this->auth['ssh_key']) ?
      '-e "ssh -i '. $this->auth['ssh_key'] .'"' :
      '';
  }

  /**
   * Get the destination string
   * @param string|null $directory Optional destination directory (added to root destination directory)
   * @return string
   */
  public function getDestination(string|null $directory = ''): string
  {
    if ($this->type === 'akamai') {
      return "{$this->user}@{$this->host}::{$this->user}{$this->destinationRootDir}{$directory}";
    }

    if ($this->type === 'remote') {
      return "{$this->user}@{$this->host}:{$this->destinationRootDir}{$directory}";
    }

    // local
    return $this->destinationRootDir . $directory;
  }

  protected function validateType(): void
  {
    if (!in_array($this->type, ['local', 'remote', 'akamai'])) {
      throw new \InvalidArgumentException($this->type . ' is not a valid value for connection type agument.');
    }
  }

  protected function validateDestinationRootDir(): void
  {
    if (!str_starts_with($this->destinationRootDir, '/')) {
      // throw an exception to help user avoid mistakes
      throw new \InvalidArgumentException('The destination root directory must be an absolute path.');
    }

    if (!str_ends_with($this->destinationRootDir, '/')) {
      // make sure there is a trailing slash
      $this->destinationRootDir .= '/';
    }
  }

  protected function validateHost(): void
  {
    if ($this->type !== 'local' && empty($this->host)) {
      throw new \InvalidArgumentException('Host is required.');
    }
  }

  protected function validateAuth(): void
  {
    if ($this->type === 'local') {
      return;
    }

    if (empty($this->user)) {
      throw new \InvalidArgumentException('User is required.');
    }

    if (isset($this->auth['ssh_key']) && !file_exists($this->auth['ssh_key'])) {
      throw new \InvalidArgumentException('SSH key path is invalid.');
    }

    if (isset($this->auth['password']) && empty($this->auth['password'])) {
      throw new \InvalidArgumentException('Password is required for non-SSH connections.');
    }
  }
}
