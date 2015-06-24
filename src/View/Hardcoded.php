<?php
namespace Yaoi\View;

use Yaoi\BaseClass;

/**
 * Class View_Hardcoded
 * @method static Hardcoded create
 */
abstract class Hardcoded extends BaseClass implements Renderer
{
    public function isEmpty()
    {
        return false;
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


}