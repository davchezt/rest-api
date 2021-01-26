<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
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
            $response = ["data" => "Hello, {$this->name}!"];
            $this->app->json(["response" => $response], 201);
        }
    }
}