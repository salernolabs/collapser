<?php
/**
 * Generic Media Collapser
 *
 * @author Eric
 * @package SalernoLabs
 * @subpackage Collapser
 */
namespace SalernoLabs\Collapser;

class Media
{
    /**
     * Currently in quotes or not
     *
     * @var boolean
     */
    protected $inQuotes = false;

    /**
     * Currently in single quotes or not
     *
     * @var boolean
     */
    protected $inSingleQuotes = false;

    /**
     * Delete comments or not
     *
     * @var boolean
     */
    protected $deleteComments = false;

    /**
     * Skip next character
     *
     * @var boolean
     */
    protected $skipNext = 0;

    /**
     * Current character
     *
     * @var integer
     */
    protected $currentCharacter = 0;

    /**
     * Last added character
     *
     * @var integer
     */
    protected $lastAdded = 0;

    /**
     * Protected next character
     *
     * @var integer
     */
    protected $nextCharacter = 0;

    /**
     * Protected last character
     *
     * @var integer
     */
    protected $lastCharacter = 0;

    /**
     * Current index
     *
     * @var integer
     */
    protected $currentIndex = 0;

    /**
     * Preserve new lines
     *
     * @var unknown
     */
    protected $preserveNewlines = false;

    /**
     * Compression amount
     *
     * @var integer
     */
    protected $sizeSavings = 0;

    /**
     * Debug mode
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * User entered input
     *
     * @var string
     */
    protected $input;

    /**
     * Last word parsed
     *
     * @var string
     */
    protected $lastWord = '';

    /**
     * Building space for last word
     *
     * @var string
     */
    protected $buildingWord = '';

    /**
     * Static wrapper for minifying media
     *
     * @param string $input
     * @param boolean $deleteComments
     * @param boolean $preserveNewlines
     * @param boolean $debugMode
     *
     * @return string
     */
    public static function minifyMedia($input, $deleteComments = true, $preserveNewlines = false, $debugMode = false)
    {
        try {
            $class = get_called_class();

            $collapser = new $class();

            $collapser
                ->setPreserveNewLines($preserveNewlines)
                ->setDeleteComments($deleteComments)
                ->setDebugMode($debugMode);

            return $collapser->collapseMedia($input);
        } catch (\Chorizo\Exceptions\Exception $exception) {
            static::getSystemStatic()->log->exception("Failed to collapse media properly.", $exception);
        }

        return false;
    }

    /**
     * Should the collapser preserve new lines or not
     *
     * @param boolean $value
     * @return \Chorizo\Utilities\Collapser\Media
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
     * @return \Chorizo\Utilities\Collapser\Media
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
     * @return \Chorizo\Utilities\Collapser\Media
     */
    public function setDebugMode($value)
    {
        $this->debug = $value;

        return $this;
    }

    /**
     * Collapse (minify) media
     *
     * @param string $input
     *
     * @return string
     */
    public function collapseMedia($input)
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

        $this->inputs = str_replace(array("\r", "\t"), array('', ' '), $input);
        unset($input);

        if (empty($this->inputs)) {
            throw new \Chorizo\Exceptions\Exception("No data has been supplied to media collapser.");
        }

        $characterCount = mb_strlen($this->inputs);

        $output = '';

        for ($i = 0; $i < $characterCount; ++$i) {
            if (!empty($this->skipNext)) {
                $this->skipNext--;
                continue;
            }

            $this->currentIndex = $i;
            $character = $this->inputs[$i];
            $this->currentCharacter = ord($character);
            $this->nextCharacter = !empty($this->inputs[$i + 1]) ? ord($this->inputs[$i + 1]) : false;
            $this->lastCharacter = !empty($this->inputs[$i - 1]) ? ord($this->inputs[$i - 1]) : false;

            $return = false;

            $methodName = 'handleCharacter' . $this->currentCharacter;

            if (method_exists($this, $methodName)) {
                $return = $this->$methodName();
            } else {
                $return = $this->handleCharacter();
            }

            if ($return === false) {
                $this->lastWord = $this->buildingWord;
                $this->buildWord = '';
                continue;
            } else if ($return === true) {
                $output .= $character;
                $this->lastAdded = $this->currentCharacter;

                if (ctype_alpha($character)) {
                    $this->buildingWord .= $character;
                } else {
                    $this->lastWord = $this->buildingWord;
                    $this->buildingWord = '';
                }
            } else if (is_string($return)) {
                $output .= $return;
                $this->lastAdded = ord($output[mb_strlen($output) - 1]);

                $this->lastWord = $this->buildingWord;
                $this->buildingWord = '';
            }
        }

        $this->sizeSavings = ($initialSize - mb_strlen($output));

        if ($this->debug) {
            $output .= "\n" . '/* culled ' . number_format($this->sizeSavings) . ' chars in ' . number_format((microtime(true) - $timeStart) * 1000, 2) . 'ms */';
        }

        return $output;
    }

    /**
     * Handle comments
     *
     * @return boolean
     */
    protected function handleCharacter47()
    {
        if ($this->inQuotes || $this->inSingleQuotes) return true;

        //We're in a // style comment, delete it regardless
        if ($this->nextCharacter == 47) {
            $nextNewLine = mb_strpos($this->inputs, "\n", $this->currentIndex);

            $comment = mb_substr($this->inputs, $this->currentIndex, $nextNewLine - $this->currentIndex);

            $this->skipNext = mb_strlen($comment);
            return false;
        } else if ($this->nextCharacter == 42) {
            $nextClosing = mb_strpos($this->inputs, "*/", $this->currentIndex);

            if ($nextClosing === false) {
                throw new \Chorizo\Exceptions\Exception("Unclosed comment in media near index " . $this->currentIndex);
            }

            $comment = mb_substr($this->inputs, $this->currentIndex, $nextClosing);

            $this->skipNext = mb_strlen($comment);

            if (!$this->deleteComments) {
                return $comment;
            }

            return false;
        }

        return true;
    }

    /**
     * Handle unescaped double quotes
     *
     * @return boolean
     */
    protected function handleCharacter34()
    {
        //Unescaped double quote
        if (!$this->inSingleQuotes && $this->lastCharacter != 92) {
            $this->inQuotes = !$this->inQuotes;
        }

        return true;
    }

    /**
     * Handle unescaped single quotes
     *
     * @return boolean
     */
    protected function handleCharacter39()
    {
        if (!$this->inQuotes && $this->lastCharacter != 92) {
            $this->inSingleQuotes = !$this->inSingleQuotes;
        }

        return true;
    }

    /**
     * Handle un-quoted spaces
     *
     * @return boolean
     */
    protected function handleCharacter32()
    {
        if (!$this->inQuotes) {
            return false;
        }

        if (!$this->inSingleQuotes) {
            return false;
        }

        return true;
    }

    /**
     * Handle new lines
     *
     * @return boolean
     */
    protected function handleCharacter10()
    {
        if (!$this->preserveNewlines) {
            return false;
        }

        //If the last added character was a new line, skip it. We still want to condense newlines.
        if ($this->lastAdded == 10) return false;

        return true;
    }

    /**
     * Default handler for a character
     *
     * @return boolean
     */
    protected function handleCharacter()
    {
        return true;
    }
}