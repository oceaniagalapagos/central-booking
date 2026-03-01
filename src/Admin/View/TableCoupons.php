<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormCoupon;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableCoupons implements DisplayerInterface
{
    public function render()
    {
        $this->showMessage();
        ?>
        <div style="max-width: 500px; margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"> Comercializador </th>
                        <th scope="col"> Medio </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (git_coupons() as $index => $coupon):
                        $operator = git_operator_by_coupon($coupon);
                        $url = AdminRouter::get_url_for_class(
                            FormCoupon::class,
                            ['id' => $coupon->ID]
                        );
                        ?>
                        <tr style="border-bottom: 1px solid gray;">
                            <td>
                                <span>
                                    <a href="<?= esc_url($url) ?>">
                                        <strong>
                                            <?= esc_html($coupon->post_title) ?>
                                        </strong>
                                    </a>
                                </span>
                                <div class="row-actions visible">
                                    <span
                                        class="edit"><?= esc_html($operator ? "{$operator->getUser()->first_name} {$operator->getUser()->last_name}" : 'N/A') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="doc-container">
                                    <?php

                                    $unique_id = uniqid('doc-media-');

                                    $link = git_text_component([
                                        'id' => 'coupon-media-link-' . $index,
                                        'tag' => 'span',
                                        'text' => 'Ver',
                                        'class' => 'doc-trigger',
                                        'data-target' => $unique_id,
                                        'style' => 'cursor:pointer;',
                                    ]);

                                    $link->render();
                                    ?>
                                    <div id="<?= esc_attr($unique_id) ?>" class="doc-preview">
                                        <img src="<?= esc_url(git_recover_url_brand_media_from_coupon($coupon)) ?>"
                                            alt="Medio del comercializador <?= $coupon->post_title ?>">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <style>
            .doc-container {
                position: relative;
                display: inline-block;
            }

            .doc-trigger {
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
            }

            .doc-trigger:hover {
                font-weight: bold;
            }

            .doc-preview {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                margin-top: 10px;
                background-color: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                padding: 20px;
                min-width: 300px;
                max-width: 500px;
                max-height: 300px;
                /* overflow-y: auto; */
                z-index: 1000;
            }

            .doc-preview.show {
                display: block;
            }

            .doc-preview img {
                width: 100%;
                height: auto;
            }
        </style>
        <script>
            function setupDocPreview(triggerId, previewId) {
                const trigger = document.getElementById(triggerId);
                const preview = document.getElementById(previewId);
                let timeoutId;

                trigger.addEventListener('mouseenter', function () {
                    clearTimeout(timeoutId);
                    preview.classList.add('show');
                });

                trigger.addEventListener('mouseleave', function () {
                    timeoutId = setTimeout(function () {
                        preview.classList.remove('show');
                    }, 200);
                });

                preview.addEventListener('mouseenter', function () {
                    clearTimeout(timeoutId);
                    preview.classList.add('show');
                });

                preview.addEventListener('mouseleave', function () {
                    preview.classList.remove('show');
                });
            }
            document.querySelectorAll('.doc-trigger').forEach(function (trigger) {
                const previewId = trigger.dataset.target;
                setupDocPreview(trigger.id, previewId);
            });
        </script>
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