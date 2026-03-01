<?php
namespace CentralBooking\REST;

final class RegisterRoute
{
    private function __construct()
    {
    }

    public const prefix = 'api_git/';

    public static function register(string $route, string $methods, callable $callback, array $args = [])
    {
        register_rest_route(
            RegisterRoute::prefix,
            $route,
            [
                'methods' => $methods,
                'callback' => $callback,
                'args' => $args,
                'permission_callback' => '__return_true'
            ]
        );
    }
}
