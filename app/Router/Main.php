<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\BaseRouter;

class Main extends BaseRouter
{
    public function init()
    {
        $this->app->plugin()->trigger('before', [$this]); // Router_Main_init_before
        $this->app->route('/', [$this, 'mainHTML']);
        $this->app->route('/v1', [$this, 'mainJSON']);
        $this->app->route('/v1/uploads', [$this, 'uploads']);
        $this->app->plugin()->trigger('after', [$this]); // Router_Main_init_after
    }

    public function mainHTML()
    {
        $version = file_get_contents($this->app->request()->path() . '/vendor/mikecao/flight/VERSION');
        $content = ['version' => 'Flight Framework (' . $version . ')'];

        $this->app->plugin()->trigger('init', [$this, &$content]); // Router_Main_mainHTML_init

        $this->app->render('index', $content);
    }

    public function mainJSON()
    {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            // to get shared ISP IP address
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check for IPs passing through proxy servers
            // check if multiple IP addresses are set and take the first one
            $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipAddressList as $ip) {
                if (! empty($ip)) {
                    // if you prefer, you can check for valid IP address here
                    $ipAddress = $ip;
                    break;
                }
            }
        } else if (! empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else if (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (! empty($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (! empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        // $ip_data = json_decode(@file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ipAddress));
        $ip_data = json_decode(@file_get_contents("http://www.geoplugin.net/json.gp"));
        $response = [
            'data' => 'API version 1.0',
            'ip_data' => $ip_data
        ];
        $this->app->plugin()->trigger('init', [$this, &$response]); // Router_Main_mainJSON_init

        $this->app->json(['response' => $response]);
    }

    public function uploads()
    {
        $data = $this->app->request()->data();
        $files = $this->app->request()->files();

        if (isset($files) && isset($_FILES['images']['tmp_name']))
        {
            // $this->app->image()->saveImages('images', 'origin', true, false);
            // $this->app->image()->saveImages('images', 'min', true, true);
            // $this->app->image()->saveImages('images', 'max', true, true);
            
            $uploadedImages = $this->app->image()->saveImages('images', 'crop', true, true);
            if (!empty($uploadedImages) && is_array($uploadedImages))
            {
                foreach ($uploadedImages as $imagePath) {
                    $message = "Image saved: " . $imagePath;
                    $this->app->logger()->write($message, 'upload');
                }
            }
        }

        $response = [
            'data' => $data,
            'files' => $files
        ];
        $this->app->plugin()->trigger('init', [$this, &$response]); // Router_Main_upload_init
        $this->app->json(['response' => $response]);
    }
}