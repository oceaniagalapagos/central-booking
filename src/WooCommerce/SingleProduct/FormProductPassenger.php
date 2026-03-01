<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputFloatingLabelComponent;
use CentralBooking\Implementation\GUI\PassengerCombine;

class FormProductPassenger implements DisplayerInterface
{
    public function __construct()
    {
    }

    public function render()
    {
        $id_prev_button = uniqid();
        $id_submit_button = uniqid();
        $id_passengers_form_container = uniqid();
        wp_enqueue_script(
            'pane-form-passenger',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/pane-form-passengers.js',
            ['jquery'],
        );
        wp_localize_script('pane-form-passenger', 'dataPassenger', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'hook' => 'git_passenger_form_html',
            'elements' => [
                'prevButton' => $id_prev_button,
                'submitButton' => $id_submit_button,
                'passengersFormContainer' => $id_passengers_form_container,
            ],
        ]);
        ?>
        <div id="git-form-product-passengers" style="display: none;">
            <template id="git_template_passenger_form">
                <?php $this->formPassenger(); ?>
            </template>
            <div id="<?= $id_passengers_form_container ?>"></div>
            <div class="mt-2">
                <button id="<?= $id_prev_button ?>" class="me-2 btn btn-secondary" type="button">Atrás</button>
                <button id="<?= $id_submit_button ?>" class="btn btn-primary" type="submit">Reservar</button>
            </div>
        </div>
        <?php
    }

    private function formPassenger()
    {
        $passenger_form = new PassengerCombine();
        $name_input = $passenger_form->getNameInput("passengers[{{INDEX}}][name]");
        $birthday_input = $passenger_form->getBirthdayInput("passengers[{{INDEX}}][birthday]");
        $data_document_input = $passenger_form->getDataDocumentInput("passengers[{{INDEX}}][data_document]");
        $nationality_select = $passenger_form->getNationalitySelect("passengers[{{INDEX}}][nationality]");
        $type_document_select = $passenger_form->getTypeDocumentSelect("passengers[{{INDEX}}][type_document]");
        $floating_name = new InputFloatingLabelComponent($name_input, 'Nombre');
        $floating_birthday = new InputFloatingLabelComponent($birthday_input, 'Fecha de Nacimiento');
        $floating_nationality = new InputFloatingLabelComponent($nationality_select, 'Nacionalidad');
        $floating_data_document = new InputFloatingLabelComponent($data_document_input, 'Número de Documento');
        $floating_type_document = new InputFloatingLabelComponent($type_document_select, 'Tipo de Documento');

        ?>
        <div class="form_passenger mb-4">
            <?php
            $floating_name->render();
            $floating_nationality->render();
            $floating_type_document->render();
            $floating_data_document->render();
            $floating_birthday->render();
            ?>
        </div>
        <?php
    }
}
