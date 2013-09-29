<?php

class Mock_Exception extends Exception {
    const ALREADY_PLAYING = 1;
    const ALREADY_RECORDING = 1;
    const CAPTURE_UNAVAILABLE = 3;
    const PLAYBACK_UNAVAILABLE = 4;
    const OUT_OF_BOUNDS = 5;
    const KEY_NOT_FOUND = 6;
}