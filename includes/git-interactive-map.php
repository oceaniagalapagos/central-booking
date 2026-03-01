<?php
define('CENTRAL_BOOKING_URL', $_GET['git_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galápagos - Mapa Interactivo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="<?= CENTRAL_BOOKING_URL . '/assets/css/interactive-map.css' ?>" rel="stylesheet">
</head>

<body>
    <div class="p-5">
        <div class="w-80 bg-white p-0">
            <header class="p-2">
                <p class="text-center fs-4 m-0">Mapa Interactivo de Conexión Interislas - Ferry</p>
            </header>
            <div class="w-100" id="map"></div>
            <div class="p-4">
                <h3>
                    ¿Cómo reservar tickets entre islas <span class="text-success">(círculo verde)</span>?
                </h3>
                <hr>
                <ol>
                    <li>
                        <span class="text-primary fw-medium">Click en la primera isla:</span>
                        Aparece el bote y línea roja a Pto. Ayora (sin ticket).
                    </li>
                    <li>
                        <span class="text-primary fw-medium">Click en la segunda isla:</span>
                        Aparece el bote en Pto. Ayora y el ticket.
                    </li>
                    <li>
                        <span class="text-primary fw-medium">Click en el ticket:</span>
                        !Buen viaje¡
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        const CENTRAL_TICKETS_URL = "<?= CENTRAL_BOOKING_URL ?>";
    </script>
    <script src="<?= CENTRAL_BOOKING_URL . 'assets/js/interactive-map.js' ?>"></script>
</body>

</html>