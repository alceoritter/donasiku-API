<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase;

class NotificationController extends Controller
{
  public function post(Request $request)
  {
    try {

      $factory = (new Factory)->withServiceAccount(__DIR__ . '/mydonate-efca6-firebase-adminsdk-hm66y-7ee73463bb.json');

      $database = $factory->createDatabase();

      // $ref = 'Transaksi%20Pembayaran/1608423760305' ;
      // $fetchData = $database->getReference($ref)->getValue();

      // var_dump($fetchData);
      // die;
      $notif_body = json_decode($request->getContent(), true);
      $order_id = $notif_body['order_id'];
      $transaksi_id = $notif_body['transaction_id'];
      $status_code = $notif_body['status_code'];

      $ref = 'Transaksi%20Pembayaran/'. $order_id ;

      

      $data = [
        'status_code' => $status_code
      ];
      $database->getReference($ref)->update($data);


      // if (!$ref) {
      //   return ['code' => 0, 'message' => 'Terjadi kesalahan. Order tidak ditemukan'];
      // }
      // switch ($ref) {
      //   case '200': // sukses

      //     break;
      //   case '201': // pending

      //     break;
      //   case '202': // cancel

      //     break;
      // }

      return response('OK', 200)->header('Content-type', 'text/plain');
    } catch (\Throwable $th) {
      dd($th);
      return ['code' => 0, 'message' => 'Terjadi kesalahan'];
    }
  }
}
