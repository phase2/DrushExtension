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
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
    $loader->load('core.xml');

    if (isset($config['drush_alias'])) {
      $container->setParameter('behat.drush.drush_alias', $config['drush_alias']);
    }
    if (isset($config['config_file'])) {
      $container->setParameter('behat.drush.config_file', $config['config_file']);
    }
  }

}
