<?php
namespace SalernoLabs\Collapser;

/**
 * Generic Media Collapser
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
class Media
{
    /** @var int  */
    protected const CHAR_BACKSLASH = 92;
    /** @var int  */
    protected const CHAR_LINEFEED = 10;

    /**
     * Currently in quotes or not
     * @var boolean
     */
    protected $inQuotes = false;

    /**
     * Currently in single quotes or not
     * @var boolean
     */
    protected $inSingleQuotes = false;

    /**
     * Delete comments or not
     * @var boolean
     */
    protected $deleteComments = true;

    /**
     * Skip next character
     * @var boolean
     */
    protected $skipNext = 0;

    /**
     * Current character
     * @var integer
     */
    protected $currentCharacter = 0;

    /**
     * Last added character
     * @var integer
     */
    protected $lastAdded = 0;

    /**
     * Protected next character
     * @var integer
     */
    protected $nextCharacter = 0;

    /**
     * Protected last character
     * @var integer
     */
    protected $lastCharacter = 0;

    /**
     * Current index
     * @var integer
     */
    protected $currentIndex = 0;

    /**
     * Preserve new lines
     * @var boolean
     */
    protected $preserveNewlines = false;

    /**
     * Compression amount
     * @var integer
     */
    protected $sizeSavings = 0;

    /**
     * Debug mode
     * @var boolean
     */
    protected $debug = false;

    /**
     * User entered input
     * @var string
     */
    protected $input;

    /**
     * Last word parsed
     * @var string
     */
    protected $lastWord = '';

    /**
     * Building space for last word
     * @var string
     */
    protected $buildingWord = '';

    /**
     * Skip text to input into the stream
     * @var string
     */
    protected $addSkipText = '';

    /**
     * Should the collapser preserve new lines or not
     *
     * @param boolean $value
     * @return Media
     */
    public function setPreserveNewLines($value)
    {
        $this->preserveNewlines = $value;

        return $this;
    }

    /**
     * Should the collapser delete multi-line comments or not?
     *
     * @param boolean $value
     * @return Media
     */
    public function setDeleteComments($value)
    {
        $this->deleteComments = $value;

        return $this;
    }

    /**
     * Should the collapser include debug stats in the output
     *
     * @param boolean $value
     * @return Media
     */
    public function setDebugMode($value)
    {
        $this->debug = $value;

        return $this;
    }

    /**
     * Collapse (minify) media
     * @param string $input The input string to collapse
     * @return string
     * @throws \Exception When input is empty
     */
    public function collapse($input)
    {
        $this->inQuotes = false;
        $this->inSingleQuotes = false;
        $this->currentCharacter = false;
        $this->nextCharacter = false;
        $this->lastCharacter = false;
        $this->skipNext = 0;
        $this->lastAdded = 0;
        $this->buildingWord = $this->lastWord = '';

        $initialSize = mb_strlen($input);
        $timeStart = microtime(true);

        $this->input = str_replace(["\r", "\t"], ['', ' '], trim($input));
        unset($input);

        if (empty($this->input))
        {
            throw new \Exception("No data has been supplied to media collapser.");
        }

        $characterCount = mb_strlen($this->input);

        $output = '';

        for ($i = 0; $i < $characterCount; ++$i)
        {
            if (!empty($this->skipNext)) {
                $this->skipNext--;
                continue;
            }

            $this->currentIndex = $i;
            $character = mb_substr($this->input, $i, 1);
            $this->currentCharacter = ord($character);
            $this->nextCharacter =
                ($this->currentIndex != $characterCount) ? ord(mb_substr($this->input, $i + 1, 1)) : false;
            $this->lastCharacter = ($this->currentIndex !== 0) ? ord(mb_substr($this->input, $i - 1, 1)) : false;

            $methodName = 'handleCharacter' . $this->currentCharacter;

            if (method_exists($this, $methodName))
            {
                $return = $this->$methodName();
            }
            else
            {
                $return = $this->handleCharacter();
            }

            if ($return === false)
            {
                $this->lastWord = $this->buildingWord;
                $this->buildWord = '';
            }
            else if ($return === true)
            {
                if (!empty($this->addSkipText)) {
                    $output .= $this->addSkipText;
                    $this->lastAdded = ord($output[mb_strlen($output) - 1]);

                    $this->lastWord = $this->buildingWord;
                    $this->buildingWord = '';
                    $this->addSkipText = '';
                } else {
                    $output .= $character;
                    $this->lastAdded = $this->currentCharacter;

                    if (ctype_alpha($character)) {
                        $this->buildingWord .= $character;
                    } else {
                        $this->lastWord = $this->buildingWord;
                        $this->buildingWord = '';
                    }
                }
            }
        }

        $this->sizeSavings = ($initialSize - mb_strlen($output));

        if ($this->debug) {
            $output .= sprintf(
                PHP_EOL . '/* culled %s chars in %sms */',
                number_format($this->sizeSavings),
                number_format((microtime(true) - $timeStart) * 1000, 2)
            );
        }

        return $output;
    }

    /**
     * Handle comments
     * @return bool
     * @throws \Exception On messed up comments
     */
    protected function handleCharacter47(): bool
    {
        if ($this->inQuotes || $this->inSingleQuotes) return true;

        //We're in a // style comment, delete it regardless
        if ($this->nextCharacter === 47)
        {
            $nextNewLine = mb_strpos($this->input, "\n", $this->currentIndex);
            if ($nextNewLine === false)
            {
                //This means the comment goes to the end of the file
                $nextNewLine = mb_strlen($this->input);
            }

            $comment = mb_substr($this->input, $this->currentIndex, $nextNewLine - $this->currentIndex);

            $this->skipNext = mb_strlen($comment);
            return false;
        }
        // Character 42 is an asterisk *
        else if ($this->nextCharacter === 42)
        {
            $nextClosing = mb_strpos($this->input, '*/', $this->currentIndex);

            if ($nextClosing === false) {
                throw new \Exception("Unclosed comment in media near index " . $this->currentIndex);
            }

            $comment = mb_substr($this->input, $this->currentIndex, $nextClosing - $this->currentIndex + 2);

            $this->skipNext = mb_strlen($comment);

            if ($this->deleteComments === false) {
                $this->addSkipText = $comment;
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Handle unescaped double quotes
     *
     * @return bool
     */
    protected function handleCharacter34(): bool
    {
        //Unescaped double quote
        if (!$this->inSingleQuotes && $this->lastCharacter !== self::CHAR_BACKSLASH) {
            $this->inQuotes = !$this->inQuotes;
        }

        return true;
    }

    /**
     * Handle unescaped single quotes
     *
     * @return bool
     */
    protected function handleCharacter39(): bool
    {
        if (!$this->inQuotes && $this->lastCharacter !== self::CHAR_BACKSLASH) {
            $this->inSingleQuotes = !$this->inSingleQuotes;
        }

        return true;
    }

    /**
     * Handle un-quoted spaces
     *
     * @return bool
     */
    protected function handleCharacter32(): bool
    {
        if ($this->inQuotes) {
            return true;
        }

        if ($this->inSingleQuotes) {
            return true;
        }

        return false;
    }

    /**
     * Handle new lines
     * @return bool
     */
    protected function handleCharacter10(): bool
    {
        if (!$this->preserveNewlines) {
            return false;
        }

        //If the last added character was a new line, skip it. We still want to condense newlines.
        if ($this->lastAdded === self::CHAR_LINEFEED) {
            return false;
        }

        return true;
    }

    /**
     * Default handler for a character
     * @return bool
     */
    protected function handleCharacter(): bool
    {
        return true;
    }
}
