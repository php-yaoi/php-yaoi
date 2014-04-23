<?php

class Sql_Exception extends Exception {
    const STATEMENT_REQUIRED = 1;
    const MISSING_COLUMNS = 2;
    const CLOSURE_MISTYPE = 3;
} 