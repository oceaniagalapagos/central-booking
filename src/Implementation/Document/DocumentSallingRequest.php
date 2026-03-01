<?php
namespace CentralBooking\Implementation\Document;

use CentralBooking\Data\Date;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;
use CentralBooking\PDF\DocumentInterface;
use CentralBooking\PDF\DocumentOrientation;
use CentralBooking\PDF\DocumentPageSize;

final class DocumentSallingRequest implements DocumentInterface
{
    public function __construct(
        private readonly Route $route,
        private readonly Transport $transport,
        private readonly Date $date_trip
    ) {
    }

    public function getPageSize(): DocumentPageSize
    {
        return DocumentPageSize::A4;
    }

    public function getOrientation(): DocumentOrientation
    {
        return DocumentOrientation::PORTRAIT;
    }

    public function getHeaderHtml(): string
    {
        ob_start();
        ?>
        <title>SolicitudZarpe-<?= $this->transport->code . '-' . date('YmdHis') ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');

            * {
                font-family: 'Roboto', sans-serif;
                font-size: 12px;
            }

            header {
                text-align: center;
            }

            h1 {
                font-size: 18px;
            }

            header p,
            header h1 {
                margin: 0;
            }

            .table-bordered {
                width: 100%;
                border-collapse: collapse;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000;
                padding: 3px 5px;
                text-align: left;
            }

            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 5px;
                vertical-align: middle;
                background-color: white;
            }

            .checkbox-label {
                display: inline-block;
                vertical-align: middle;
                margin-right: 15px;
            }

            .checkbox-container {
                margin: 10px 0;
            }
        </style>
        <?php
        return ob_get_clean() ?? '';
    }

    public function getBodyHtml(): string
    {
        ob_start();
        ?>
        <header>
            <img src="<?= CENTRAL_BOOKING_URL . '/assets/img/shield-ecuador.png' ?>" alt="Escudo del Ecuador" width="100"
                style="margin-bottom: 20px;">
            <p>REPÚBLICA DEL ECUADOR</p>
            <p>CAPITANÍA DEL PUERTO DE <?= $this->route->getOrigin()->name ?></p>
            <h1>SOLICITUD DE ZARPE Y ROL DE TRIPULACIÓN</h1>
            <p>Tráfico de Cabotaje</p>
            <p>(Para naves de 10 T.R.B. en adelante)</p>
        </header>
        <main>
            <div class="checkbox-container" style="text-align: right;">
                <div class="checkbox-label">
                    <span class="checkbox"></span> Carga
                </div>
                <div class="checkbox-label">
                    <span class="checkbox"></span> Lastre
                </div>
            </div>
            <p>Señor Capitán del puerto <?= $this->route->getOrigin()->name ?> cúmpleme informar a usted que el:</p>
            <table style="width: 100%; margin: 20px 0;">
                <tbody>
                    <tr>
                        <td><b>Buque:</b></td>
                        <td><?= $this->transport->nicename ?></td>
                        <td><b>Matrícula:</b></td>
                        <td><?= $this->transport->code ?></td>
                    </tr>
                    <tr>
                        <td><b>Armador:</b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td><b>Compañía:</b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td><b>Zarpa del puerto de:</b></td>
                        <td><?= $this->route->getOrigin()->name ?></td>
                        <td><b>Con destino a:</b></td>
                        <td><?= $this->route->getDestiny()->name ?></td>
                    </tr>
                    <tr>
                        <td><b>Fecha y hora de despacho:</b></td>
                        <td><?= git_datetime_format(date('Y-m-d H:i:s')) ?></td>
                        <td><b>Fecha y hora de zarpe:</b></td>
                        <td><?= $this->date_trip->pretty() . ' ' . $this->route->getDepartureTime()->pretty() ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><b>Fecha y hora de estimada de arribo:</b></td>
                        <td><?= $this->date_trip->pretty() . ' ' . $this->route->getDepartureTime()->pretty() ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Titulo</th>
                        <th>Plaza</th>
                        <th>Nombre</th>
                        <th>NAC</th>
                        <th>Matrícula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $index = 1;
                    foreach ($this->transport->getCrew() as $member):
                        ?>
                        <tr>
                            <td><?= $index ?></td>
                            <td><?= $member['role'] ?></td>
                            <td><?= $member['role'] ?></td>
                            <td><?= $member['name'] ?></td>
                            <td>Ecuador</td>
                            <td><?= $member['license'] ?></td>
                        </tr>
                        <?php
                        $index++;
                    endforeach;
                    ?>
                </tbody>
            </table>
            <p style="text-align: center;">Certifico que la información aqui contenida es exacta, veraz y completa.</p>
        </main>
        <footer style="margin-top: 50px;">
            <p style="text-align: center;">
                <span style="border-top: 1px solid #000; display: inline-block; padding: 5px;">
                    EL CAPITÁN DEL PUERTO
                </span>
            </p>
            <p>Fecha de emision: <?= git_datetime_format(date('Y-m-d H:i:s')) ?></p>
        </footer>
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
}
