<?php

namespace PhpRsync;

use Psr\Log\LoggerInterface;

class Rsync
{
  protected array $defaultOptions = [
    'archive' => true,
    'compress' => true,
    'cwd' => null,
    'delete' => false,
    'dryrun' => false,
    'exclude' => null,
    'include' => null,
    'relative' => false,
  ];

  public function __construct(protected Connection $connection, protected LoggerInterface|null $logger = null)
  {

  }

  /**
   * @param string $sourceDirectory      Source directory
   * @param string $destinationDirectory Destination relative to Connection::destinationRootDir
   * @param array $options               Options (to override default options)
   * @param $returnCommand               Unless testing, keep false
   * @return int|string
   * @throws \ErrorException
   */
  public function run(string $sourceDirectory, string $destinationDirectory, array $options = [], $returnCommand = false)
  {
    $options = $this->mergeOptionsWithDefaults($options);
    $command = $this->compileCommand($sourceDirectory, $destinationDirectory, $options);

    if ($returnCommand) {
      return $command;
    }

    exec($command, $output, $resultCode);

    if ($options['dryrun'] === true && $this->logger) {
      $this->logger->info('RSYNC dry run', [
        'command' => $command,
        'output' => $output,
      ]);
    }

    if ($resultCode > 0) {
      throw new \ErrorException('RSYNC failed. ' . implode('\n', $output));
    }

    return $resultCode;
  }

  public function compileCommand(string $sourceDirectory, string $destinationDirectory, array $options = [])
  {
    $command = [];

    if ($options['cwd']) {
      $command[] = "cd {$options['cwd']} &&";
    }

    $command[] =  'rsync';

    if ($ssh = $this->connection->getSSHKey()) {
      $command[] = $ssh;
    }

    if ($options['dryrun']) {
      $command[] =  '--dry-run --verbose';
    }

    if ($options['archive']) {
      $command[] =  '--archive';
    }

    if ($options['delete']) {
      $command[] =  '--delete';
    }

    if ($options['include']) {
      $include = $this->sanitizeIncludeExclude('include', (array) $options['include']);
      array_push($command, ...$include);
    }

    if ($options['exclude']) {
      $include = $this->sanitizeIncludeExclude('exclude', (array) $options['exclude']);
      array_push($command, ...$include);
    }

    $command[] = $this->standardizeDirectory($sourceDirectory);
    $command[] = $this->connection->getDestination($this->standardizeDirectory($destinationDirectory));
    $command[] = '2>&1'; // redirect to STDOUT

    return implode(' ', array_filter($command));
  }

  protected function mergeOptionsWithDefaults($options)
  {
    return array_merge($this->defaultOptions, $options);
  }

  protected function sanitizeIncludeExclude($which, $array)
  {
    return array_map(function ($a) use ($which) {
      $sanitized = $a !== '*' ? addcslashes($a, '"[]*?') : '*';
      return "--{$which}=\"{$sanitized}\"";
    }, $array);
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
