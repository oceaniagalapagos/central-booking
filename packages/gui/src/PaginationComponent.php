<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\AlignmentConstants;

class PaginationComponent extends BaseComponent
{
    private array $data = [
        'first' => '#',
        'prev' => '#',
        'next' => '#',
        'last' => '#',
        'total_elements' => 0,
        'current_page' => 0,
        'total_pages' => 0,
        'alignment' => 'right',
        'interactive' => false,
    ];

    public function __construct(bool $interactive = false, string $alignment = AlignmentConstants::RIGHT)
    {
        parent::__construct('div');
        $this->data['alignment'] = $alignment;
        $this->data['interactive'] = $interactive;
        $this->class_list->add('tablenav', "tablenav-alignment-{$this->data['alignment']}");
        wp_enqueue_style(
            'pagination-component-style',
            CENTRAL_BOOKING_URL . '/assets/css/components/pagination-component.css'
        );
    }
    
    public function setLinks(string $link_first, string $link_last, string $link_next, string $link_prev)
    {
        $this->data['first'] = $link_first;
        $this->data['last'] = $link_last;
        $this->data['next'] = $link_next;
        $this->data['prev'] = $link_prev;
    }

    public function setData(int $total_items, int $current_page, int $total_pages)
    {
        $this->data['total_elements'] = $total_items;
        $this->data['current_page'] = $current_page;
        $this->data['total_pages'] = $total_pages;
    }

    public function compact()
    {
        $html = parent::compact();
        ob_start();
        ?>
        <div class="tablenav-pages">
            <span class="display-number"><?= $this->data['total_elements']; ?> elementos</span>
            <a href="<?= $this->data['current_page'] <= 1 ? '#' : $this->data['first']; ?>" class="button link-first"
                <?= $this->data['current_page'] <= 1 ? 'disabled' : ''; ?>>«</a>
            <a href="<?= $this->data['current_page'] <= 1 ? '#' : $this->data['prev']; ?>" class="button link-prev"
                <?= $this->data['current_page'] <= 1 ? 'disabled' : ''; ?>>‹</a>
            <?php if ($this->data['interactive']): ?>
                <input type="text" class="display-number" value="<?= $this->data['current_page']; ?>" size="1">
                <span class="display-number">de <?= $this->data['total_pages']; ?></span>
            <?php else: ?>
                <span class="display-number"><?= $this->data['current_page']; ?> de <?= $this->data['total_pages']; ?></span>
            <?php endif; ?>
            <a href="<?= $this->data['current_page'] >= $this->data['total_pages'] ? '#' : $this->data['next']; ?>"
                class="button link-next" <?= $this->data['current_page'] >= $this->data['total_pages'] ? 'disabled' : ''; ?>>›</a>
            <a href="<?= $this->data['current_page'] >= $this->data['total_pages'] ? '#' : $this->data['last']; ?>"
                class="button link-last" <?= $this->data['current_page'] >= $this->data['total_pages'] ? 'disabled' : ''; ?>>»</a>
        </div>
        <?php
        $html .= ob_get_clean();
        $html .= "</{$this->tag}>";
        return $html;
    }
}
