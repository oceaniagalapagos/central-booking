<?php
namespace CentralBooking\Data;

final class SecretKey
{
    private static ?SecretKey $instance = null;

    public static function getInstance(): SecretKey
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    private function _set(string $key): void
    {
        git_set_setting('secret_key', $key);
    }

    public function set(string $key): void
    {
        $hash = hash('sha256', $key);
        $this->_set($hash);
    }

    public function get(): string
    {
        $secret = git_get_setting('secret_key');
        if ($secret === null) {
            $secret = bin2hex(random_bytes(32));
            $secret = hash('sha256', $secret);
            $this->_set($secret);
        }
        return (string) $secret;
    }

    public function check(string $key): bool
    {
        return hash_equals($key, $this->get());
    }
}
