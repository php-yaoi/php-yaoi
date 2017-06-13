<?php

namespace Yaoi\Cli;

use Yaoi\Cli\View\Table;
use Yaoi\Cli\View\Text as ViewText;
use Yaoi\Io\Content\Error;
use Yaoi\Io\Content\Progress;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Success;
use Yaoi\Io\Content\Text;


class Response extends \Yaoi\Io\Response
{
    protected $console;

    public function __construct()
    {
        $this->console = new Console();
    }


    /**
     * @return Console
     */
    public function console()
    {
        return $this->console;
    }

    public function error($message)
    {
        $this->console->printLines(
            new ViewText(
                new Error(
                    (string)$message
                )
            )
        );
        return $this;
    }

    public function success($message)
    {
        $this->console->printLines(
            new ViewText(
                new Success(
                    (string)$message
                )
            )
        );
        return $this;
    }


    private $progressStartedLength = 0;

    /**
     * @param mixed $message
     * @return $this
     */
    public function addContent($message)
    {
        if ($message instanceof Progress) {
            if ($this->console->attached()) {
                if ($this->progressStartedLength) {
                    $this->console->returnCaret();
                }

                $text = $message->prefix . round(100 * $message->done / $message->total) . '%, '
                    . $message->done . '/' . $message->total . ' ' . $message->text;
                if (strlen($text) < $this->progressStartedLength) {
                    $text = str_pad($text, $this->progressStartedLength, ' ');
                } else {
                    $this->progressStartedLength = strlen($text);
                }

                $this->console->printF($text);
            }
            return $this;
        }

        if ($this->progressStartedLength) {
            $text = str_pad(' ', $this->progressStartedLength, ' ');
            $this->console->returnCaret();
            $this->console->printF($text);
            $this->console->returnCaret();
            $this->progressStartedLength = 0;
        }

        if ($message instanceof SubContent) {
            $this->console->setPadding('   ');
            $this->addContent($message->content);
            $this->console->setPadding('');
            return $this;
        }

        if ($message instanceof Rows) {
            $message = (string)Table::create($message->getIterator())->setShowHeader();
        } elseif ($message instanceof Text) {
            $message = new ViewText($message);
        }

        $this->console->printLines($message);
        return $this;
    }

    public function __destruct()
    {
        if ($this->progressStartedLength) {
            $this->console->eol();
        }
    }
}