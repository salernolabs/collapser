<?php
/**
 * Javascript Collapser class
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
namespace SalernoLabs\Collapser;

class CSS extends Media
{
    /**
     * In rule or not
     *
     * @var boolean
     */
    protected $inRule = false;

    /**
     * The start of a rule, colon character
     *
     * @return boolean
     */
    protected function handleCharacter58()
    {
        $this->inRule = true;

        return true;
    }

    /**
     * Handle semi-colon character, close out the rule
     *
     * @return boolean
     */
    protected function handleCharacter59()
    {
        if (!$this->inSingleQuotes && !$this->inQuotes) {
            $this->inRule = false;
        }

        return true;
    }

    /**
     * Handle } in case developer left off the semi-colon from the rule, close the rule out
     *
     * @return boolean
     */
    protected function handleCharacter125()
    {
        return $this->handleCharacter59();
    }

    /**
     * Handle un-quoted spaces
     *
     * @return boolean
     */
    protected function handleCharacter32()
    {
        return true;
    }
}