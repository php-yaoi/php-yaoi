<?php

namespace Yaoi\String\Tokenizer;


use Yaoi\String\Exception;

/**
 * Class Parser
 * @package Yaoi\String\Tokenizer
 * @todo heredoc support
 * @todo check single letter quote
 */
class Parser
{
    /** @var Quote[]|array */
    protected $quoteStrings = array();

    public function addQuote($start, $end, $escape = array())
    {
        $this->quoteStrings[$start] = new Quote($start, $end, $escape);
        return $this;
    }

    /** @var LineTerminator[]|array */
    protected $lineStoppers = array();

    public function addLineStopper($start)
    {
        $this->lineStoppers[$start] = new LineTerminator($start);
        return $this;
    }


    /** @var Bracket[]|array */
    protected $brackets = array();

    public function addBracket($start, $end)
    {
        $this->brackets [$start] = new Bracket($start, $end);
        return $this;
    }

    /**
     * @param $string
     * @return Parsed
     * @throws Exception
     */
    public function tokenize($string)
    {
        $result = $mainResult = new Parsed();

        /** @var Quote $quoteStarted */
        $quoteStarted = null;
        $currentQuote = null;
        $unescapedString = false;
        $escapePosition = false;

        $prevPosition = 0;

        for ($position = 0; $position < strlen($string); ++$position) {
            do {
                if ($quoteStarted) {
                    if ($quoteStarted->escape) {
                        foreach ($quoteStarted->escape as $escaped => $unescaped) {
                            if (substr($string, $position, strlen($escaped)) === $escaped) {
                                if (null !== $escapePosition) {
                                    $unescapedString .= substr($string, $escapePosition, $position - $escapePosition);
                                }
                                $unescapedString .= $unescaped;
                                $position += strlen($escaped) - 1;
                                $escapePosition = $position + 1;
                                break 2;
                            }
                        }
                    }

                    if (substr($string, $position, $quoteStarted->endLen) === $quoteStarted->end) {
                        $originalString = substr($string, $prevPosition, $position - $prevPosition);
                        $unescapedString .= substr($string, $escapePosition, $position - $escapePosition);
                        $result->tokens [] = new Token($unescapedString, $quoteStarted->start, $quoteStarted->end, $originalString);
                        $position += $quoteStarted->endLen - 1;
                        $prevPosition = $position + 1;
                        $quoteStarted = false;
                    }

                    break;
                }

                foreach ($this->quoteStrings as $quote) {
                    if (substr($string, $position, $quote->startLen) === $quote->start) {
                        if ($prevPosition != $position) {
                            $result->tokens [] = substr($string, $prevPosition, $position - $prevPosition);
                        }

                        $position += $quote->startLen - 1;
                        $prevPosition = $position + 1;

                        $escapePosition = $position + 1;
                        $unescapedString = '';

                        $quoteStarted = $quote;
                        break 2;
                    }
                }

                foreach ($this->lineStoppers as $lineStopper) {
                    if (substr($string, $position, $lineStopper->startLen) === $lineStopper->start) {
                        $result->tokens [] = substr($string, $prevPosition, $position - $prevPosition);

                        $position += $lineStopper->startLen - 1;
                        $newLine = strpos($string, "\n", $position);
                        if (false === $newLine) {
                            $result->tokens [] = new Token(substr($string, $position + 1), $lineStopper->start);
                            $position = strlen($string);
                            $prevPosition = $position + 1;
                        } else {
                            $result->tokens [] = new Token(
                                substr($string, $position + 1, $newLine - $position - 1),
                                $lineStopper->start);
                            $prevPosition = $newLine;
                            $position = $newLine;
                        }
                        break 2;
                    }
                }

                foreach ($this->brackets as $bracket) {
                    if (substr($string, $position, $bracket->startLen) === $bracket->start) {
                        $result->tokens[] = substr($string, $prevPosition, $position - $prevPosition);
                        $position += $bracket->startLen - 1;
                        $prevPosition = $position + 1;

                        $bracketParsed = new Parsed();
                        $bracketParsed->parent = $result;
                        $bracketParsed->bracket = $bracket;
                        $result->tokens[] = $bracketParsed;

                        $result = $bracketParsed;
                        break 2;
                    } elseif (substr($string, $position, $bracket->endLen) === $bracket->end) {
                        if (!$result->bracket || $result->bracket->end != $bracket->end) {
                            throw new Exception('Closing not opened bracket', Exception::MALFORMED);
                        }

                        if ($prevPosition < $position) {
                            $result->tokens [] = substr($string, $prevPosition, $position - $prevPosition);
                        }

                        $position += $bracket->endLen - 1;
                        $prevPosition = $position + 1;
                        $result = $result->parent;
                        break 2;
                    }
                }
            } while (false);
        }
        if ($quoteStarted) {
            throw new Exception('Unterminated quoted expression', Exception::MALFORMED);
        }

        if ($prevPosition < $position) {
            $result->tokens [] = substr($string, $prevPosition, $position - $prevPosition);
        }

        return $result;
    }

}