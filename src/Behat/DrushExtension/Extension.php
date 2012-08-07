<?php

namespace Behat\DrushExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Behat\Behat\Extension\Extension as BaseExtension;

use Behat\DrupalExtension\Context\DrupalContext;

/**
 * Drush extension for Behat class.
 */
class Extension extends BaseExtension {

  /**
   * Loads a specific configuration.
   *
   * @param array $config Extension configuration hash (from behat.yml)
   * @param ContainerBuilder $container ContainerBuilder instance
   */
  public function load(array $config, ContainerBuilder $container) {
    $alias = empty($config['drush_alias']) ? '@self' : $config['drush_alias'];
    $container->setParameter('behat.drush_extension.drush_alias', $alias);
  }

}
