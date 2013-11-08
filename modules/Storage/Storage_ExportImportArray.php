<?php

interface Storage_ExportImportArray {
    /**
     * @return array
     */
    public function exportArray();

    public function importArray(array &$data);
}