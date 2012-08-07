<?php

namespace Behat\DrushExtension\Context;

use Behat\DrushExtension\Drush;

/**
 * Drush aware interface for contexts.
 */
interface DrushAwareInterface
{
    /**
     * Sets Drush instance.
     *
     * @param Drush $drush Drush instance.
     */
    public function setDrush(Drush $drush);
}