<?php

namespace Yaoi\Storage\Contract;
interface ExportImportArray
{
    /**
     * @return array
     */
    public function &exportArray();

    public function importArray(array &$data);
}