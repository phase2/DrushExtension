<?php

namespace Behat\DrushExtension;

use Symfony\Component\Process\Process;

class Drush {

  private $alias;
  private $config_file;

  public function __construct($alias, $config_file = NULL) {
    $this->alias = $alias;
    if (!empty($config_file)) {
      $this->config_file = $config_file;
    }
  }

  public function setAlias($alias) {
    $this->alias = $alias;
  }

  public function getAlias() {
    return $this->alias;
  }

  public function setConfigFile($config_file) {
    $this->config_file = $config_file;
  }

  public function getConfigFile() {
    return $this->config_file;
  }

  public function run($drush_command) {
    $options = '';
    if (!empty($this->config_file)) {
      $options .= sprintf('--config="%s"', $this->config_file);
    }

    $command = sprintf("drush %s %s %s", $options, $this->alias, $drush_command);
    $process = new Process($command);
    $process->setTimeout(30);
    $process->run();
    if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
    }

    return $process->getOutput();
  }
}

