<?php

namespace Behat\DrushExtension;

use Symfony\Component\Process\Process;

class Drush {

  private $alias;

  public function __construct($alias) {
    $this->alias = $alias;
  }

  public function setAlias($alias) {
    $this->alias = $alias;
  }

  public function getAlias() {
    return $this->alias;
  }

  public function run($drush_command) {
    $command = sprintf("drush %s %s", $this->alias, $drush_command);
    $process = new Process($command);
    $process->setTimeout(30);
    $process->run();
    if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
    }

    return $process->getOutput();
  }
}

