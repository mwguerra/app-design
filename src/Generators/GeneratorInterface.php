<?php

namespace MWGuerra\AppDesign\Generators;

/**
 * Interface for all generator classes.
 */
interface GeneratorInterface
{
    /**
     * Generates output based on the implemented generator logic.
     *
     * @return array An associative array with 'log' and 'files' as keys.
     */
    public function generate(): array;
}
