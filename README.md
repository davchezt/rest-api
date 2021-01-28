# REST API

Simple REST API using [Flight Framework](https://github.com/mikecao/flight) + [PHP-JWT](https://github.com/firebase/php-jwt) + [Idiorm](https://github.com/j4mie/idiorm)

## Requirements
requires `PHP 5.3` or greater

## INSTALL
```bash
$ git clone https://github.com/davchezt/rest-api.git
$ cd rest-api
$ composer install
```

## CONFIG
config file: `config.php`

### App Config
```php
'app' => [
    'hash' => 'DaVchezt', // password hash
    'debug' => false, // show debug request
    'log' => true, // write log to file
    'secret' => 'U0hiqmizT7repIgy3wX1uJv6R3T8YtskNcZmF7ClH2ajBtE4nF8WXQGNAw5b3VVe'
]
```

### Database Config
```php
'db' => [
    'dsn' => "mysql:host=localhost;dbname=dbname",
    'dbu' => "dbuser",
    'dbp' => "dbpass"
]
```

### Mail SMTP Config
```php
'mail' => [
    'host' => 'mail.domain.com',
    'user' => 'davchezt@domain.com',
    'pass' => 'mailpass',
    'name' => 'Leonardo DaVchezt'
]
```

## ROUTING
Create new file and save to dir app/Router/

```php
<?php
// file: app/Router/Main.php

namespace app\Router;

use app\BaseRouter;

class Main extends BaseRouter
{
    public function init()
    {
        // call this with url http://localhost/rest-api/
        $this->app->route('/', function () {
            $this->app->json(['response' => ['data' => 'This is Main']]);
        });

        // this route is require authorize using JWT
        $this->app->route('GET|POST /test', function () {
            $this->app->json(['response' => ['data' => 'This is JWT test']]);
        }, false, true); // set last argument with true to enable
    }
}
?>
```

## CREATING MODEL
Create new file and save to dir app/Adapter/

```php
<?php
// file: app/Adapter/UserAdapter.php

namespace app\Adapter;

use app\ModelInterface;
use app\SQL;

class UserAdapter implements ModelInterface
{
    public function getById($id = 0) : array
    {
        return ['id' => 1, 'name' => 'admin'];
    }
    public function getAll() : array
    {
        return [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'admin2'],
            ['id' => 3, 'name' => 'admin3']
        ];
    }
    
    ...
}
?>
```
## USING MYSQL DATABASES ON MODEL
Example on __UserAdapter__ `app/Adapter/UserAdapter.php`
```php
public function getById($id = 0) : array
{
    $id = intval($this->id);

    return $this->orm()
                ->select_many('user.id', 'user.username ...')
                ->find_one($id);
}
```

## USE MODEL
Example on __Main__ router `app/Router/Main.php`
```php
public function init()
{
    // call this with url http://localhost/rest-api/
    $this->app->route('/', function () {
        // initialize model
        $user = new app\Adapter\UserAdapter();
        // assign model
        $this->app->model()->setAdapter($user);
        // call model
        $users = $this->app->model()->getAll();
        
        $response = [
            'users' => $users,
            'count' => count($users)
        ];

        // output
        $this->app->json(['response' => $response]);
    });
}
```