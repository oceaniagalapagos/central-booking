<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\ButtonActionConstants;
use CentralBooking\GUI\Constants\ButtonStyleConstants;

final class ModalComponent implements ComponentInterface, DisplayerInterface
{
    private string $id;
    private string $title;
    private array $body = [];
    private array $footer = [];

    public function __construct(string $title = '')
    {
        $this->title = $title;
        $this->id = 'modal-' . rand();
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
                        <?php
                        foreach ($this->body as $component)
                            echo $component->compact();
                        ?>
                    </div>
                    <div class="modal-footer">
                        <?php
                        foreach ($this->footer as $component)
                            echo $component->compact();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_contents();
    }

    public function set_title(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function set_body_component(ComponentInterface $component)
    {
        $this->body = [$component];
    }

    public function set_footer_component(ComponentInterface $component)
    {
        $this->footer = [$component];
    }

    public function add_body(ComponentInterface $component)
    {
        $this->body[] = $component;
        return $this;
    }

    public function add_footer(ComponentInterface $component)
    {
        $this->footer[] = $component;
        return $this;
    }

    public function create_button_launch(string|ComponentInterface $text = 'Launch Modal')
    {
        $button_launch = new ButtonComponent($text, ButtonActionConstants::BUTTON, ButtonStyleConstants::PRIMARY);
        $button_launch->attributes->set('data-bs-toggle', 'modal');
        $button_launch->attributes->set('data-bs-target', '#' . $this->id);
        return $button_launch;
    }

    public function create_button_dimiss(string $text = 'Close')
    {
        $button_dimiss = new ButtonComponent($text, ButtonActionConstants::BUTTON, ButtonStyleConstants::SECONDARY);
        $button_dimiss->attributes->set('data-bs-dismiss', 'modal');
        return $button_dimiss;
    }
}
