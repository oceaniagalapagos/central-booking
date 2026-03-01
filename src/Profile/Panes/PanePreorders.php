<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Profile\Forms\FormEditPreorder;
use CentralBooking\Profile\Forms\FormPreorder;
use CentralBooking\Profile\Tables\TablePreorder;

final class PanePreorders implements DisplayerInterface
{
    public function render()
    {
        $action = $_GET['action'] ?? 'list';
        $this->renderContent($action);
    }

    private function renderContent(string $action)
    {
        if ($action === 'create') {
            (new FormPreorder)->render();
        } elseif ($action === 'list') {
            (new TablePreorder)->render();
        } elseif ($action === 'edit') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            (new FormEditPreorder($id))->render();
        } else {
            $this->renderContentDefault();
        }
    }

    private function renderContentDefault()
    {
        ?>
        <p>No se encontró contenido.</p>
        <?php
    }
}
