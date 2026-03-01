<?php
namespace CentralBooking\Admin;

use CentralBooking\GUI\DisplayerInterface;

class TestPage implements DisplayerInterface
{
    public function render()
    {
        ?>
        <div class="wrap">
            <h1>Test Panel</h1>
            <p>Ambiente de desarrollo y testing.</p>
        </div>
        <?php
    }
}
