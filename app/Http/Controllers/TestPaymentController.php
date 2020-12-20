<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Midtrans\CoreApi;
use Illuminate\Http\Request;

class TestPaymentController extends Controller
{
    public function testPayment()
    {
        try {
            $transaksi = array(
                "payment_type" => "bank_transfer",
                "transaction_details" => [
                    "gross_amount" => 10000,
                    "order_id" => date('Y-m-d H:i:s') . '123'
                ],
                "customer_details" => [
                    "email" => "budi.utomo@Midtrans.com",
                    "first_name" => "budi",
                    "last_name" => "utomo",
                    "phone" => "+6281 1234 1234"
                ],
                "item_details" => array([
                    "id" => "1388998298204",
                    "price" => 5000,
                    "quantity" => 1,
                    "name" => "Ayam Zozozo"
                ], [
                    "id" => "1388998298205",
                    "price" => 5000,
                    "quantity" => 1,
                    "name" => "Ayam Xoxoxo"
                ]),
                "bank_transfer" => [
                    "bank" => "bca",
                    "va_number" => "111111",
                ]
            );

            $charge = CoreApi::charge($transaksi);
            if (!$charge) {
                return ['code' => 0, 'message' => 'Terjadi kesalahan'];
            }
            return ['code' => 1, 'message' => 'sukses', 'result' => $charge];
        } catch (\Throwable $th) {

            return ['code' => 0, 'message' => 'Terjadi kesalahan' . $th];
        }
    }
}
