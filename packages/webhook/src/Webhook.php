<?php
namespace CentralBooking\Webhook;

class Webhook
{
    public int $id = 0;
    public string $name = '';
    public WebhookStatus $status = WebhookStatus::ACTIVE;
    public WebhookTopic $topic = WebhookTopic::NONE;
    public string $url_delivery = '';

    public function send(array $payload)
    {
        if ($this->status !== WebhookStatus::ACTIVE) {
            return false;
        }
        $args = [
            'method' => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body' => git_serialize($payload),
        ];
        $response = wp_remote_post($this->url_delivery, $args);
        return $response['response']['code'] >= 200 && $response['response']['code'] < 300;
    }

    public function save()
    {
        return WebhookManager::getInstance()->save($this) !== null;
    }
}