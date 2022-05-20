[![Latest Stable Version](http://poser.pugx.org/lexik/modelo-bundle/v)](https://packagist.org/packages/lexik/modelo-bundle)

ModeloBundle
============

This Symfony bundle provides a service that performs and handle http requests to modelo API

[Modelo](https://www.modelo.fr/)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require choosit/modelo-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require choosit/modelo-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Choosit\ModeloBundle\ModeloBundle::class => ['all' => true],
];
```

Configuration
============

It can be configured as follows:

 ```yaml
#config/packages/choosit_modelo.yml

choosit_modelo:
  modelo_base_uri: '%env(MODELO_BASE_URL)%' # Base url of modelo API 
  auth:
    modelo_agency_code: 'my agency code' # Agency code can be find on your modelo account
    modelo_private_key: 'my private key' # Private key can be find on your modelo account
```

ModeloHttpClient service
============

This bundle provides the Choosit\ModeloBundle\Service\ModeloHttpClientInterface service that can be injected anywhere.

```php

use Choosit\ModeloBundle\Service\ModeloClientInterface;
class MyService
{
    public function __construct(ModeloClientInterface $modeloHttpClient) 
    {
    
    }
}

```
