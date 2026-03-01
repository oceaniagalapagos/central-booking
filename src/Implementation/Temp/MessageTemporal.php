<?php
namespace CentralBooking\Implementation\Temp;

final class MessageTemporal
{
    public function writeTemporalMessage(
        string $message,
        string $access_key,
        MessageLevel $level = MessageLevel::INFO,
        int $expiration_seconds = 60
    ) {
        if (empty($access_key) || $expiration_seconds <= 0) {
            return;
        }

        $service = git_temporal_service();

        $data = [
            'expiration' => time() + $expiration_seconds,
            'message' => $message,
            'level' => $level->name
        ];

        $service->write(
            "temporal_message_{$access_key}",
            $data,
            $expiration_seconds
        );
    }

    public function readTemporalMessage(string $access_key)
    {
        $service = git_temporal_service();

        $result = $service->read("temporal_message_{$access_key}", true);

        if ($result === null) {
            return null;
        }

        if (is_array($result)) {

            $level = MessageLevel::fromString($result['level'] ?? 'INFO');
            $message = (string) ($result['message'] ?? '');

            return [
                'message' => $message,
                'level' => $level
            ];
        }

        return null;
    }
}
