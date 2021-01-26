<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

use \Firebase\JWT\JWT;

use app\Helper;
use app\Lib\R;

/**
 * Class Jwt Authentication
 */
class JWTAuth
{
    /**
     * This method create a valid token.
     *
     * @param	int		$id		The user id
     * @param	string	$user	Username
     * @return	string	JWT		Valid token.
     */
    public static function getToken($id, $user)
    {
        // config secret
        $secret = R::get('config')['app']['secret'];

        // date: now
        $now = Helper::timeNow(false, false, 'Y-m-d H:i:s');
        // date: now +12 hours
        $future = Helper::dateFuture('12 hours', 'Y-m-d H:i:s');

        $token = array(
            'header' => [ 			// User Information
                'id' 	=> 	$id, 	// User id
                'user' 	=> 	$user 	// username
            ],
            'payload' => [
                'iat'	=>	$now, 	// Start time of the token
                'exp'	=>	$future	// Time the token expires (+12 hours)
            ]
        );

        $result = null;
        try {
            // Encode Jwt Authentication Token
            $result = JWT::encode($token, $secret, "HS256");
        } catch (\Exception $ex) {
            // throw $ex;
        }

        return $result;
    }

    /**
     * This method get header from token.
     *
     * @param	string	$token	token.
     * @return	array
     */
    public static function getHeader($token)
    {
        $result = array();
        try {
            // config secret
            $secret = R::get('config')['app']['secret'];

            // Decode Jwt Authentication Token
            $obj = JWT::decode($token, $secret, array("HS256"));

            if (isset($obj->header)) {
                $result = (array) $obj->header;
            }
        } catch (\Exception $ex) {
            // throw $ex;
        }

        return $result;
    }

    /**
     * This method verify a token.
     *
     * @param	string	$token	token.
     * @return	boolean
     */
    public static function verifyToken($token)
    {
        try {
            // config secret
            $secret = R::get('config')['app']['secret'];

            // Decode Jwt Authentication Token
            $obj = JWT::decode($token, $secret, array("HS256"));

            // If payload is defined
            if (isset($obj->payload)) {
                // Gets the actual date
                $now = strtotime(Helper::timeNow(false, false, 'Y-m-d H:i:s'));
                // Gets the expiration date
                $exp = strtotime($obj->payload->exp);
                // If token didn't expire
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
