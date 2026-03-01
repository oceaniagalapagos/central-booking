<?php
namespace CentralBooking\GUI;

class TabInteractiveComponent implements DisplayerInterface
{
    private array $panes = [];

    public function __construct()
    {
        $this->loadScripts();
    }

    private function loadScripts()
    {
        wp_enqueue_style('git-tab-component-style', CENTRAL_BOOKING_URL . 'packages/gui/assets/css/tab-component.css');
        wp_enqueue_script('git-tab-component-script', CENTRAL_BOOKING_URL . 'packages/gui/assets/js/tab-component.js', ['jquery'], null, true);
    }

    public function addPane(string $title, ComponentInterface|DisplayerInterface $content)
    {
        $this->panes[] = ['title' => $title, 'content' => $content];
    }

    public function render()
    {
        $uniqueId = uniqid('git-tab-');
        ?>
        <div class="git-tab-container">
            <div class="git-nav-tabs">
                <?php foreach ($this->panes as $index => $pane): ?>
                    <button class="git-tab-button <?= $index === 0 ? 'active' : '' ?>" data-tab="<?= $uniqueId . $index ?>">
                        <?php echo htmlspecialchars($pane['title']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="git-tab-content">
                <?php foreach ($this->panes as $index => $pane): ?>
                    <div id="<?= $uniqueId . $index ?>" class="git-tab-panel <?= $index === 0 ? 'active' : '' ?>">
                        <?php
                        if ($pane['content'] instanceof DisplayerInterface)
                            $pane['content']->render();
                        elseif ($pane['content'] instanceof ComponentInterface)
                            echo $pane['content']->compact();
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
