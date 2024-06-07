<?php

namespace App\Component\SecretKey;

class GenerateSecurityKey
{
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }
}