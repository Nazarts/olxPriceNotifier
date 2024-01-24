<?php

namespace Services;

class JobService
{
    static public function runCommand($command): void
    {
        exec($command);
    }
}