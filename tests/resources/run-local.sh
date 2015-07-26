#!/bin/bash

nohup node http-mirror.js &
nohup php -S 127.0.0.1:8000 http-server.php &
