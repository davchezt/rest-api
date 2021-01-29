<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;
use \Firebase\JWT\JWT;

/**
 * Class Jwt Authentication
 */
class JWTAuth
{
    private static $app;

    public static function configure(Engine $app)
    {
        self::$app = $app;
    }

    /**
     * This method create a valid token.
     *
     * @param int $id The user id
     * @param string $user Username
     * @param string $period time period default 12 hours
     * @return string JWT Valid token.
     */
    public static function getToken($id, $user, $period = '12 hours')
    {
        // config secret
        $secret = self::$app->get('flight.config')['app']['secret'];

        // date now
        $iat = self::$app->helper()->timeNow(false, false, 'Y-m-d H:i:s');
        // date now + period
        $exp = self::$app->helper()->dateFuture($period, 'Y-m-d H:i:s');

        $token = array(
            'header' => [ 			// store information
                'id' 	=> 	$id, 	// user id
                'user' 	=> 	$user 	// username
            ],
            'payload' => [
                'iat'	=>	$iat, 	// start time
                'exp'	=>	$exp	// ttoken expires
            ]
        );

        $result = null;
        try {
            // encode token
            $result = JWT::encode($token, $secret, 'HS256');
        } catch (\Exception $ex) {
            // throw $ex;
        }

        return $result;
    }

    /**
     * This method get header from token.
     *
     * @param string $token token.
     * @return array
     */
    public static function getHeader($token)
    {
        $result = array();
        $obj = self::verifyToken($token);

        if ($obj && isset($obj->header)) {
            $result = (array) $obj->header;
        }
        
        return $result;
    }

    /**
     * This method verify a token.
     *
     * @param string $token token.
     * @return boolean
     */
    public static function verifyToken($token)
    {
        try {
            // config secret
            $secret = self::$app->get('flight.config')['app']['secret'];

            // decode token
            $obj = JWT::decode($token, $secret, array('HS256'));

            // check if payload is defined
            if (isset($obj->payload)) {
                // actual date
                $now = strtotime(self::$app->helper()->timeNow(false, false, 'Y-m-d H:i:s'));
                // expiration date
                $exp = strtotime($obj->payload->exp);
                
                // chech expiration
                if (($exp - $now) > 0) {
                    return $obj;
                }
            }
        } catch (\Exception $ex) {
            // throw $ex;
        }

        return false;
    }
}
