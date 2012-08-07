<?php

namespace Behat\DrushExtension\Context;

use Behat\Behat\Context\BehatContext;

/**
 * Raw Drush context for Behat BDD tool.
 * Provides raw Drush integration (without step definitions) and web assertions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DrushContext extends BehatContext implements DrushAwareInterface
{
    private $drushAlias;

    /**
     * Sets Drush alias.
     *
     * @param string $alias
     */
    public function setDrushAlias($alias)
    {
        $this->drushAlias = $alias;
    }

    /**
     * Returns Drush alias.
     *
     * @return string
     */
    public function getDrushAlias()
    {
        return $this->drushAlias;
    }
}