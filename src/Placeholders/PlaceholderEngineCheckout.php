<?php
namespace CentralBooking\Placeholders;

use WC_Order;

final class PlaceholderEngineCheckout extends PlaceholderEngine
{
    public function __construct(private readonly WC_Order $ticket)
    {
        $this->add_placeholders();
    }

    private function add_placeholders()
    {
        $this->add_placeholder('order_number', fn(array $params) => $this->ticket->get_order_number());
        $this->add_description('order_number', [
            'title' => 'Número de Pedido',
            'description' => 'Número único del pedido realizado',
            'parameters' => [],
        ]);

        $this->add_placeholder('order_date', function (array $params) {
            $date_obj = $this->ticket->get_date_created();
            if (!$date_obj) {
                return 'Fecha no disponible';
            }
            $format = $params['format'] ?? 'iso';
            $include_time = isset($params['time']) && $params['time'] === 'true';
            $result = match ($format) {
                'long' => function_exists('git_date_format')
                ? git_date_format($date_obj->format('Y-m-d'), false)
                : $date_obj->format('j \d\e F \d\e Y'),
                'short' => function_exists('git_date_format')
                ? git_date_format($date_obj->format('Y-m-d'), true)
                : $date_obj->format('j M, Y'),
                'iso' => $date_obj->format('Y-m-d'),
                default => $date_obj->format('Y-m-d')
            };
            if ($include_time) {
                $time_format = $params['time_format'] ?? 'H:i';
                $result .= ' ' . $date_obj->format($time_format);
            }
            return $result;
        });
        $this->add_description('order_date', [
            'title' => 'Fecha de Pedido',
            'description' => 'Fecha en que se realizó el pedido',
            'parameters' => [
                [
                    'param' => 'format',
                    'values' => [
                        [
                            'value' => 'long',
                            'description' => 'Formato largo (ej. 1 de enero de 2023)'
                        ],
                        [
                            'value' => 'short',
                            'description' => 'Formato corto (ej. 01 ene, 2023)'
                        ],
                        [
                            'value' => 'iso',
                            'description' => 'Formato ISO (ej. 2023-01-01)'
                        ]
                    ]
                ],
                [
                    'param' => 'time',
                    'values' => [
                        [
                            'value' => 'true',
                            'description' => 'Incluir hora en el resultado'
                        ],
                        [
                            'value' => 'false',
                            'description' => 'Solo mostrar fecha (por defecto)'
                        ]
                    ]
                ]
            ],
        ]);

        $this->add_placeholder('billing_first_name', fn(array $params) => $this->ticket->get_billing_first_name());
        $this->add_description('billing_first_name', [
            'title' => 'Nombre de Facturación',
            'description' => 'Nombre del cliente en la dirección de facturación',
            'parameters' => [],
        ]);

        $this->add_placeholder('billing_last_name', fn(array $params) => $this->ticket->get_billing_last_name());
        $this->add_description('billing_last_name', [
            'title' => 'Apellido de Facturación',
            'description' => 'Apellido del cliente en la dirección de facturación',
            'parameters' => [],
        ]);

        $this->add_placeholder('billing_email', fn(array $params) => $this->ticket->get_billing_email());
        $this->add_description('billing_email', [
            'title' => 'Email de Facturación',
            'description' => 'Dirección de correo electrónico del cliente para facturación',
            'parameters' => [],
        ]);

        $this->add_placeholder('billing_phone', fn(array $params) => $this->ticket->get_billing_phone());
        $this->add_description('billing_phone', [
            'title' => 'Teléfono de Facturación',
            'description' => 'Número de teléfono del cliente en la dirección de facturación',
            'parameters' => [],
        ]);

        $this->add_placeholder(
            'billing_address',
            fn(array $params) =>
            trim($this->ticket->get_billing_address_1() . ' ' . $this->ticket->get_billing_address_2())
        );
        $this->add_description('billing_address', [
            'title' => 'Dirección de Facturación',
            'description' => 'Dirección completa de facturación (línea 1 + línea 2)',
            'parameters' => [],
        ]);

        $this->add_placeholder(
            'shipping_address',
            fn(array $params) =>
            trim($this->ticket->get_shipping_address_1() . ' ' . $this->ticket->get_shipping_address_2())
        );
        $this->add_description('shipping_address', [
            'title' => 'Dirección de Envío',
            'description' => 'Dirección completa de envío (línea 1 + línea 2)',
            'parameters' => [],
        ]);

        $this->add_placeholder('payment_method', fn(array $params) => $this->ticket->get_payment_method_title());
        $this->add_description('payment_method', [
            'title' => 'Método de Pago',
            'description' => 'Título del método de pago utilizado en el pedido',
            'parameters' => [],
        ]);

        $this->add_placeholder('order_total', function (array $params) {
            $format_currency = isset($params['currency']) && $params['currency'] === 'false' ? false : true;
            if (function_exists('git_currency_format')) {
                return git_currency_format($this->ticket->get_total(), false);
            }
            return $format_currency ? '$' . number_format($this->ticket->get_total(), 2) : number_format($this->ticket->get_total(), 2);
        });
        $this->add_description('order_total', [
            'title' => 'Total del Pedido',
            'description' => 'Monto total del pedido con formato de moneda',
            'parameters' => [
                [
                    'param' => 'currency',
                    'values' => [
                        [
                            'value' => 'true',
                            'description' => 'Incluir símbolo de moneda (por defecto)'
                        ],
                        [
                            'value' => 'false',
                            'description' => 'Solo mostrar número sin símbolo'
                        ]
                    ]
                ]
            ],
        ]);

        $this->add_placeholder('customer_note', fn(array $params) => $this->ticket->get_customer_note());
        $this->add_description('customer_note', [
            'title' => 'Nota del Cliente',
            'description' => 'Comentarios o notas especiales dejadas por el cliente',
            'parameters' => [],
        ]);
    }
}