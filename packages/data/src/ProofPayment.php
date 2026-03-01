<?php
/**
 * Proof of Payment Data Class
 * 
 * This file contains the ProofPayment class which represents a proof of payment
 * entity within the Central Booking system. It stores information about payment
 * evidence including file details, amount, and transaction codes.
 * 
 * @package CentralBooking\Data
 * @since 1.0.0
 * @author Central Booking Team
 */

namespace CentralBooking\Data;

use Exception;

/**
 * ProofPayment represents a proof of payment document or evidence.
 * 
 * This final class stores all relevant information about payment proofs
 * including file references, payment amounts, transaction codes, and dates.
 * It serves as a value object for payment verification processes.
 * 
 * @final
 * @package CentralBooking\Data
 * @since 1.0.0
 * 
 * @example
 * $proofPayment = new ProofPayment(
 *     filename: 'payment_receipt_001.pdf',
 *     url: 'https://example.com/uploads/receipts/payment_receipt_001.pdf',
 *     code: 'TXN123456789',
 *     amount: 25000, // Amount in cents
 *     date: new Date('2026-01-05')
 * );
 */
final class ProofPayment
{
    /**
     * Constructs a new ProofPayment instance.
     * 
     * Creates a proof of payment object with all necessary details for
     * payment verification and tracking purposes.
     * 
     * @param string $filename The original filename of the proof document
     *                        (e.g., 'receipt.pdf', 'bank_transfer.jpg')
     * @param string $url The publicly accessible URL to the proof document
     *                   Used for downloading or viewing the payment evidence
     * @param string $code The transaction or reference code associated with the payment
     *                    This could be a bank transaction ID, payment gateway reference, etc.
     * @param int $amount The payment amount in the smallest currency unit (e.g., cents)
     *                   For example: 2500 = $25.00, 150000 = $1,500.00
     * @param Date $date The date when the payment was made
     *                  This should represent the actual payment date, not upload date
     * 
     * @since 1.0.0
     * 
     * @example
     * // Create a proof payment for a bank transfer
     * $proof = new ProofPayment(
     *     filename: 'bank_transfer_receipt.pdf',
     *     url: 'https://storage.example.com/receipts/bt_001.pdf',
     *     code: 'BT20260105001',
     *     amount: 50000, // $500.00
     *     date: new Date('2026-01-05')
     * );
     * 
     * // Create a proof payment for an online payment
     * $proof = new ProofPayment(
     *     filename: 'paypal_confirmation.png',
     *     url: 'https://cdn.example.com/payments/pp_001.png',
     *     code: 'PP-TXN-123456789',
     *     amount: 12500, // $125.00
     *     date: new Date('2026-01-04')
     * );
     */
    public function __construct(
        /** @var string The original filename of the uploaded proof document */
        public string $filename = '',

        /** @var string The accessible URL where the proof document can be viewed/downloaded */
        public string $url = '',

        /** @var string The transaction reference code or payment identifier */
        public string $code = '',

        /** @var int The payment amount in smallest currency unit (cents) */
        public int $amount = 0,

        /** @var Date The date when the payment was executed */
        public Date $date,
    ) {
    }

    public function saveFile(array $file)
    {
        $original_name = $file['name'];
        $unique_name = $this->generateUniqueFilename($original_name);
        $upload_dir = wp_upload_dir();
        $destination = $upload_dir['path'] . '/' . $unique_name;
        if (move_uploaded_file($file['tmp_name'], $destination) === false) {
            return false;
        }
        $this->url = $upload_dir['url'] . '/' . $unique_name;
        return true;
    }

    private function generateUniqueFilename(string $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $microtime = microtime(true);
        $timestamp = date('dHis', intval($microtime));
        $milliseconds = sprintf('%03d', ($microtime - intval($microtime)) * 1000);
        return "$timestamp$milliseconds.$extension";
    }
}
