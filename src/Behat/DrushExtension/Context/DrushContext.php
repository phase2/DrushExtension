<?php

namespace Behat\DrushExtension\Context;

use Behat\DrushExtension\Drush;

use Behat\Behat\Context\BehatContext;

/**
 * Raw Drush context for Behat BDD tool.
 * Provides raw Drush integration (without step definitions) and web assertions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DrushContext extends BehatContext implements DrushAwareInterface
{
    private $drush;

    /**
     * Sets Drush instance.
     *
     * @param string $drush
     */
    public function setDrush(Drush $drush)
    {
        $this->drush = $drush;
    }

    /**
     * Returns Drush instance.
     *
     * @return string
     */
    public function getDrush()
    {
        return $this->drush;
    }
}
