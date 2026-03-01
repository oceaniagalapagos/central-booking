<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

class FormTrip implements DisplayerInterface
{
    private SelectComponent $origin_select;
    private SelectComponent $destiny_select;
    private SelectComponent $schedule_select;
    private SelectComponent $transport_select;
    private InputComponent $date_from_input;
    private InputComponent $date_to_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $route_selector = new SelectorRouteCombine();

        $this->date_to_input = new InputComponent('date_to', 'date');
        $this->date_from_input = new InputComponent('date_from', 'date');

        $this->origin_select = $route_selector->get_origin_select('id_origin');
        $this->destiny_select = $route_selector->get_destiny_select('id_destiny');
        $this->schedule_select = $route_selector->get_time_select('time');
        $this->transport_select = $route_selector->get_transport_select('id_transport');

        $this->origin_select->setValue($_GET['id_origin'] ?? '');
        $this->date_to_input->setValue($_GET['date_to'] ?? '');
        $this->destiny_select->setValue($_GET['id_destiny'] ?? '');
        $this->schedule_select->setValue($_GET['time'] ?? '');
        $this->date_from_input->setValue($_GET['date_from'] ?? '');
        $this->transport_select->setValue($_GET['id_transport'] ?? '');

        $this->date_to_input->setRequired(true);
        $this->date_from_input->setRequired(true);

        $this->date_to_input->attributes->set('readonly', '');

        wp_enqueue_script_module(
            'central-tickets-operator-form-trip',
            CENTRAL_BOOKING_URL . '/assets/js/operator/form-trip-operator.js',
            [],
            time(),
        );
    }

    public function render()
    {
        $this->showMessage();
        ?>
        <div class="git-profile-section">
            <div class="git-profile-card">
                <div class="git-profile-card-header">
                    <h3 class="git-card-title">
                        <i class="dashicons dashicons-location"></i>
                        Buscar viajes
                    </h3>
                </div>
                <div class="git-profile-card-body">
                    <form method="get" class="git-search-form">
                        <input type="hidden" name="tab" value="<?= $_GET['tab'] ?? '' ?>">
                        
                        <!-- Ruta -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-route"></i>
                                Ruta del viaje
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->origin_select->getLabel('Origen')->compact(); ?>
                                    <?= $this->origin_select->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->destiny_select->getLabel('Destino')->compact(); ?>
                                    <?= $this->destiny_select->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del viaje -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-clock"></i>
                                Detalles del viaje
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->schedule_select->getLabel('Horario')->compact(); ?>
                                    <?= $this->schedule_select->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->transport_select->getLabel('Transporte')->compact(); ?>
                                    <?= $this->transport_select->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                Período de búsqueda
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->date_from_input->getLabel('Fecha desde')->compact(); ?>
                                    <?= $this->date_from_input->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->date_to_input->getLabel('Fecha hasta')->compact(); ?>
                                    <?= $this->date_to_input->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <div class="git-form-actions">
                            <button type="submit" class="git-btn git-btn-primary">
                                <i class="dashicons dashicons-search"></i>
                                Buscar viajes
                            </button>
                            <button type="reset" class="git-btn git-btn-secondary" onclick="this.form.reset(); window.location.href=window.location.pathname+'?page=git_operator_panel&tab=trips';">
                                <i class="dashicons dashicons-dismiss"></i>
                                Limpiar filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .git-form-section {
                margin-bottom: 32px;
                padding: 20px;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
            }
            
            .git-form-section:last-of-type {
                margin-bottom: 24px;
            }
            
            .git-form-section-title {
                margin: 0 0 16px 0;
                font-size: 16px;
                font-weight: 600;
                color: #374151;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .git-form-section-title .dashicons {
                color: #3b82f6;
                font-size: 18px;
            }
            
            .git-form-row {
                display: flex;
                gap: 20px;
                margin-bottom: 0;
            }
            
            .git-form-group-half {
                flex: 1;
                margin-bottom: 0;
            }
            
            .git-form-group select,
            .git-form-group input[type="date"] {
                width: 100%;
                padding: 12px 16px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 14px;
                transition: all 0.2s ease;
                box-sizing: border-box;
                background: #fff;
            }
            
            .git-form-group select:focus,
            .git-form-group input[type="date"]:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            
            .git-form-group label {
                display: block;
                margin-bottom: 8px;
                color: #374151;
                font-weight: 500;
                font-size: 14px;
            }
            
            .git-form-actions {
                display: flex;
                gap: 12px;
                margin-top: 24px;
                justify-content: flex-start;
            }
            
            .git-btn .dashicons {
                margin-right: 8px;
                font-size: 16px;
                text-decoration: none;
            }
            
            .git-card-title .dashicons {
                margin-right: 10px;
                color: #3b82f6;
                font-size: 20px;
            }
            
            /* Estados especiales para campos readonly */
            .git-form-group input[readonly] {
                background: #f3f4f6;
                color: #6b7280;
                cursor: not-allowed;
            }
            
            @media (max-width: 768px) {
                .git-form-row {
                    flex-direction: column;
                    gap: 0;
                }
                
                .git-form-group-half {
                    margin-bottom: 20px;
                }
                
                .git-form-section {
                    padding: 16px;
                    margin-bottom: 24px;
                }
                
                .git-form-actions {
                    flex-direction: column;
                }
                
                .git-btn {
                    width: 100%;
                }
            }
            
            @media (max-width: 480px) {
                .git-search-form {
                    max-width: 100%;
                }
                
                .git-form-section-title {
                    font-size: 14px;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 4px;
                }
                
                .git-form-section-title .dashicons {
                    font-size: 16px;
                }
            }
        </style>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}
