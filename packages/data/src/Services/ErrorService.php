<?php
namespace CentralBooking\Data\Services;

enum ErrorService
{
    case NO_ERROR;
    case PASSENGER_NOT_APPROVED;
    case ROUTE_NOT_FOUND;
    case TICKET_NOT_FOUND;
    case PASSENGER_NOT_FOUND;
    case TRANSPORT_NOT_FOUND;
    case TRANSPORT_NOT_TAKE_ROUTE;
    case TRANSPORT_NOT_AVAILABLE;
    case INVALID_DATE_RANGE;
    case PASSENGERS_PENDING_TRIPS;
    case TICKET_NOT_FLEXIBLE;
    case TRANSPORT_DOES_NOT_TAKE_ROUTE;
}
