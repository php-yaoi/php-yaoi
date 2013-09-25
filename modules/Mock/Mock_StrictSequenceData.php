<?php
/**
 * Class Mock_StrictSequenceData
 *
 * Capture and play strictly sequenced protocol scenarios.
 */
class Mock_StrictSequenceData implements Mock_DataSet {
    private $data = array();
    private $imported = false;


    public function add($key, $value)
    {
        if ($this->imported) {
            throw new Mock_Exception('Strict sequence mock data set can not be altered (adding ' . $key . ')',
                Mock_Exception::IMPORT_ALTER);
        }
        $this->data []= array($key, $value);
    }

    public function get($key = null)
    {
        if (!$this->imported) {
            throw new Mock_Exception('No data imported', Mock_Exception::NO_DATA);
        }

        if ($item = each($this->data)) {
            if ((null !== $key) && $item['key'] !== $key) {
                throw new Mock_Exception('Invalid key, "' . $key . '" received, "' . $item['key'] . '" required',
                    Mock_Exception::INVALID_KEY);
            }
            return $item['value'];
        }
        else {
            throw new Mock_Exception('Out of bounds', Mock_Exception::OUT_OF_BOUNDS);
        }
    }

    public function export()
    {
        return $this->data;
    }

    public function import($data)
    {
        $this->data = $data;
    }

    public function rewind() {
        reset($this->data);
    }


}