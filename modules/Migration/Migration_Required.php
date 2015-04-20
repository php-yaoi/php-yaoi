<?php

interface Migration_Required {
    /**
     * @return Migration|Migration_Rollback
     */
    public function getMigration();
}