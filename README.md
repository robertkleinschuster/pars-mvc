# pars-mvc

[![Build Status](https://travis-ci.com/pars-framework/pars-mvc.svg?branch=master)](https://travis-ci.com/pars-framework/pars-mvc)
[![Coverage Status](https://coveralls.io/repos/github/pars-framework/pars-mvc/badge.svg?branch=master)](https://coveralls.io/github/pars-framework/pars-mvc?branch=master)

This library provides MVC implementation for PARS Framework.

## Understanding:

Model: Is responsible for loading and saving data via bean finder and processor. Methods for loading additional data may also be added.

Controller: Initializes view with data provided by the model. Controller actions can also be nested.

View: Renders a template or builds html via the provided object oriented html builder.


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
    
    protected function initView(){
        $view = new MyView();
        $view->setLayout(new MyLayout());
        $this->setView($view);
        
    }
    
    protected function initModel(){
        $this->getModel()->initialize();
        $this->getModel()->initializeDependencies();
    }
      
    public function indexAction()
    {
        $this->getView()->set('heading', $this->getModel()->getHeading());
        $this->getView()->set('text', $this->getModel()->getText());
        // adding compontent to be rendered
        $this->getView()->pushComponent(new MyCompontent());
        // nesting additional controller action to be rendered e.g. UserController::indexAction
        // all compontents of the nested controller action will be appended to the parent controllers view
        // rendering templates is currently not supported for nested controller actions
        $this->pushAction('user', 'index');
    }
}
```

```php
class IndexModel extends \Pars\Mvc\Model\AbstractModel {
    public function getHeading(): string 
    {
        return 'Hello World';
    }
    
      public function getText(): string 
    {
        return 'Hello Hello Hello Hello';
    }
}
```


Layout and Component

```php
class MyLayout extends \Pars\Mvc\View\AbstractLayout {
    protected function initialize() {
      parent::initialize();
      $this->setTag('html');
      $head = new \Pars\Mvc\View\ViewElement('head');
      $this->initHead($head);
      $this->push($head);
      $body = new \Pars\Mvc\View\ViewElement('body');
      $this->initBody($body);
      $this->push($body);
    }
    protected function initHead(\Pars\Mvc\View\ViewElement $head) {
       $link = new \Pars\Mvc\View\ViewElement('link');
       $link->setAttribute('rel', 'stylesheet');
       $link->setAttribute('href', 'styles.css');
       $head->push($link);
    }
    
    protected function initBody(\Pars\Mvc\View\ViewElement $body) {
        $heading = new \Pars\Mvc\View\ViewElement('h1');
        $heading->setContent('{heading}');
        $body->push($heading);
    }
}
```

```php
class MyComponent extends \Pars\Mvc\View\AbstractComponent {
    
    protected function initialize() {
      parent::initialize();
      $text = new \Pars\Mvc\View\ViewElement('p');
      $text->setContent('{text}');
      $this->push($text);
    }
    
}
```

## Documentation

Browse the documentation online at https://docs.parsphp.org/pars-mvc/

## Support

* [Issues](https://github.com/pars/pars-mvc/issues/)
* [Forum](https://discourse.parsphp.org/)
