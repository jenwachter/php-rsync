<?php

namespace PhpRsync;

use Psr\Log\LoggerInterface;

class Rsync
{
  public function __construct(protected Connection $connection, protected LoggerInterface|null $logger = null)
  {

  }

  /**
   * Upload a file.
   * In the examples, the full path of the file to be uploaded is:
   * /var/www/html/project/uploads/2023-05/filename.jpg
   * @param string $basepath System path that cooresponds to Akamai upload directory.
   *                         Example: /var/www/html/project
   *                         Akamai upload directory will contain content in `project` directory
   * @param string $filepath Filepath relative to basepath
   *                         Drupal example: uploads/2023-05/filename.jpg
   * @param bool   $dryRun   Generate the command in dry-run mode (true) or not (false)
   * @return int
   * @throws \ErrorException
   */

  /**
   * @param string $sourceDirectory       Where the files are on the local system
   * @param string $destinationDirectory  Where the files are going on the destination system relative to
   *                                      Connection::destinationRootDir
   * @param array  $files
   * @param bool   $dryRun
   * @return int
   */
  public function upload(string $sourceDirectory, string $destinationDirectory, array $files = [], bool $dryRun = false): int
  {
    return $this->rsync($sourceDirectory, $destinationDirectory, $files, false,$dryRun);
  }

  /**
   * @param string $sourceDirectory
   * @param string $destinationDirectory
   * @param array  $files
   * @param bool   $dryRun
   * @return int
   */
  public function delete(string $sourceDirectory, string $destinationDirectory, array $files = [], bool $dryRun = false): int
  {
    return $this->rsync($sourceDirectory, $destinationDirectory, $files, true,$dryRun);
  }

  protected function rsync(string $sourceDirectory, string $destinationDirectory, array $files = [], bool $delete = false, bool $dryRun = false)
  {
    if (empty($files)) {
      throw new \ErrorException('List of files to include cannot be empty.');
    }

    $command = $this->compileCommand($sourceDirectory, $destinationDirectory, $files, $delete,$dryRun);

    exec($command, $output, $resultCode);

    if ($dryRun && $this->logger) {
      $this->logger->info('NetStorage RSYNC dry run', [
        'command' => $command,
        'output' => $output,
      ]);
    }

    if ($resultCode > 0) {
      throw new \ErrorException('NetStorage RSYNC failed. ' . implode('\n', $output));
    }

    return $resultCode;
  }

  public function compileCommand(string $sourceDirectory, string $destinationDirectory, array $files = [], bool $delete = false, bool $dryRun = false)
  {
    $include = array_map(function ($filename) {
      $sanitized = addcslashes($filename, '"');
      return "--include=\"{$sanitized}\"";
    }, $files);

    $command = [
      'rsync -a',
      $this->connection->getSSHKey(),
      $dryRun ? '--dry-run --verbose' : '',
      $delete ? '--delete' : '',
      implode(' ', $include),
      '--exclude="*"',
      $this->standardizeDirectory($sourceDirectory),
      $this->connection->getDestination($this->standardizeDirectory($destinationDirectory)),
      '2>&1', // redirect to STDOUT (php can capture this)
    ];

    return implode(' ', array_filter($command));
  }

  protected function standardizeDirectory($dir)
  {
    if (empty($dir)) {
      return $dir;
    }

    // make sure source has trailing /
    if (substr($dir, -1) !== '/') {
      $dir .= '/';
    }

    return $dir;
  }
}
