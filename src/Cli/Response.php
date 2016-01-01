<?php

namespace Yaoi\Cli;

use Yaoi\Cli\View\Table;
use \Yaoi\Cli\View\Text as ViewText;
use Yaoi\Io\Content\Error;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Success;
use Yaoi\Io\Content\Text;


class Response extends \Yaoi\Io\Response
{
    protected $console;
    public function __construct() {
        $this->console = new Console();
    }


    /**
     * @return Console
     */
    public function console() {
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

    /**
     * @param mixed $message
     * @return $this
     */
    public function addContent($message)
    {
        if ($message instanceof SubContent) {
            $this->console->setPadding('   ');
            $this->addContent($message->content);
            $this->console->setPadding('');
            return $this;
        }

        if ($message instanceof Rows) {
            $message = (string)Table::create($message->getIterator())->setShowHeader();
        }
        elseif ($message instanceof Text) {
            $message = new ViewText($message);
        }

        $this->console->printLines($message);
        return $this;
    }

}