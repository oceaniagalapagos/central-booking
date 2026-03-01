<?php
namespace CentralBooking\Implementation\Document;

use CentralBooking\Data\Date;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;
use CentralBooking\PDF\DocumentInterface;
use CentralBooking\PDF\DocumentOrientation;
use CentralBooking\PDF\DocumentPageSize;

final class DocumentTrip implements DocumentInterface
{
    private Route $route;
    private Transport $transport;
    private Date $date_trip;

    public function __construct(
        Route $route,
        Transport $transport,
        Date $date_trip
    ) {
        $this->route = $route;
        $this->transport = $transport;
        $this->date_trip = $date_trip;
    }

    public function getPageSize(): DocumentPageSize
    {
        return DocumentPageSize::A4;
    }

    public function getOrientation(): DocumentOrientation
    {
        return DocumentOrientation::LANDSCAPE;
    }

    public function getHeaderHtml(): string
    {
        ob_start();
        ?>
        <title>ListaEmbarque-<?= $this->transport->code . '-' . date('YmdHis') ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');

            table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #000;
                font-family: 'Roboto', sans-serif;
                font-size: 11px;
            }

            thead {
                background-color: #d5e4e9ff;
            }

            td,
            th {
                padding: 3px 5px;
                border: 1px solid #000;
            }

            th {
                text-align: center;
            }
        </style>
        <?php
        return ob_get_clean() ?? '';
    }

    public function getBodyHtml(): string
    {
        $passengers = git_passengers(
            [
                'date_trip' => $this->date_trip->format('Y-m-d'),
                'id_route' => $this->route->id,
                'id_transport' => $this->transport->id,
            ]
        );
        ob_start();
        ?>
        <table>
            <thead>
                <tr>
                    <th rowspan="3" style="background-color: white;">
                        <img src="<?= CENTRAL_BOOKING_URL . '/assets/img/shield-army.jpg' ?>" alt="Escudo de armas"
                            width="75px">
                    </th>
                    <th colspan="6">I. INFORMACIÓN DEL VIAJE</th>
                    <th colspan="4"> II. INFORMACIÓN DE LA NAVE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fecha:</td>
                    <td><?= git_date_format($this->date_trip->format()) ?></td>
                    <td>Puerto de zarpe:</td>
                    <td><?= $this->route->getOrigin()->name ?></td>
                    <td>Hora estimada de zarpe:</td>
                    <td><?= $this->route->getDepartureTime()->pretty() ?></td>
                    <td>Nombre:</td>
                    <td><?= $this->transport->nicename; ?></td>
                    <td>Ap. tripulantes:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Actividad:</td>
                    <td><?= $this->transport->type->label() ?></td>
                    <td>Puerto de arribo:</td>
                    <td><?= $this->route->getDestiny()->name ?></td>
                    <td>Hora estimada de arribo:</td>
                    <td><?= $this->route->getArrivalTime()->pretty() ?></td>
                    <td>Matrícula:</td>
                    <td><?= $this->transport->code; ?></td>
                    <td>Cap. pasajeros:</td>
                    <td><?= $this->transport->getCapacity() ?></td>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th colspan="4">III. INFORMACIÓN DEL ARMADOR</th>
                    <th colspan="4">IV. INFORMACIÓN RESPONSABLE DEL EMBARQUE</th>
                    <th colspan="4">V. INFORMACIÓN TRIPULANTES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nombre:</td>
                    <td colspan="3"></td>
                    <td>Nombre:</td>
                    <td colspan="3"></td>
                    <td><?= $this->get_crew_member(0)['role'] ?></td>
                    <td><?= $this->get_crew_member(0)['name'] ?></td>
                    <td>Licencia:</td>
                    <td><?= $this->get_crew_member(0)['license'] ?></td>
                </tr>
                <tr>
                    <td>RUC:</td>
                    <td></td>
                    <td>Teléfono:</td>
                    <td></td>
                    <td>Cédula:</td>
                    <td></td>
                    <td>Teléfono:</td>
                    <td></td>
                    <td><?= $this->get_crew_member(1)['role'] ?></td>
                    <td><?= $this->get_crew_member(1)['name'] ?></td>
                    <td>Licencia:</td>
                    <td><?= $this->get_crew_member(1)['license'] ?></td>
                </tr>
                <tr>
                    <td>Correo:</td>
                    <td colspan="3"></td>
                    <td>Correo:</td>
                    <td colspan="3"></td>
                    <td><?= $this->get_crew_member(2)['role'] ?></td>
                    <td><?= $this->get_crew_member(2)['name'] ?></td>
                    <td>Licencia:</td>
                    <td><?= $this->get_crew_member(2)['license'] ?></td>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th colspan="2">CHECK</th>
                    <th colspan="18">VI. REGISTRO DE PASAJEROS</th>
                </tr>
                <tr>
                    <td>Sí</td>
                    <td>No</td>
                    <td>Nro.</td>
                    <td colspan="4">Nombres y Apellidos</td>
                    <td colspan="3">Cédula/Pasaporte</td>
                    <td colspan="3">Nacionalidad</td>
                    <td style="width: 25px;">Res.</td>
                    <td style="width: 25px;">Tra.</td>
                    <td style="width: 25px;">Tur.</td>
                    <td colspan="2">Telf. Emerg.</td>
                    <td colspan="2">Referencia</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 1;
                foreach ($passengers as $passenger):
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><?= $index ?></td>
                        <td colspan="4"><?= $passenger->name ?></td>
                        <td colspan="3"><?= $passenger->dataDocument ?></td>
                        <td colspan="3"><?= $passenger->nationality ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="2"><?= $passenger->getTicket()->getOrder()->get_billing_phone() ?></td>
                        <td colspan="2"><?= $passenger->getTicket()->getCoupon()?->post_title ?></td>
                    </tr>
                    <?php
                    $index++;
                endforeach;
                ?>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th colspan="2">VII. RESPONSABLE DEL REGISTRO DE PASAJEROS</th>
                    <th colspan="2">VIII. CAPITANÍA DEL PUERTO (Zarpe)</th>
                    <th colspan="2">IX. GAD MUNICIPAL (Recepción)</th>
                    <th colspan="2">X. CAPITANÍA DEL PUERTO (Control)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 50px;">Nombre:</td>
                    <td></td>
                    <td style="width: 50px;">Nombre:</td>
                    <td></td>
                    <td style="width: 50px;">Nombre:</td>
                    <td></td>
                    <td style="width: 50px;">Nombre:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Cargo:</td>
                    <td></td>
                    <td>Cédula:</td>
                    <td></td>
                    <td>Cédula:</td>
                    <td></td>
                    <td>Cédula:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Cédula:</td>
                    <td></td>
                    <td>Fecha:</td>
                    <td></td>
                    <td>Fecha:</td>
                    <td></td>
                    <td>Fecha:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Teléfono:</td>
                    <td></td>
                    <td>Observación:</td>
                    <td></td>
                    <td>Observación:</td>
                    <td></td>
                    <td>Observación:</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="8">
                        Declaración de responsabilidad: El Armador asume toda responsabilidad legal sobre los actos relacionados
                        con la operación de la embarcación, incluido el registro de pasajeros. Asimismo, como persona
                        responsable del registro de pasaje-ros DECLARO que la información detallada en el presente formulario es
                        verídica en su totalidad, asimismo, conozco que puede estar sujeto al análisis que en derecho
                        corresponda y que es de mi entera responsabilidad cualquier tipo de falsificación, destrucción,
                        adulteración, modificación u omisión en la infor-mación proporcionada a las Autoridades competentes.
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        return ob_get_clean() ?? '';
    }

    private function plus_time(string $time_one, string $time_two)
    {
        $time_one = strtotime($time_one);
        $time_two = strtotime($time_two);
        if ($time_one === false || $time_two === false) {
            return '0:00:00';
        }
        $total_seconds = $time_one + $time_two;
        return date('H:i:s', $total_seconds);
    }

    private function get_crew_member(int $index)
    {
        $crew = $this->transport->getCrew();
        return $crew[$index] ?? [
            'role' => '',
            'name' => '',
            'contact' => '',
            'license' => '',
        ];
    }
}
