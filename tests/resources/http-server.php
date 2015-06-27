<?php

if (empty($_COOKIE['coo'])) {
    $_COOKIE['coo'] = 0;
    setcookie('coo', 1);
    header("Location: /");
}
elseif (1 == $_COOKIE['coo']) {
    setcookie('coo', 2);
    header("Location: /");
}

echo $_COOKIE['coo'];

