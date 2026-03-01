<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\ButtonActionConstants;
use CentralBooking\GUI\Constants\ButtonStyleConstants;

final class AdminModalComponent implements ComponentInterface, DisplayerInterface
{
    private string $id;
    private string $title;
    private ?ComponentInterface $content = null;

    public function __construct(string $title = '')
    {
        $this->title = $title;
        $this->id = 'modal-' . rand();
        wp_enqueue_style(
            'git-component-modal',
            CENTRAL_BOOKING_URL . '/assets/css/admin/modal.css',
            ['thickbox'],
            time()
        );
    }

    public function render()
    {
        echo $this->compact();
    }

    public function compact()
    {
        ob_start();
        ?>
        <div class="modal fade" id="<?= $this->id ?>" tabindex="-1" aria-labelledby="<?= $this->id ?>Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="<?= $this->id ?>Label"><?= $this->title ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_contents();
    }

    public function setContent(ComponentInterface $content)
    {
        $this->content = $content;
    }

    public function getButtonLaunch(string $text = 'Launch Modal')
    {
        $button_launch = new TextComponent($text);
        $button_launch->attributes->set('href', '#' . $this->id);
        $button_launch->attributes->set('data-bs-target', '#' . $this->id);
        return $button_launch;
    }
}
