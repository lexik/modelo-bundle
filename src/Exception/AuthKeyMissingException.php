<?php

namespace Choosit\ModeloBundle\Exception;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class AuthKeyMissingException extends InvalidConfigurationException
{
    public function __construct()
    {
        $message = 'authKey couldn\'t generated, you may have forgot to fill in the configuration file or setAuthKey with agencyCode and/or privateKey in your config file.';
        parent::__construct($message);
    }
}
