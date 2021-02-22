# pars-mvc

[![Build Status](https://travis-ci.com/pars/pars-mvc.svg?branch=master)](https://travis-ci.com/pars/pars-mvc)
[![Coverage Status](https://coveralls.io/repos/github/pars/pars-mvc/badge.svg?branch=master)](https://coveralls.io/github/pars/pars-mvc?branch=master)

This library provides MVC implementation for PARS Framework


## Installation

Run the following to install this library:

```bash
$ composer require pars/pars-mvc
```

In your routs add:

```php
$app->any(\Pars\Mvc\Handler\MvcHandler::getRoute(), \Pars\Mvc\Handler\MvcHandler::class, 'mvc');
```

Registering Controllers and Models:

Configuration example in your Application
```php
    'mvc' => [
        'error_controller' => 'index',
        'controllers' => [
            'index' => \Pars\Admin\Index\IndexController::class,
        ],
        'models' => [
            'index' => \Pars\Admin\Index\IndexModel::class,
        ],

    ],
```
This will register `IndexController` under the path `/index`.

Implementing controllers and models:
```php
class IndexController extends \Pars\Mvc\Controller\AbstractController {
    public function indexAction()
    {
    }
}
```
```php
class IndexModel extends \Pars\Mvc\Model\AbstractModel {
    
}
```

## Documentation

Browse the documentation online at https://docs.parsphp.org/pars-mvc/

## Support

* [Issues](https://github.com/pars/pars-mvc/issues/)
* [Forum](https://discourse.parsphp.org/)
