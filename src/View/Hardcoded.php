<?php
namespace Yaoi\View;

use Yaoi\BaseClass;

abstract class Hardcoded extends BaseClass implements Renderer
{
    public function isEmpty()
    {
        return false;
    }

    public function __toString()
    {
        try {
            ob_start();
            $this->render();
            return ob_get_clean();
        }
        catch (\Exception $exception) {
            return 'Error: (' . $exception->getCode() . ') ' . $exception->getMessage();
        }
    }


}