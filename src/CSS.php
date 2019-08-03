<?php
namespace SalernoLabs\Collapser;

/**
 * CSS Collapser class
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
class CSS extends Media
{
    /**
     * In rule or not
     * @var boolean
     */
    protected $inRule = false;

    /**
     * The start of a rule, colon character
     * @return bool
     */
    protected function handleCharacter58(): bool
    {
        $this->inRule = true;

        return true;
    }

    /**
     * Handle semi-colon character, close out the rule
     * @return bool
     */
    protected function handleCharacter59(): bool
    {
        if (!$this->inSingleQuotes && !$this->inQuotes)
        {
            $this->inRule = false;
        }

        return true;
    }

    /**
     * Handle } in case developer left off the semi-colon from the rule, close the rule out
     * @return bool
     */
    protected function handleCharacter125(): bool
    {
        return $this->handleCharacter59();
    }

    /**
     * Handle un-quoted spaces
     * @return bool
     */
    protected function handleCharacter32(): bool
    {
        if (!$this->inSingleQuotes && !$this->inQuotes)
        {
            return false;
        }

        return true;
    }
}
