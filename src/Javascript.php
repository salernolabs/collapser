<?php
/**
 * Javascript Collapser class
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
namespace SalernoLabs\Collapser;

class Javascript extends Media
{
    /**
     * In condition or not
     *
     * @var boolean
     */
    protected $inCondition = false;

    /**
     * Last space character
     *
     * @var integer
     */
    protected $lastSpace = 0;

    /**
     * Keyword hash maps
     *
     * @var array
     */
    private $keywordHashMap = [
        'var' => 1,
        'else' => 1,
        'function' => 1,
        'return' => 1,
        'in' => 1,
        'case' => 1,
        'typeof' => 1,
        'new' => 1,
        'instanceof' => 1,
        'throw' => 1,
        'delete' => 1
    ];

    /**
     * The start of a condition, open parens
     *
     * @return boolean
     */
    protected function handleCharacter40()
    {
        if (!$this->inSingleQuotes && !$this->inQuotes) {
            $this->inCondition = true;
        }

        return true;
    }

    /**
     * Closing of a condition, close parens
     *
     * @return boolean
     */
    protected function handleCharacter41()
    {
        if (!$this->inSingleQuotes && !$this->inQuotes) {
            $this->inCondition = false;
        }

        return true;
    }

    /**
     * Handle javascript matches
     *
     * @see \Chorizo\Utilities\Collapser\Media::handleCharacter47()
     */
    protected function handleCharacter47()
    {
        if ($this->inQuotes || $this->inSingleQuotes || $this->inCondition) return true;

        return parent::handleCharacter47();
    }

    /**
     * Handle un-quoted spaces
     *
     * @return boolean
     */
    protected function handleCharacter32()
    {
        $this->lastSpace = $this->currentIndex;

        if (!empty($this->keywordHashMap[$this->buildingWord]))
        {
            return true;
        }

        if ((chr($this->nextCharacter) . $this->inputs[$this->currentIndex + 2]) == 'in') {
            return true;
        }

        if ($this->inCondition) {
            return true;
        }

        if ($this->inQuotes) {
            return true;
        }

        if ($this->inSingleQuotes) {
            return true;
        }

        return false;
    }
}