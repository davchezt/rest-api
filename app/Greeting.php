<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

use flight\Engine;

class Greeting
{
    private $app;
    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello()
    {
        if ($this->app) {
            $this->app->json(["response" => "Hello, {$this->name}!"], 201);
        }
    }
}