<?php
namespace CentralBooking\Preorder;

use CentralBooking\Admin\Form\FormService;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

class PreorderDashboard implements ComponentInterface
{
    public function compact()
    {
        $this->loadScripts();
        ob_start();
        $this->showMessage();
        if ($this->verify_preorder()) {
            (new PreorderForm)->render();
        } else {
            $this->not_preorder();
        }
        return ob_get_clean();
    }

    private function loadScripts()
    {
        wp_enqueue_style('bootstrap-icon', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css');
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
    }

    private function not_preorder()
    {
        ?>
        <form method="get" class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Buscar Preorden</h5>
                            <div class="input-group mb-3">
                                <input type="number" name="preorder" class="form-control" placeholder="Número de Preorden"
                                    required>
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }

    private function verify_preorder()
    {
        if (isset($_GET['preorder']) === false) {
            return false;
        }
        $id = (int) $_GET['preorder'];

        $ticket = git_ticket_by_id($id);

        return $ticket !== null;
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormService::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}
