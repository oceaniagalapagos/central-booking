<?php
namespace CentralBooking\Implementation\Temp;

use CentralBooking\GUI\DisplayerInterface;

final class MessageAlert implements DisplayerInterface
{
    private MessageTemporal $messageTemporal;

    public function __construct(private readonly string $keyAccess)
    {
        $this->messageTemporal = new MessageTemporal();
    }

    public function render()
    {
        $temporalMessage = $this->messageTemporal->readTemporalMessage($this->keyAccess);
        if ($temporalMessage === null) {
            return;
        }
        $noticeType = '';
        if ($temporalMessage['level'] === MessageLevel::ERROR) {
            $noticeType = 'notice-error';
        } elseif ($temporalMessage['level'] === MessageLevel::WARNING) {
            $noticeType = 'notice-warning';
        } elseif ($temporalMessage['level'] === MessageLevel::SUCCESS) {
            $noticeType = 'notice-success';
        } else {
            $noticeType = 'notice-info';
        }
        ?>
        <div class="notice is-dismissible <?= esc_attr($noticeType) ?>">
            <p><?= $temporalMessage['message'] ?></p>
        </div>
        <?php
    }

    public static function getInstance(string $keyAccess): MessageAlert
    {
        return new self($keyAccess);
    }
}
