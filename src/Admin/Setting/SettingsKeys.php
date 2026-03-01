<?php
namespace CentralBooking\Admin\Setting;

final class SettingsKeys
{
    // General Settings
    public const GENERAL_PROFILE_PAGE = 'general_profile_page';
    public const GENERAL_FILE_SIZE = 'general_file_size';
    public const GENERAL_FILE_EXTENSION = 'general_file_extension';

    // Booking Settings
    public const FORM_MESSAGE_RPM = 'form_message_rpm';
    public const FORM_MESSAGE_KID = 'form_message_kid';
    public const FORM_MESSAGE_LOCAL = 'form_message_local';
    public const FORM_MESSAGE_EXTRA = 'form_message_extra';
    public const FORM_MESSAGE_STANDARD = 'form_message_standard';
    public const FORM_MESSAGE_FLEXIBLE = 'form_message_flexible';
    public const FORM_DAYS_WITHOUT_SALE = 'form_days_without_sale';
    public const FORM_MESSAGE_REQUEST_SEATS = 'form_message_request_seats';
    public const FORM_MESSAGE_TERMS_CONDITIONS = 'form_message_terms_conditions';

    // Ticket Viewer Settings
    public const TICKET_VIEWER = 'ticket_viewer';
    public const TICKET_VIEWER_JS = 'ticket_viewer_js';
    public const TICKET_VIEWER_CSS = 'ticket_viewer_css';
    public const TICKET_VIEWER_HTML = 'ticket_viewer_html';
    public const TICKET_VIEWER_DEFAULT_MEDIA = 'ticket_viewer_default_media';
    public const TICKET_VIEWER_PASSENGER_HTML = 'ticket_viewer_passenger_html';

    // Labels Settings
    public const LABEL_TRANSPORT_A = 'label_transport_a';
    public const LABEL_TRANSPORT_B = 'label_transport_b';
    public const LABEL_TRANSPORT_C = 'label_transport_c';
    public const LABEL_TICKET_CANCEL = 'label_ticket_cancel';
    public const LABEL_TICKET_PENDING = 'label_ticket_pending';
    public const LABEL_TICKET_PAYMENT = 'label_ticket_payment';
    public const LABEL_TICKET_PARTIAL = 'label_ticket_partial';
    public const LABEL_TICKET_PREORDER = 'label_ticket_preorder';
    public const LABEL_ROUTE_NONE = 'label_route_none';
    public const LABEL_ROUTE_ONE_WAY = 'label_route_one_way';
    public const LABEL_ROUTE_DOUBLE_WAY = 'label_route_double_way';
    public const LABEL_ROUTE_ANY_WAY = 'label_route_any_way';

    // Notifications Settings
    public const NOTIFICATION_CHECKOUT_MESSAGE = 'notification_checkout_message';
    public const NOTIFICATION_CHECKOUT_EMAIL_TITLE = 'notification_checkout_email_title';
    public const NOTIFICATION_CHECKOUT_EMAIL_SENDER = 'notification_checkout_email_sender';
    public const NOTIFICATION_CHECKOUT_EMAIL_CONTENT = 'notification_checkout_email_content';

    private function __construct()
    {
    }
}
