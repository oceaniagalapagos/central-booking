<?php

use CentralBooking\Admin\Setting\SettingsKeys;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Date;

function git_date_trip_field(string $name = 'date_trip')
{
    $input = git_input_field(['name' => $name, 'type' => 'date']);
    $date_min = git_date_trip_min();
    $input->attributes->set('min', $date_min->format('Y-m-d'));
    return $input;
}

function git_file_field(string $name = 'file')
{
    $input = git_input_field(['name' => $name, 'type' => 'file']);
    $input->attributes->set(
        'accept',
        join(
            ',',
            git_get_setting(SettingsKeys::GENERAL_FILE_EXTENSION, [])
        )
    );
    return $input;
}

function git_location_select_field(string $name = 'file', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    foreach (git_locations(['order_by' => 'name']) as $location) {
        $selectComponent->addOption(
            $location->name,
            $location->id
        );
    }
    return $selectComponent;
}

function git_operator_select_field(string $name = 'operator', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    foreach (git_operators() as $operator) {
        $selectComponent->addOption(
            $operator->getUser()->user_login,
            $operator->getUser()->ID
        );
    }

    return $selectComponent;
}

function git_route_select_field(string $name = 'route', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    foreach (git_routes(['order_by' => 'name_origin']) as $route) {
        $selectComponent->addOption(
            "{$route->getOrigin()->name} » {$route->getDestiny()->name} | {$route->getDepartureTime()->format()}",
            $route->id
        );
    }

    return $selectComponent;
}

function git_transport_select_field(string $name = 'transport', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    $args = ['order_by' => 'nicename'];

    if (git_current_user_has_role(UserRole::OPERATOR)) {
        $current_user = wp_get_current_user();
        $args['operator_user_id'] = $current_user->ID;
    }

    foreach (git_transports($args) as $transport) {
        $selectComponent->addOption(
            $transport->nicename,
            $transport->id
        );
    }

    return $selectComponent;
}

function git_service_select_field(string $name = 'service', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    $args = ['order_by' => 'name'];

    foreach (git_services($args) as $service) {
        $selectComponent->addOption(
            $service->name,
            $service->id
        );
    }

    return $selectComponent;
}

function git_ticket_status_select_field(string $name = 'status', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption(
        'Seleccione...',
        ''
    );

    $valuesToSkip = [TicketStatus::NONE, TicketStatus::PERORDER];

    foreach (TicketStatus::cases() as $status) {
        if (in_array($status, $valuesToSkip, true)) {
            continue;
        }

        $selectComponent->addOption(
            $status->label(),
            $status->slug()
        );
    }

    return $selectComponent;
}

function git_country_select_field(string $name = 'country', bool $multiple = false)
{
    $selectComponent = $multiple ? git_multiselect_field(['name' => $name]) : git_select_field(['name' => $name]);

    $selectComponent->addOption('Seleccione...', '');

    if (!defined('GIT_COUNTRIES')) {
        $jsonFilePath = CENTRAL_BOOKING_DIR . 'assets/data/countries.json';
        $jsonString = file_get_contents($jsonFilePath);
        if ($jsonString === false) {
            define('GIT_COUNTRIES', []);
        } else {
            $countries = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                define('GIT_COUNTRIES', []);
            } else {
                define('GIT_COUNTRIES', $countries);
            }
        }
    }

    foreach (GIT_COUNTRIES as $country) {
        $selectComponent->addOption($country, $country);
    }

    return $selectComponent;
}

function git_get_profile_page_url()
{
    $profile_page_id = git_get_setting(SettingsKeys::GENERAL_PROFILE_PAGE);

    if ($profile_page_id === null) {
        return false;
    }

    $permalink = get_permalink((int) $profile_page_id);

    if ($permalink === false) {
        return false;
    }

    return $permalink;
}

function git_nonce_field(string $name = 'git_nonce', bool $display = true)
{
    $name = esc_attr($name);
    $nonce_field = '<input type="hidden" name="' . $name . '" value="' . git_create_nonce() . '" />';
    if ($display) {
        echo $nonce_field;
    }
    return $nonce_field;
}

function git_referer_field(string $name = 'git_referer', bool $display = true)
{
    $request_url = remove_query_arg('_wp_http_referer');
    $referer_field = '<input type="hidden" name="' . $name . '" value="' . esc_url($request_url) . '" />';
    if ($display) {
        echo $referer_field;
    }
    return $referer_field;
}

function git_create_nonce()
{
    return wp_create_nonce(git_get_secret_key());
}

function git_api_key()
{
    $key = 'api_key_git_' . git_get_secret_key();
    return substr(hash('sha256', $key), 0, 32);
}

function git_check_api_key(string $api_key)
{
    return git_api_key() === $api_key;
}

function git_verify_nonce(string $nonce)
{
    return wp_verify_nonce($nonce, git_get_secret_key());
}

function git_date_trip_min()
{
    $offset = git_get_setting(SettingsKeys::FORM_DAYS_WITHOUT_SALE, 0);
    $min_date = git_date_create();
    if ($offset > 0) {
        $min_date->addDays($offset);
    }
    return $min_date;
}

function git_date_trip_valid(Date $date_trip)
{
    $min_date = git_date_trip_min();
    return $min_date->format('Y-m-d') <= $date_trip->format('Y-m-d');
}

function git_date_create(string $Ymd = 'today')
{
    if ($Ymd === 'today') {
        return Date::today();
    }
    return new Date($Ymd);
}

/**
 * Crear QR code para tickets
 */
function git_create_code_qr(mixed $data, int $size = 350)
{
    $data_serialized = git_serialize($data);

    if ($data_serialized === false) {
        return false;
    }

    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$data_serialized}";
}

function git_get_ticket_viewer_page()
{
    $ticket_viewer_data = git_get_setting(SettingsKeys::TICKET_VIEWER);
    if ($ticket_viewer_data === null) {
        return null;
    }
    return get_permalink((int) $ticket_viewer_data);
}

function git_get_ticket_viewer_qr_url($data)
{
    $ticket_viewer_data = git_get_setting(SettingsKeys::TICKET_VIEWER);

    if ($ticket_viewer_data === null) {
        return '#';
    }

    $permalink = get_permalink($ticket_viewer_data);

    if ($permalink === false) {
        return null;
    }

    $url = add_query_arg('data', git_serialize($data), $permalink);

    return $url;
}

function git_currency_format($amount, bool $is_cent = true)
{
    if ($is_cent) {
        $amount /= 100;
    }
    return number_format($amount, 2, ',', '.') . '$';
}

function git_time_format(string $time)
{
    return date_format(date_create($time), 'H:i a');
}

function git_date_format(string $date, bool $short = false)
{
    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $months_short = [
        1 => 'ene',
        2 => 'feb',
        3 => 'mar',
        4 => 'abr',
        5 => 'may',
        6 => 'jun',
        7 => 'jul',
        8 => 'ago',
        9 => 'sep',
        10 => 'oct',
        11 => 'nov',
        12 => 'dic'
    ];

    $date_obj = date_create($date);
    if ($date_obj === false) {
        return $date;
    }

    $day = date_format($date_obj, 'j');
    $month_num = (int) date_format($date_obj, 'n');
    $year = date_format($date_obj, 'Y');

    if ($short) {
        return "{$day} {$months_short[$month_num]}, {$year}";
    } else {
        return "{$day} de {$months[$month_num]}, {$year}";
    }
}

function git_datetime_format(string $datetime)
{
    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $datetime_obj = date_create($datetime);
    if ($datetime_obj === false) {
        return $datetime;
    }

    $day = date_format($datetime_obj, 'j');
    $month_num = (int) date_format($datetime_obj, 'n');
    $year = date_format($datetime_obj, 'Y');
    $time = date_format($datetime_obj, 'G:i');
    $ampm = date_format($datetime_obj, 'A') === 'AM' ? 'am' : 'pm';

    $hour = (int) date_format($datetime_obj, 'G');
    $minute = date_format($datetime_obj, 'i');

    if ($hour == 0) {
        $hour_12 = 12;
    } elseif ($hour > 12) {
        $hour_12 = $hour - 12;
    } else {
        $hour_12 = $hour;
    }

    $time_formatted = sprintf("%d:%s %s", $hour_12, $minute, $ampm);

    return "{$day} de {$months[$month_num]}, {$year} {$time_formatted}";
}

function git_user_logged_in()
{
    return is_user_logged_in();
}

/**
 * @param UserRole|UserRole[] $role
 * @return bool
 */
function git_current_user_has_role($role)
{
    if (is_user_logged_in() === false) {
        return false;
    }
    $user = wp_get_current_user();
    if (is_array($role)) {
        foreach ($role as $r) {
            if (in_array($r->slug(), $user->roles, true)) {
                return true;
            }
        }
        return false;
    }
    return in_array($role->slug(), $user->roles, true);
}

function git_sanitize_file_extensions($input): array|false
{
    $input = trim($input);

    if (empty($input)) {
        return [];
    }

    $extensions = array_map('trim', explode(',', $input));
    $sanitized_extensions = [];

    foreach ($extensions as $extension) {
        $extension = trim($extension);

        if (empty($extension)) {
            continue;
        }

        $extension = preg_replace('/[^a-zA-Z0-9.]/', '', $extension);

        if (!str_starts_with($extension, '.')) {
            $extension = '.' . $extension;
        }

        if (strlen($extension) < 3) {
            continue;
        }

        $extension = strtolower($extension);

        $allowed_extensions = git_get_file_allowed_extensions();

        if (in_array($extension, $allowed_extensions)) {
            $sanitized_extensions[] = $extension;
        }
    }

    $sanitized_extensions = array_values(array_unique($sanitized_extensions));

    return $sanitized_extensions;
}

function git_get_file_allowed_extensions()
{
    return [
        '.jpg',
        '.jpeg',
        '.png',
        '.gif',
        '.webp',
        '.svg',
        '.bmp',
        '.ico',
        '.tiff',
        '.pdf',
        '.doc',
        '.docx',
        '.xls',
        '.xlsx',
        '.ppt',
        '.pptx',
        '.txt',
        '.rtf',
        '.zip',
        '.rar',
        '.7z',
        '.tar',
        '.gz',
        '.mp4',
        '.avi',
        '.mov',
        '.wmv',
        '.flv',
        '.webm',
        '.mp3',
        '.wav',
        '.ogg',
        '.m4a',
        '.flac',
        '.css',
        '.js',
        '.html',
        '.xml',
        '.json'
    ];
}