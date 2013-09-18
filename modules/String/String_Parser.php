<?php

class String_Parser extends Base_Class {
    private $string;

    private $position = 0;

    public function __construct($string = null) {
        $this->string = $string;
    }


    /**
     * @param null $start
     * @param null $end
     * @return bool|String_Parser
     */
    public function inner($start = null, $end = null) {
        if (is_null($this->string)) {
            return $this;
        }

        if (is_null($start)) {
            $startPosition = $this->position;
        }
        else {
            $startPosition = strpos($this->string, $start, $this->position);
            if (false === $startPosition) {
                return new self();
            }
            $startPosition += strlen($start);
        }

        if (is_null($end)) {
            $endPosition = strlen($this->string);
        }
        else {
            $endPosition = strpos($this->string, $end, $startPosition);
            if (false === $endPosition) {
                return new self();
            }
        }

        $this->position = $endPosition + strlen($end);
        return new self(substr($this->string, $startPosition, $endPosition - $startPosition));
    }

    public function __toString() {
        return (string)$this->string;
    }

}

//echo SimpleParser::create('<div class="active odd">hooy</div>')->inner(null, '"');