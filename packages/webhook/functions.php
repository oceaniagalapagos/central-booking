<?php
/**
 * Webhook Functions for Central Booking Plugin
 * 
 * This file contains utility functions for managing webhooks within the Central Booking
 * system. These functions provide a simplified interface for creating, saving, and 
 * triggering webhooks.
 * 
 * @package CentralBooking\Webhook
 * @since 1.0.0
 * @author Central Booking Team
 */

use CentralBooking\Webhook\Webhook;
use CentralBooking\Webhook\WebhookManager;
use CentralBooking\Webhook\WebhookStatus;
use CentralBooking\Webhook\WebhookTopic;

/**
 * Creates a new Webhook instance from provided data.
 * 
 * This function initializes a Webhook object using the data provided
 * in the associative array. It maps the array keys to the corresponding
 * properties of the Webhook class.
 * 
 * @param array $data Associative array containing webhook data
 *                    - 'id' (int): The unique identifier of the webhook
 *                    - 'name' (string): The name of the webhook
 *                    - 'status' (string|WebhookStatus): The status slug or enum of the webhook
 *                    - 'topic' (string|WebhookTopic): The topic slug or enum of the webhook
 *                    - 'delivery_url' (string): The URL to which the webhook will send data
 * 
 * @return Webhook The initialized Webhook instance
 * 
 * @since 1.0.0
 * 
 * @example
 * $webhook_data = [
 *     'id' => 1,
 *     'name' => 'Booking Created Webhook',
 *     'status' => 'active',
 *     'topic' => 'booking_created',
 *     'delivery_url' => 'https://example.com/webhook-endpoint'
 * ];
 * $webhook = git_create_webhook($webhook_data);
 */
function git_webhook_create(array $data = [])
{
    $webhook = new Webhook;

    $webhook->id = (int) ($data['id'] ?? 0);
    $webhook->name = (string) ($data['name'] ?? '');

    if (isset($data['status'])) {
        if (is_string($data['status'])) {
            $webhook->status = WebhookStatus::fromSlug($data['status']) ?? WebhookStatus::DISABLED;
        } elseif ($data['status'] instanceof WebhookStatus) {
            $webhook->status = $data['status'];
        }
    }

    if (isset($data['topic'])) {
        if (is_string($data['topic'])) {
            $webhook->topic = WebhookTopic::fromSlug($data['topic']) ?? WebhookTopic::NONE;
        } elseif ($data['topic'] instanceof WebhookTopic) {
            $webhook->topic = $data['topic'];
        }
    }

    if (filter_var($data['delivery_url'] ?? '', FILTER_VALIDATE_URL)) {
        $webhook->url_delivery = $data['delivery_url'];
    }

    return $webhook;
}

/**
 * Saves a webhook to persistent storage.
 * 
 * This function delegates to the WebhookManager singleton to handle
 * the actual persistence of the webhook data. The webhook can be either
 * a new webhook (insert) or an existing webhook (update).
 * 
 * @param Webhook $webhook The webhook instance to save
 * 
 * @return mixed The result of the save operation (typically boolean success or ID)
 * 
 * @since 1.0.0
 * 
 * @throws Exception If the webhook data is invalid or save operation fails
 * 
 * @example
 * $webhook = git_create_webhook($webhook_data);
 * $result = git_save_webhook($webhook);
 * if ($result) {
 *     echo "Webhook saved successfully";
 * }
 */
function git_webhook_save(Webhook $webhook)
{
    return WebhookManager::getInstance()->save($webhook);
}

/**
 * Triggers all active webhooks for a specific topic.
 * 
 * This function finds all webhooks registered for the given topic and
 * sends the payload to their respective delivery URLs. Only active webhooks
 * will be triggered.
 * 
 * @param WebhookTopic $topic The topic/event that occurred (e.g., booking created, updated)
 * @param array $payload The data to send to the webhook endpoints
 * 
 * @return void
 * 
 * @since 1.0.0
 * 
 * @example
 * // Trigger webhooks when a new booking is created
 * $booking_data = [
 *     'id' => 123,
 *     'customer_name' => 'John Doe',
 *     'booking_date' => '2026-01-10',
 *     'status' => 'confirmed'
 * ];
 * git_trigger_webhook(WebhookTopic::BOOKING_CREATED, $booking_data);
 */
function git_webhook_trigger(WebhookTopic $topic, array $payload): void
{
    WebhookManager::getInstance()->trigger($topic, $payload);
}

function git_webhook_get_by_topic(WebhookTopic $topic): array
{
    return WebhookManager::getInstance()->getByTopic($topic);
}

function git_webhook_get_by_id(int $id): ?Webhook
{
    return WebhookManager::getInstance()->get($id);
}

function git_webhook_get_all(): array
{
    return WebhookManager::getInstance()->getAll();
}