<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;

class Console extends BaseClass
{
    const RESET = 0;        // reset all attributes to their defaults
    const BOLD = 1;         // set bold
    //const HALF_BRIGHT = 2;  // set half-bright (simulated with color on a color display)
    //const UNDERSCORE = 4;   // set underscore (simulated with color on a color display) (the colors used to simulate dim or underline are set using ESC ] ...)
    //const BLINK = 5;        // set blink
    //const REVERSE = 7;      // set reverse video
    //const PRIMARY_FONT = 10; //      reset selected mapping, display control flag, and toggle meta flag (ECMA-48 says "primary font").
    //const ALT_FONT = 11;      // select null mapping, set display control flag, reset  toggle  meta flag (ECMA-48 says "first alternate font").
    //const SECOND_ALT_FONT = 12;      // select  null  mapping,  set  display control flag, set toggle meta flag (ECMA-48 says "second alternate font").  The toggle meta flag causes the high bit of a byte to be toggled before the mapping table translation is done.
    //const DOUBLY_UNDERLINED = 21;     // set normal intensity (ECMA-48 says "doubly underlined")
    //const NORMAL_INTENSITY = 22;      // set normal intensity
    //const UNDERLINE_OFF = 24;     // underline off
    //const BLINK_OFF = 25;      // blink off
    //const REVERSE_OFF = 27;     // reverse video off
    const FG_BLACK = 30;      // set black foreground
    const FG_RED = 31;      // set red foreground
    const FG_GREEN = 32;     // set green foreground
    const FG_BROWN = 33;      // set brown foreground
    const FG_BLUE = 34;      // set blue foreground
    const FG_MAGENTA = 35;    // set magenta foreground
    const FG_CYAN = 36;      // set cyan foreground
    const FG_WHITE = 37;      // set white foreground
    //const UNDERSCORE_ON = 38;      // set underscore on, set default foreground color
    //const UNDERSCORE_OFF = 39;      // set underscore off, set default foreground color
    const BG_BLACK = 40;      // set black background
    const BG_RED = 41;      // set red background
    const BG_GREEN = 42;      // set green background
    const BG_BROWN = 43;      // set brown background
    const BG_BLUE = 44;      // set blue background
    const BG_MAGENTA = 45;      // set magenta background
    const BG_CYAN = 46;      // set cyan background
    const BG_WHITE = 47;      // set white background
    const BG_DEFAULT = 49;     // set default background color

    private $mode = array(self::RESET);

    public function set($mode = self::RESET) {
        if (!($this->forceColors || $this->attached())) {
            return $this;
        }

        if (!is_array($mode)) {
            $mode = func_get_args();
        }
        if ($this->mode === $mode) {
            return $this;
        }
        $this->mode = $mode;
        echo "\x1B[" . implode(';', $mode) . 'm';
        return $this;
    }

    public function returnCaret() {
        echo "\r";
        return $this;
    }

    public function eol() {
        echo PHP_EOL;
        $this->lineStarted = false;
        return $this;
    }

    /**
     * @param string $statement
     * @param mixed ...$binds
     * @throws \Yaoi\String\Exception
     * @return static
     */
    public function printF($statement) {
        if ($this->padding && !$this->lineStarted) {
            echo $this->padding;
            $this->lineStarted = true;
        }
        echo $statement;

        return $this;
    }

    /**
     * @param string $statement
     * @throws \Yaoi\String\Exception
     * @return static
     */
    public function printLine($statement) {
        $this->printF($statement)->eol();
        return $this;
    }

    public function printLines($statement) {
        $lines = explode("\n", str_replace("\r\n", "\n", $statement));
        foreach ($lines as $line) {
            $this->printLine($line);
        }
        return $this;
    }

    private $lineStarted = false;
    private $padding = '';
    public function setPadding($padding = '   ') {
        $this->padding = $padding;
        return $this;
    }


    /**
     * @return static
     */
    public static function getInstance() {
        static $instance;
        if (null === $instance) {
            $instance = new static;
        }
        return $instance;
    }

    public $forceColors = false;

    public function attached()
    {
        if (function_exists('posix_isatty')) {
            return posix_isatty(STDOUT);
        } else {
            return true;
        }
    }

}