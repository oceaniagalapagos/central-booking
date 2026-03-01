<?php
namespace CentralBooking\Data\Repository;
enum DatabaseTable: string
{
    case TABLE_META = 'git_meta';
    case TABLE_ZONES = 'git_zones';
    case TABLE_ROUTES = 'git_routes';
    case TABLE_TICKETS = 'git_tickets';
    case TABLE_SERVICES = 'git_services';
    case TABLE_LOCATIONS = 'git_locations';
    case TABLE_PASSENGERS = 'git_passengers';
    case TABLE_TRANSPORTS = 'git_transports';
    case TABLE_ROUTES_TRANSPORTS = 'git_routes_transports';
    case TABLE_TRANSPORTS_SERVICES = 'git_transports_services';
}