<?php
namespace Yaoi\View;

use Yaoi\BaseClass;

/**
 * Class View_Raw
 * @method static Raw create($data)
 */
class Raw extends BaseClass implements Renderer
{
    public $data = '';

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function isEmpty()
    {
        return '' == $this->data;
    }

    public function render()
    {
        echo $this->data;
    }

    public function __toString()
    {
        try {
            return (string)$this->data;
        }
        catch (\Exception $exception) {
            return 'ERROR: (' . $exception->getCode() . ') ' . $exception->getMessage();
        }
    }

}