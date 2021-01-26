<?php
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
        // date: now +2 hours
        $future = Helper::dateFuture('2 hours', 'Y-m-d H:i:s');

        $token = array(
            'header' => [ 			// User Information
                'id' 	=> 	$id, 	// User id
                'user' 	=> 	$user 	// username
            ],
            'payload' => [
                'iat'	=>	$now, 	// Start time of the token
                'exp'	=>	$future	// Time the token expires (+2 hours)
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
