<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase;
use PhpParser\Node\Expr\Cast\Double;

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
      $gross_amount = $notif_body['gross_amount'];

      $ref = 'Transaksi%20Pembayaran/' . $order_id;

     


      if (!$status_code) {
        return ['code' => 0, 'message' => 'Terjadi kesalahan. Order tidak ditemukan'];
      } else {
        switch ($status_code) {
          case '200': // sukses
            $data11 = [
              'status_code' => $status_code
            ];
            $database->getReference($ref)->update($data11);
            // get jenis donasi
            $product_name = $database->getReference($ref)->getSnapshot()->getChild('product_name')->getValue();

            $cek_dana_program_umum = $database->getReference('Program%20Umum/'.$product_name)->getSnapshot()->getChild("dana")->getValue();

            // if ($product_name == 'Donasi Umum') {
            //   // donasi umum
            //   $ref_donasi = 'Dana/-MKcKLqNL6jEgBgnNg8p';

            //   $dana_tersedia = $database->getReference($ref_donasi)->getSnapshot()->getChild('total_dana')->getValue();

            //   $parse_dana_tersedia = floatval($dana_tersedia);
            //   $data = [
            //     'total_dana' => floatval($gross_amount) + $parse_dana_tersedia
            //   ];
            //   $database->getReference($ref_donasi)->update($data);

            if ($cek_dana_program_umum != null) {
              $parse_dana_pu = floatval(str_replace(".","", $cek_dana_program_umum));
              // tambah dana ke program umum
              $dana_final = $parse_dana_pu + floatval($gross_amount);

              $v = [
                'dana' => strval($dana_final)
              ];
              $database->getReference('Program%20Umum/'.$product_name)->update($v);
        
            
            } else {


              // get biaya kebutuhan
              $ref_kebutuhan = 'Bayar%20Kebutuhan/' . $product_name;
              $sisa_nominal_kebutuhan = $database->getReference($ref_kebutuhan)->getSnapshot()->getChild('sisa_nominal_kebutuhan')->getValue();
              $sisa_nominal_kebutuhan_rplc = str_replace(".","", $sisa_nominal_kebutuhan);
              $parse_sisa_nominal_kebutuhan = floatval($sisa_nominal_kebutuhan_rplc);

              // mengurangi sisa nominal awal
              $sisa_nominal = $parse_sisa_nominal_kebutuhan - floatval($gross_amount);

              // jika berlebih maka simpan ke tabungan masjid
              if ($sisa_nominal < 0) {
                $to_positif = $sisa_nominal * -1;
                // set sisa nominal kebutuhan menjadi 0
                $data = [
                  'sisa_nominal_kebutuhan' => "0"
                ];
                $database->getReference($ref_kebutuhan)->update($data);

                // get id pengurus
                $ref_idPengurus = 'Kebutuhan/' . $product_name;
                $id_pengurus = $database->getReference($ref_idPengurus)->getSnapshot()->getChild('id_pengurus')->getValue();

                // cek apakah id pengurus sudah ada atau belum
                $query = $database->getReference('Dana')
                  ->getChildKeys();

                $already = true;

                foreach ($query as $key => $value) {
                  try {
                    $ref_ = 'Dana/' . $value;
                    $cek = $database->getReference($ref_)->getValue();
                    if ($cek['id_pengurus'] == $id_pengurus) {
                      $data_dana = [
                        'total_dana' => $to_positif + floatval($cek['total_dana'])
                      ];

                      $database->getReference($ref_)->update($data_dana);
                      $already = true;
                      break;
                    } else {
                      $already = false;
                      continue;
                    }
                  } catch (ClientException $cl) {
                    return $cl;
                  } catch (RequestException $rq) {
                    return $rq;
                  }
                }

                // jika tidak ada maka tambah baru 
                if ($already == false) {

                  $upload = [
                    'id_pengurus' => $id_pengurus,
                    'total_dana' => $to_positif,
                    'jenis_dana' => 'Dana Lebih'
                  ];

                  $database->getReference('Dana/')->push($upload);
                }
              } else {
                $datasisa = [
                  'sisa_nominal_kebutuhan' => strval($sisa_nominal)
                ];
                $database->getReference($ref_kebutuhan)->update($datasisa);
              }
            }
            break;
          case '201': // pending

            break;
          case '202': // cancel

            break;
        }
      }

      return response("OK", 200)->header('Content-type', 'text/plain');
    } catch (\Throwable $th) {
      dd($th);
      return ['code' => 0, 'message' => 'Terjadi kesalahan'];
    }
  }
}
