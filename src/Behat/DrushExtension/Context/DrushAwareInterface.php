<?php

namespace Behat\DrushExtension\Context;

/**
 * Drush aware interface for contexts.
 */
interface DrushAwareInterface
{
    /**
     * Sets Drush alias.
     *
     * @param string $alias Drush alias
     */
    public function setDrushAlias(Mink $mink);
}