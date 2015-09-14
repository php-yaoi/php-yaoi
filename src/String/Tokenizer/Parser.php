<?php

namespace Yaoi\String\Tokenizer;


use Yaoi\String\Exception;

class Parser
{
    /** @var Quote[]|array  */
    protected $quoteStrings = array();

    public function addQuote($start, $end, $escape = array())
    {
        $this->quoteStrings[$start] = new Quote($start, $end, $escape);
        return $this;
    }

    /** @var LineTerminator[]|array  */
    protected $lineStoppers = array();

    public function addLineStopper($start)
    {
        $this->lineStoppers[$start] = new LineTerminator($start);
        return $this;
    }


    /** @var Bracket[]|array  */
    protected $brackets = array();
    public function addBracket($start, $end) {
        $this->brackets [$start]= new Bracket($start, $end);
        return $this;
    }

    /**
     * @param $string
     * @return Parsed
     * @throws Exception
     */
    public function tokenize($string)
    {
        $result = new Parsed();

        /** @var Quote $quoteStarted */
        $quoteStarted = null;
        $currentQuote = null;
        $quotedString = false;
        $escapePosition = false;

        $prevPosition = 0;

        for ($position = 0; $position < strlen($string); ++$position) {
            if (!$quoteStarted) {
                foreach ($this->quoteStrings as $quote) {
                    if (substr($string, $position, $quote->startLen) === $quote->start) {
                        $result->tokens []= substr($string, $prevPosition, $position - $prevPosition);
                        $position += $quote->startLen;
                        $prevPosition = $position;

                        $escapePosition = $position;
                        $quotedString = '';

                        $quoteStarted = $quote;
                        break;
                    }
                }

                if (!$quoteStarted) {
                    foreach ($this->lineStoppers as $lineStopper) {
                        if (substr($string, $position, $lineStopper->startLen) === $lineStopper->start) {
                            $result->tokens []= substr($string, $prevPosition, $position - $prevPosition);

                            $position += $lineStopper->startLen;
                            $newLine = strpos($string, "\n", $position);
                            if (false === $newLine) {
                                $result->tokens []= new Token(substr($string, $position), $lineStopper->start);
                                $position = strlen($string);
                                $prevPosition = $position + 1;
                            } else {
                                $result->tokens []= new Token(
                                    substr($string, $position, $newLine - $position),
                                    $lineStopper->start);
                                $prevPosition = $newLine;
                                $position = $newLine;
                            }
                        }
                    }
                }
            }
            else {
                if ($quoteStarted->escape) {
                    $escapeFound = false;
                    foreach ($quoteStarted->escape as $escaped => $unescaped) {
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
                }

                if (substr($string, $position, $quoteStarted->endLen) === $quoteStarted->end) {
                    $escapedString = substr($string, $prevPosition, $position - $prevPosition);
                    $quotedString .= substr($string, $escapePosition, $position - $escapePosition);
                    $result->tokens []= new Token($quotedString, $quoteStarted->start, $quoteStarted->end, $escapedString);
                    $position += $quoteStarted->endLen;
                    $prevPosition = $position;
                    $quoteStarted = false;
                }
            }
        }
        if ($quoteStarted) {
            throw new Exception('Unterminated quoted expression', Exception::MALFORMED);
        }

        if ($prevPosition < $position - 1) {
            $result->tokens []= substr($string, $prevPosition, $position - $prevPosition);
        }

        return $result;
    }

}