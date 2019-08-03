<?php
namespace SalernoLabs\Collapser;

/**
 * Javascript Collapser class
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
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
     * @return boolean
     */
    protected function handleCharacter40(): bool
    {
        if (!$this->inSingleQuotes && !$this->inQuotes) {
            $this->inCondition = true;
        }

        return true;
    }

    /**
     * Closing of a condition, close parens
     * @return bool
     */
    protected function handleCharacter41(): bool
    {
        if (!$this->inSingleQuotes && !$this->inQuotes) {
            $this->inCondition = false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function handleCharacter47(): bool
    {
        if ($this->inQuotes || $this->inSingleQuotes || $this->inCondition) return true;

        return parent::handleCharacter47();
    }

    /**
     * Handle un-quoted spaces
     * @return bool
     */
    protected function handleCharacter32(): bool
    {
        $this->lastSpace = $this->currentIndex;

        if (!empty($this->keywordHashMap[$this->buildingWord]))
        {
            return true;
        }

        // Special case for for in
        if ((chr($this->nextCharacter) . $this->input[$this->currentIndex + 2]) === 'in') {
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
