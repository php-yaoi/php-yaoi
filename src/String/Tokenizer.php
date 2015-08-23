<?php

namespace Yaoi\String;

use Yaoi\BaseClass;

/**
 * Class Tokenizer
 * @package Yaoi\String
 * @todo add heredoc quoting
 */
class Tokenizer extends BaseClass
{
    protected $quoteStrings = array();

    public function addQuote($start, $end, $escape = array())
    {
        $this->quoteStrings[$start] = array($start, $end, $escape);
        return $this;
    }

    protected $lineStoppers = array();

    public function addLineStopper($start)
    {
        $this->lineStoppers[$start] = $start;
        return $this;
    }

    public function tokenize($string)
    {
        $quoteStarted = false;
        $escape = array();
        $start = false;
        $end = false;
        $quotedString = false;
        $escapePosition = false;

        $result = array();

        $prevPosition = 0;

        for ($position = 0; $position < strlen($string); ++$position) {
            if (!$quoteStarted) {
                foreach ($this->quoteStrings as $quote) {
                    if (substr($string, $position, strlen($quote[0])) === $quote[0]) {
                        $start = $quote[0];
                        $result [] = substr($string, $prevPosition, $position - $prevPosition);
                        $position += strlen($start);
                        $prevPosition = $position;

                        $escapePosition = $position;
                        $quotedString = '';

                        $end = $quote[1];
                        $escape = $quote[2];

                        $quoteStarted = 1;
                        break;
                    }
                }


                if (!$quoteStarted) {
                    foreach ($this->lineStoppers as $lineStopper) {
                        if (substr($string, $position, strlen($lineStopper)) === $lineStopper) {
                            $result [] = substr($string, $prevPosition, $position - $prevPosition);

                            $position += strlen($lineStopper);
                            $newLine = strpos($string, "\n", $position);
                            if (false === $newLine) {
                                $result [] = array(substr($string, $position), $lineStopper);
                                $position = strlen($string);
                                $prevPosition = $position + 1;
                            } else {
                                $result [] = array(substr($string, $position, $newLine - $position), $lineStopper);
                                $prevPosition = $newLine;
                                $position = $newLine;
                            }
                        }
                    }
                }

            } else {
                $escapeFound = false;
                foreach ($escape as $escaped => $unescaped) {
                    if (substr($string, $position, strlen($escaped)) === $escaped) {
                        $escapeFound = true;
                        $quotedString .= substr($string, $escapePosition, $position - $escapePosition);
                        $quotedString .= $unescaped;

                        $position += strlen($escaped);
                        $escapePosition = $position;
                        break;
                    }
                }
                if ($escapeFound) {
                    continue;
                }

                if (substr($string, $position, strlen($end)) === $end) {
                    $escapedString = substr($string, $prevPosition, $position - $prevPosition);
                    $quotedString .= substr($string, $escapePosition, $position - $escapePosition);
                    $result [] = array($quotedString, $start, $escapedString);
                    $position += strlen($end);
                    $prevPosition = $position;
                    $quoteStarted = false;
                }
            }
        }
        if ($quoteStarted) {
            throw new Exception('Unterminated quoted expression', Exception::MALFORMED);
        }

        if ($prevPosition < $position - 1) {
            $result [] = substr($string, $prevPosition, $position - $prevPosition);
        }

        return $result;
    }


    public function stripQuotes($string, $replace = array()) {
        $tokens = $this->tokenize($string);
        $template = '';
        $binds = array();

        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $template .= $token;
            }
            else {
                $template .= '?';
                $binds []= $token;
            }
        }

        return array($template, $binds);
    }

}