<?php

class Mock_Exception extends Exception {
    const ALREADY_PLAYING = 1;
    const ALREADY_RECORDING = 1;
    const IMPORT_ALTER = 3;
    const NO_DATA = 4;
    const OUT_OF_BOUNDS = 5;
    const INVALID_KEY = 6;
}