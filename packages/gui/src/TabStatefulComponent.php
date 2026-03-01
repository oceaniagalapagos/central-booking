<?php
namespace CentralBooking\GUI;

class TabStatefulComponent implements DisplayerInterface
{
    private array $panes = [];
    private string $tabQuery = 'tab';
    private $defaultContent = null;

    public function __construct()
    {
        $this->loadScripts();
    }

    private function loadScripts()
    {
        wp_enqueue_style('git-tab-component-style', CENTRAL_BOOKING_URL . 'packages/gui/assets/css/tab-component.css');
    }

    public function addPane(string $title, ComponentInterface|DisplayerInterface $content)
    {
        $slug = sanitize_title($title);
        $this->panes[] = [
            'slug' => $slug,
            'title' => $title,
            'content' => $content
        ];
    }

    public function setDefaultPane(ComponentInterface|DisplayerInterface $defaultContent)
    {
        $this->defaultContent = $defaultContent;
    }

    public function render()
    {
        ?>
        <div class="git-tab-container">
            <div class="git-nav-tabs">
                <?php foreach ($this->panes as $index => $pane): ?>
                    <a href="<?= add_query_arg($this->tabQuery, $pane['slug']) ?>"
                        class="git-tab-button <?= $this->tabIsActive($index, $pane['slug']) ? 'active' : '' ?>">
                        <?php echo htmlspecialchars($pane['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="git-tab-content">
                <?php
                $contentRendered = false;
                foreach ($this->panes as $index => $pane) {
                    if ($this->tabIsActive($index, $pane['slug'])) {
                        $content = $pane['content'];
                        if ($content instanceof DisplayerInterface) {
                            $content->render();
                        } elseif ($content instanceof ComponentInterface) {
                            echo $content->compact();
                        }
                        $contentRendered = true;
                        break;
                    }
                }
                if ($contentRendered === false && count($this->panes) > 0) {
                    if ($this->defaultContent === null) {
                        echo '<p>No se encontró contenido para esta pestaña.</p>';
                    } else {
                        if ($this->defaultContent instanceof DisplayerInterface) {
                            $this->defaultContent->render();
                        } elseif ($this->defaultContent instanceof ComponentInterface) {
                            echo $this->defaultContent->compact();
                        }
                    }
                }
                ?>
            </div>
        </div>
        <style>
            a.git-tab-button {
                text-decoration: none;
            }
        </style>
        <?php
    }

    public function setTabQuery(string $tabQuery)
    {
        $this->tabQuery = $tabQuery;
    }

    private function tabIsActive($index, $tab)
    {
        $currentTab = $this->getCurrentTab();
        if ($currentTab === null && $index === 0) {
            return true;
        }
        return $currentTab === $tab;
    }

    private function getCurrentTab()
    {
        return $_GET[$this->tabQuery] ?? null;
    }
}
