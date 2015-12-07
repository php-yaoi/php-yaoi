<?php

namespace Yaoi\Command;

interface Runner
{
    /**
     * @return $this
     */
    public function error($message);

    /**
     * @return $this
     */
    public function success($message);

    /**
     * @return $this
     */
    public function respond($message);
}