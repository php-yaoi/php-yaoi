<?php

namespace Yaoi\Io\Response;

use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Text;
use Yaoi\Io\Response;

class ArrayResponse extends Response
{
    public $result = array();

    public function error($message)
    {
        $this->result['error'] []= (string)$message;
    }

    public function success($message)
    {
        $this->result['success'] []= (string)$message;
    }

    public function addContent($message)
    {
        if ($message instanceof SubContent) {
           $this->addContent($message->content);
        }
        elseif ($message instanceof Text) {
            $this->result['content'][]= $message->value;
        }
        elseif ($message instanceof Rows) {
            $array = &$this->result['content'][];
            foreach ($message->getIterator() as $item) {
                $array []= $item;
            }
        }
        else {
            $this->result['content'] []= $message;
        }
    }
}