<?php

namespace Behat\DrushExtension\Context;

use Behat\Behat\Context\BehatContext;

use Behat\Mink\Mink,
    Behat\Mink\WebAssert;

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
    public function setDrushAlias(string $alias)
    {
        $this->drushAlias = $drushAlias;
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