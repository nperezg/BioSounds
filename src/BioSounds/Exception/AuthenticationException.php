<?php

namespace BioSounds\Exception;


class AuthenticationException extends \Exception
{
    const MESSAGE = 'Invalid username or password, please try again.';

    public function __construct()
    {
        parent::__construct($this::MESSAGE);
    }
}