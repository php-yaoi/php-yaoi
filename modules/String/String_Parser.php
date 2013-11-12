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
            $startPosition = strpos($this->string, (string)$start, $this->position);
            if (false === $startPosition) {
                return new static();
            }
            $startPosition += strlen($start);
        }

        if (is_null($end)) {
            $endPosition = strlen($this->string);
        }
        else {
            $endPosition = strpos($this->string, (string)$end, $startPosition);
            if (false === $endPosition) {
                return new static();
            }
        }

        $this->position = $endPosition + strlen($end);
        return new static(substr($this->string, $startPosition, $endPosition - $startPosition));
    }

    /**
     * @return $this
     */
    public function resetPosition() {
        $this->position = 0;
        return $this;
    }

    public function __toString() {
        return (string)$this->string;
    }

}

//echo SimpleParser::create('<div class="active odd">hooy</div>')->inner(null, '"');