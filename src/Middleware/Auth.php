<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Mix\Vega\Context;

/**
 * Class Auth
 * @package App\Middleware
 */
class Auth
{

    /**
     * @return \Closure
     */
    public static function middleware(): \Closure
    {
        return function (Context $ctx) {

            $ctx->next();
        };
    }

}
