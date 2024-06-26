<?php

namespace App\Http\Controllers\Api\Backend\Online\Shopee;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\Api\Response;
use App\Helpers\Api\UpdateToken;
use App\Helpers\Api\ServiceShopee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class ApiOrderController extends Controller
{
    public function daftarOrder(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'fields'        => 'required',
                'start_date'    => 'required',
                'end_date'      => 'required',
                'page_size'     => 'required',
                'companyid'     => 'required',
            ]);

            if($validate->fails()) {
                return Response::responseWarning("Data fields, start date, end date, dan page size tidak boleh kosong");
            }

            $start_date = $request->get('start_date').'T'.'00:00:00';
            $end_date = $request->get('end_date').'T'.'23:59:59';

            if(strtotime($start_date) > strtotime($end_date)) {
                return Response::responseWarning('Tanggal awal harus lebih kecil dari tanggal akhir');
            } else {
                $seconds_to_expire = strtotime($start_date) - strtotime($end_date);

                if($seconds_to_expire >= 15 * 86400) {
                    return Response::responseWarning('Rentang Tanggal harus kurang dari 3 hari');
                }
            }

            $authorization = $request->header('Authorization');
            $token = explode(" ", $authorization);
            $auth_token = trim($token[1]);

            $token_shopee = '';

            $sql = DB::table('user_api_office')->lock('with (nolock)')
                    ->selectRaw("isnull(user_api_office.shopee_token, '') as shopee_token,
                                isnull(user_api_office.user_id, '') as user_id")
                    ->where('user_api_office.office_token', $auth_token)
                    ->orderByRaw("isnull(user_api_office.id, 0) desc")
                    ->first();

            if(empty($sql->shopee_token) || trim($sql->shopee_token) == '') {
                return Response::responseWarning('Token shopee tidak ditemukan, lakukan logout kemudian login kembali');
            } else {
                $token_shopee = $sql->shopee_token;
            }

            // ==========================================================================
            // CEK KONEKSI API SHOPEE
            // ==========================================================================
            $responseShopee = ServiceShopee::GetShopInfo(trim($token_shopee));
            $statusServer = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusServer == 0) {
                $authorization = $request->header('Authorization');
                $token = explode(" ", $authorization);
                $auth_token = trim($token[1]);

                $responseUpdateToken = UpdateToken::shopee($auth_token);

                if($responseUpdateToken->status == 1) {
                    $token_shopee = $responseUpdateToken->data->token;
                } else {
                    return Response::responseWarning($responseUpdateToken->message);
                }
            }

            $responseShopee = ServiceShopee::GetOrderList(trim($token_shopee), $request->get('fields'),
                                strtotime($start_date), strtotime($end_date), $request->get('page_size'),
                                $request->get('cursor'), $request->get('status'));
            $statusResponseShopee = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusResponseShopee == 1) {
                $dataShopee = json_decode($responseShopee)->response;
                $dataInvoice = $dataShopee->order_list;
                $data_order = new Collection();

                if($dataInvoice != null) {
                    $list_invoice = '';
                    $list_invoice_faktur = '';
                    $data_faktur = new Collection();

                    foreach($dataInvoice as $data) {
                        if(trim($list_invoice) == '') {
                            $list_invoice = trim($data->order_sn);
                        } else {
                            $list_invoice .= ','.trim($data->order_sn);
                        }

                        if(trim($list_invoice_faktur) == '') {
                            $list_invoice_faktur = "'".trim($data->order_sn)."'";
                        } else {
                            $list_invoice_faktur .= ','."'".trim($data->order_sn)."'";
                        }
                    }

                    $sql = "select  isnull(faktur.no_faktur, 0) as nomor_faktur,
                                    isnull(faktur.tgl_faktur, '') as tanggal_faktur,
                                    isnull(faktur.kd_lokasi, '') as kode_lokasi,
                                    isnull(faktur.kd_sales, '') as kode_sales,
                                    isnull(faktur.kd_dealer, '') as kode_dealer,
                                    isnull(faktur.kd_ekspedisi, '') as kode_ekspedisi,
                                    isnull(faktur.ket, '') as keterangan,
                                    isnull(faktur.total, 0) as total,
                                    isnull(sj_dtl.no_sj, '') as nomor_surat_jalan,
                                    isnull(serah_online_dtl.no_dok, '') as nomor_serah_terima
                            from
                            (
                                select  faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                        faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                        faktur.ket, faktur.total,
                                        max(fakt_dtl.kd_lokasi) as kd_lokasi
                                from
                                (
                                    select  faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                            faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                            faktur.ket, faktur.total
                                    from    faktur with (nolock)
                                    where   faktur.ket in (".$list_invoice_faktur.") and
                                            faktur.companyid=?
                                )  faktur
                                    left join fakt_dtl with (nolock) on faktur.no_faktur=fakt_dtl.no_faktur and
                                                faktur.companyid=fakt_dtl.companyid
                                group by faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                        faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                        faktur.ket, faktur.total
                            )   faktur
                                    left join sj_dtl with (nolock) on faktur.no_faktur=sj_dtl.no_faktur and
                                                faktur.companyid=sj_dtl.companyid
                                    left join serah_online_dtl with (nolock) on sj_dtl.no_sj=serah_online_dtl.no_sj and
                                                faktur.companyid=serah_online_dtl.companyid";

                    $result = DB::select($sql, [ $request->get('companyid') ]);

                    foreach($result as $data) {
                        $data_faktur->push((object) [
                            'nomor_faktur'          => strtoupper(trim($data->nomor_faktur)),
                            'nomor_surat_jalan'     => strtoupper(trim($data->nomor_surat_jalan)),
                            'nomor_serah_terima'    => strtoupper(trim($data->nomor_serah_terima)),
                            'tanggal'               => trim($data->tanggal_faktur),
                            'kode_lokasi'           => trim($data->kode_lokasi),
                            'kode_sales'            => trim($data->kode_sales),
                            'kode_dealer'           => trim($data->kode_dealer),
                            'kode_ekspedisi'        => trim($data->kode_ekspedisi),
                            'keterangan'            => strtoupper(trim($data->keterangan)),
                            'total'                 => (double)$data->total,
                        ]);
                    }

                    $responseShopee = ServiceShopee::GetOrderDetail(trim($token_shopee), $list_invoice);
                    $statusResponseShopee = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

                    if($statusResponseShopee == 1) {
                        $dataShopee = json_decode($responseShopee)->response;

                        foreach($dataShopee->order_list as $data) {
                            $total_products = 0;
                            foreach($data->item_list as $item) {
                                $total = (double)$item->model_discounted_price * (double)$item->model_quantity_purchased;
                                $total_products = (double)$total_products + (double)$total;
                            }

                            $data_order->push([
                                'order_sn'      => $data->order_sn,
                                'create_time'   => $data->create_time,
                                'update_time'   => $data->update_time,
                                'order_status'  => $data->order_status,
                                'buyer_username'=> $data->buyer_username,
                                'cod'           => $data->cod,
                                'payment_method'=> $data->payment_method,
                                'shipping_carrier'=> $data->shipping_carrier,
                                'recipient'     => [
                                    'full_address'  => $data->recipient_address->full_address,
                                    'district'      => $data->recipient_address->district,
                                    'city'          => $data->recipient_address->city,
                                    'state'         => $data->recipient_address->state,
                                    'region'        => $data->recipient_address->region,
                                    'zipcode'       => $data->recipient_address->zipcode,
                                ],
                                'note'              => $data->note,
                                'actual_shipping_fee'=> $data->actual_shipping_fee,
                                'actual_products'   => $total_products,
                                'total_amount'      => $data->total_amount,
                                'faktur'            => $data_faktur
                                                        ->where('keterangan', $data->order_sn)
                                                        ->values()
                                                        ->all()
                            ]);
                        }
                    } else {
                        return Response::responseWarning(json_decode($responseShopee)->error);
                    }
                }

                return Response::responseSuccess('success', $data_order);
            } else {
                return Response::responseWarning(json_decode($responseShopee)->error);
            }
        } catch (\Exception $exception) {
            return Response::responseError($request->get('user_id'), 'API', Route::getCurrentRoute()->action['controller'],
                        $request->route()->getActionMethod(), $exception->getMessage(), $request->get('companyid'));
        }
    }

    public function singleOrder(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'nomor_invoice' => 'required',
                'companyid'     => 'required',
            ]);

            if($validate->fails()) {
                return Response::responseWarning("Nomor invoice tidak boleh kosong");
            }

            $authorization = $request->header('Authorization');
            $token = explode(" ", $authorization);
            $auth_token = trim($token[1]);

            $token_shopee = '';

            $sql = DB::table('user_api_office')->lock('with (nolock)')
                    ->selectRaw("isnull(user_api_office.shopee_token, '') as shopee_token,
                                isnull(user_api_office.user_id, '') as user_id")
                    ->where('user_api_office.office_token', $auth_token)
                    ->orderByRaw("isnull(user_api_office.id, 0) desc")
                    ->first();

            if(empty($sql->shopee_token) || trim($sql->shopee_token) == '') {
                return Response::responseWarning('Token shopee tidak ditemukan, lakukan logout kemudian login kembali');
            } else {
                $token_shopee = $sql->shopee_token;
            }

            // ==========================================================================
            // CEK KONEKSI API SHOPEE
            // ==========================================================================
            $responseShopee = ServiceShopee::GetShopInfo(trim($token_shopee));
            $statusServer = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusServer == 0) {
                $authorization = $request->header('Authorization');
                $token = explode(" ", $authorization);
                $auth_token = trim($token[1]);

                $responseUpdateToken = UpdateToken::shopee($auth_token);

                if($responseUpdateToken->status == 1) {
                    $token_shopee = $responseUpdateToken->data->token;
                } else {
                    return Response::responseWarning($responseUpdateToken->message);
                }
            }

            $data_order = new Collection();
            $responseShopee = ServiceShopee::GetOrderDetail(trim($token_shopee), $request->get('nomor_invoice'));
            $statusResponseShopee = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusResponseShopee == 1) {
                $dataShopee = json_decode($responseShopee)->response;

                $data_faktur = new Collection();

                $sql = "select  isnull(faktur.no_faktur, 0) as nomor_faktur,
                                    isnull(faktur.tgl_faktur, '') as tanggal_faktur,
                                    isnull(faktur.kd_lokasi, '') as kode_lokasi,
                                    isnull(faktur.kd_sales, '') as kode_sales,
                                    isnull(faktur.kd_dealer, '') as kode_dealer,
                                    isnull(faktur.kd_ekspedisi, '') as kode_ekspedisi,
                                    isnull(faktur.ket, '') as keterangan,
                                    isnull(faktur.total, 0) as total,
                                    isnull(sj_dtl.no_sj, '') as nomor_surat_jalan,
                                    isnull(serah_online_dtl.no_dok, '') as nomor_serah_terima
                        from
                        (
                            select  faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                    faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                    faktur.ket, faktur.total, max(fakt_dtl.kd_lokasi) as kd_lokasi
                            from
                            (
                                select  faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                        faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                        faktur.ket, faktur.total
                                from    faktur with (nolock)
                                where   faktur.ket=? and
                                        faktur.companyid=?
                            )  faktur
                                    left join fakt_dtl with (nolock) on faktur.no_faktur=fakt_dtl.no_faktur and
                                            faktur.companyid=fakt_dtl.companyid
                            group by faktur.companyid, faktur.no_faktur, faktur.tgl_faktur,
                                    faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                    faktur.ket, faktur.total
                        )   faktur
                                left join sj_dtl with (nolock) on faktur.no_faktur=sj_dtl.no_faktur and
                                            faktur.companyid=sj_dtl.companyid
                                left join serah_online_dtl with (nolock) on sj_dtl.no_sj=serah_online_dtl.no_sj and
                                            faktur.companyid=serah_online_dtl.companyid";

                    $result = DB::select($sql, [ $request->get('nomor_invoice'), $request->get('companyid') ]);

                    foreach($result as $data) {
                        $data_faktur->push((object) [
                            'nomor_faktur'          => strtoupper(trim($data->nomor_faktur)),
                            'nomor_surat_jalan'     => strtoupper(trim($data->nomor_surat_jalan)),
                            'nomor_serah_terima'    => strtoupper(trim($data->nomor_serah_terima)),
                            'tanggal'               => trim($data->tanggal_faktur),
                            'kode_lokasi'           => trim($data->kode_lokasi),
                            'kode_sales'            => trim($data->kode_sales),
                            'kode_dealer'           => trim($data->kode_dealer),
                            'kode_ekspedisi'        => trim($data->kode_ekspedisi),
                            'keterangan'            => strtoupper(trim($data->keterangan)),
                            'total'                 => (double)$data->total,
                        ]);
                    }

                foreach($dataShopee->order_list as $data) {
                    $total_products = 0;

                    foreach($data->item_list as $item) {
                        $total = (double)$item->model_discounted_price * (double)$item->model_quantity_purchased;
                        $total_products = (double)$total_products + (double)$total;
                    }

                    $data_order->push([
                        'order_sn'      => $data->order_sn,
                        'create_time'   => $data->create_time,
                        'update_time'   => $data->update_time,
                        'order_status'  => $data->order_status,
                        'buyer_username'=> $data->buyer_username,
                        'cod'           => $data->cod,
                        'payment_method'=> $data->payment_method,
                        'shipping_carrier'=> $data->shipping_carrier,
                        'recipient'     => [
                            'full_address'  => $data->recipient_address->full_address,
                            'district'      => $data->recipient_address->district,
                            'city'          => $data->recipient_address->city,
                            'state'         => $data->recipient_address->state,
                            'region'        => $data->recipient_address->region,
                            'zipcode'       => $data->recipient_address->zipcode,
                        ],
                        'note'              => $data->note,
                        'actual_shipping_fee'=> $data->actual_shipping_fee,
                        'actual_products'   => $total_products,
                        'total_amount'      => $data->total_amount,
                        'faktur'            => $data_faktur
                                                ->where('keterangan', $data->order_sn)
                                                ->values()
                                                ->all()
                    ]);
                }

                return Response::responseSuccess('success', $data_order);
            } else {
                if(json_decode($responseShopee)->error == 'error_not_found') {
                    return Response::responseWarning('Nomor invoice '.trim($request->get('nomor_invoice').' tidak terdaftar'));
                } else {
                    return Response::responseWarning(json_decode($responseShopee)->error);
                }
            }
        } catch (\Exception $exception) {
            return Response::responseError($request->get('user_id'), 'API', Route::getCurrentRoute()->action['controller'],
                        $request->route()->getActionMethod(), $exception->getMessage(), $request->get('companyid'));
        }
    }

    public function formOrder(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'nomor_invoice'     => 'required',
                'companyid'         => 'required|string',
                'user_id'           => 'required|string',
            ]);

            if($validate->fails()) {
                return Response::responseWarning("Isi nomor invoice terlebih dahulu");
            }

            $authorization = $request->header('Authorization');
            $token = explode(" ", $authorization);
            $auth_token = trim($token[1]);

            $token_shopee = '';

            $sql = DB::table('user_api_office')->lock('with (nolock)')
                    ->selectRaw("isnull(user_api_office.shopee_token, '') as shopee_token,
                                isnull(user_api_office.user_id, '') as user_id")
                    ->where('user_api_office.office_token', $auth_token)
                    ->orderByRaw("isnull(user_api_office.id, 0) desc")
                    ->first();

            if(empty($sql->shopee_token) || trim($sql->shopee_token) == '') {
                return Response::responseWarning('Token shopee tidak ditemukan, lakukan logout kemudian login kembali');
            } else {
                $token_shopee = $sql->shopee_token;
            }

            // ==========================================================================
            // CEK KONEKSI API Shopee
            // ==========================================================================
            $responseshopee = ServiceShopee::GetShopInfo(trim($token_shopee));
            $statusServer = (empty(json_decode($responseshopee)->error)) ? 1 : 0;

            if($statusServer == 0) {
                $authorization = $request->header('Authorization');
                $token = explode(" ", $authorization);
                $auth_token = trim($token[1]);

                $responseUpdateToken = UpdateToken::shopee($auth_token);

                if($responseUpdateToken->status == 1) {
                    $token_shopee = $responseUpdateToken->data->token;
                } else {
                    return Response::responseWarning($responseUpdateToken->message);
                }
            }

            // ==========================================================================
            // AMBIL DATA SHOPEE
            // ==========================================================================
            $data_faktur = new Collection();
            $responseShopee = ServiceShopee::GetOrderDetail(trim($token_shopee), trim($request->get('nomor_invoice')));
            $statusResponseShopee = (empty(json_decode($responseshopee)->error)) ? 1 : 0;

            if($statusResponseShopee == 0) {
                return Response::responseWarning(json_decode($responseShopee)->error);
            }

            $dataShopee = json_decode($responseShopee)->response;

            $data_shopee = new Collection();
            $data_product_shopee = new Collection();
            $data_product_id_marketplace = '';
            $item_price = 0;

            $nomor_invoice = '';
            $kode_ekspedisi = '';
            $nama_ekspedisi = '';

            foreach($dataShopee->order_list as $data) {
                $nomor_invoice = strtoupper(trim($data->order_sn));
                $nama_ekspedisi = strtoupper(trim($data->shipping_carrier));

                $responseChannelList = ServiceShopee::GetChannelList(trim($token_shopee));
                $statusResponseChannelList = (empty(json_decode($responseChannelList)->error)) ? 1 : 0;

                if($statusResponseChannelList == 1) {
                    $dataChannelList = json_decode($responseChannelList)->response;

                    foreach($dataChannelList->logistics_channel_list as $data_channel) {
                        if(strtoupper(trim($nama_ekspedisi)) == strtoupper(trim($data_channel->logistics_channel_name))) {
                            $kode_ekspedisi = strtoupper(trim($data_channel->logistics_channel_id));
                        }
                    }
                } else {
                    return Response::responseWarning(json_decode($responseShopee)->error);
                }

                foreach($data->item_list as $item) {
                    if(strtoupper(trim($data_product_id_marketplace)) == '') {
                        $data_product_id_marketplace = "'".$item->item_id."'";
                    } else {
                        $data_product_id_marketplace .= ','."'".$item->item_id."'";
                    }

                    $item_price = (double)$item_price + ((double)$item->model_discounted_price * (double)$item->model_quantity_purchased);

                    $data_product_shopee->push((object) [
                        'item_id'                   => $item->item_id,
                        'item_sku'                  => $item->item_sku,
                        'item_name'                 => $item->item_name,
                        'model_quantity_purchased'  => $item->model_quantity_purchased,
                        'model_discounted_price'    => $item->model_discounted_price,
                        'subtotal_price'            => (double)$item->model_discounted_price * (double)$item->model_quantity_purchased,
                        'pictures'                  => $item->image_info->image_url
                    ]);
                }

                $data_shopee->push((object) [
                    'order_id'          => $data->order_sn,
                    'nomor_invoice'     => $data->order_sn,
                    'item_price'        => (double)$item_price,
                    'shipping_price'    => (double)$data->actual_shipping_fee,
                    'logistics'         => (object)[
                        'id'            => $kode_ekspedisi,
                        'name'          => $data->shipping_carrier
                    ],
                    'address'           => (object)[
                        'full_address'  => $data->recipient_address->full_address,
                        'district'      => $data->recipient_address->district,
                        'city'          => $data->recipient_address->city,
                        'province'      => $data->recipient_address->state,
                        'postal'        => $data->recipient_address->zipcode,
                    ],
                    'payment'           => $data->payment_method,
                    'status'            => $data->order_status,
                    'detail'            => $data_product_shopee
                ]);
            }

            if(trim($nomor_invoice) == '') {
                return Response::responseWarning('Nomor invoice tidak terdaftar');
            }

            $status_faktur = 0;

            $sql = "select	isnull(faktur.no_faktur, '') as nomor_faktur, isnull(faktur.tgl_faktur, '') as tanggal_faktur,
                            isnull(faktur.kd_beli, '') as kode_beli, isnull(jns_beli.nama, '') as nama_beli,
                            isnull(faktur.no_pof, '') as nomor_pof, isnull(faktur.kd_sales, '') as kode_sales,
                            isnull(salesman.nm_sales, '') as nama_sales, isnull(faktur.kd_dealer, '') as kode_dealer,
                            isnull(dealer.nm_dealer, '') as nama_dealer, isnull(faktur.kd_ekspedisi, '') as kode_ekspedisi,
                            isnull(ekspedisi_online.nm_ekspedisi, '') as nama_ekspedisi, isnull(faktur.kd_tpc, '') as kode_tpc,
                            isnull(faktur.umur_faktur, 0) as umur_faktur, isnull(faktur.tgl_akhir_faktur, '') as tanggal_akhir_faktur,
                            isnull(faktur.rh, '') as rh, isnull(faktur.bo, '') as bo, isnull(faktur.ket, '') as keterangan,
                            isnull(fakt_dtl.kd_part, '') as part_number, isnull(part.ket, '') as nama_part,
                            isnull(fakt_dtl.kd_lokasi, '') as kode_lokasi, isnull(lokasi.ket, '') as nama_lokasi,
                            isnull(fakt_dtl.jml_order, 0) as jml_order, isnull(fakt_dtl.jml_jual, 0) as jml_jual,
                            isnull(fakt_dtl.harga, 0) as harga_detail, isnull(fakt_dtl.disc1, 0) as disc_detail,
                            isnull(fakt_dtl.jumlah, 0) as total_detail, isnull(faktur.disc2, 0) as disc_header,
                            isnull(faktur.discrp, 0) as disc_rp, isnull(faktur.discrp1, 0) as disc_rp1,
                            isnull(faktur.total, 0) as total_faktur, isnull(sj_dtl.no_sj, '') as nomor_sj,
                            isnull(serah_online_dtl.no_dok, '') as nomor_serah_terima
                    from
                    (
                        select	faktur.companyid, faktur.no_faktur, faktur.tgl_faktur, faktur.kd_beli,
                                faktur.no_pof, faktur.kd_sales, faktur.kd_dealer, faktur.kd_ekspedisi,
                                faktur.kd_tpc, faktur.umur_faktur, faktur.tgl_akhir_faktur,
                                faktur.bo, faktur.rh, faktur.ket, faktur.disc2,
                                faktur.discrp, faktur.discrp1, faktur.total
                        from	faktur with (nolock)
                        where	faktur.ket=? and faktur.companyid=?
                    )	faktur
                            left join jns_beli with (nolock) on faktur.kd_beli=jns_beli.kd_beli and
                                        faktur.companyid=jns_beli.companyid
                            left join salesman with (nolock) on faktur.kd_sales=salesman.kd_sales and
                                        faktur.companyid=salesman.companyid
                            left join dealer with (nolock) on faktur.kd_dealer=dealer.kd_dealer and
                                        faktur.companyid=dealer.companyid
                            left join ekspedisi_online with (nolock) on faktur.kd_ekspedisi=ekspedisi_online.kd_ekspedisi
                            left join fakt_dtl with (nolock) on faktur.no_faktur=fakt_dtl.no_faktur and
                                        faktur.companyid=fakt_dtl.companyid
                            left join lokasi with (nolock) on fakt_dtl.kd_lokasi=lokasi.kd_lokasi and
                                        faktur.companyid=lokasi.companyid
                            left join part with (nolock) on fakt_dtl.kd_part=part.kd_part and
                                        faktur.companyid=part.companyid
                            left join sj_dtl with (nolock) on faktur.no_faktur=sj_dtl.no_faktur and
                                        faktur.companyid=sj_dtl.companyid
                            left join serah_online_dtl with (nolock) on sj_dtl.no_sj=serah_online_dtl.no_sj and
                                        faktur.companyid=serah_online_dtl.companyid
                    order by faktur.companyid asc, faktur.no_faktur asc";

            $result = DB::select($sql, [ strtoupper($nomor_invoice), $request->get('companyid') ]);

            $jumlah_faktur = 0;
            $data_faktur = new Collection();
            $data_faktur_temp = new Collection();
            $data_faktur_detail_temp = new Collection();

            foreach($result as $data) {
                $jumlah_faktur = (double)$jumlah_faktur + 1;

                $data_faktur_detail_temp->push((object) [
                    'pictures'      => trim(config('constants.app.url.images')).'/'.strtoupper(trim($data->part_number)).'.jpg',
                    'nomor_faktur'  => strtoupper(trim($data->nomor_faktur)),
                    'part_number'   => strtoupper(trim($data->part_number)),
                    'nama_part'     => strtoupper(trim($data->nama_part)),
                    'jml_order'     => (double)$data->jml_order,
                    'jml_jual'      => (double)$data->jml_jual,
                    'stock'         => 0,
                    'harga'         => (double)$data->harga_detail,
                    'disc_detail'   => (double)$data->disc_detail,
                    'total_detail'  => (double)$data->total_detail,
                    'keterangan'    => '',
                ]);

                $data_faktur_temp->push((object) [
                    'nomor_faktur'      => trim($data->nomor_faktur),
                    'tanggal'           => trim($data->tanggal_faktur),
                    'nomor_pof'         => trim($data->nomor_pof),
                    'kode_lokasi'       => trim($data->kode_lokasi),
                    'nama_lokasi'       => trim($data->nama_lokasi),
                    'kode_beli'         => trim($data->kode_beli),
                    'nama_beli'         => trim($data->nama_beli),
                    'kode_sales'        => trim($data->kode_sales),
                    'nama_sales'        => trim($data->nama_sales),
                    'kode_dealer'       => trim($data->kode_dealer),
                    'nama_dealer'       => trim($data->nama_dealer),
                    'kode_ekspedisi'    => trim($data->kode_ekspedisi),
                    'nama_ekspedisi'    => trim($data->nama_ekspedisi),
                    'kode_tpc'          => trim($data->kode_tpc),
                    'umur_faktur'       => (double)$data->umur_faktur,
                    'tanggal_akhir_faktur' => trim($data->tanggal_akhir_faktur),
                    'rh'                => strtoupper(trim($data->rh)),
                    'bo'                => strtoupper(trim($data->bo)),
                    'keterangan'        => trim($data->keterangan),
                    'disc_header'       => (double)$data->disc_header,
                    'disc_rp'           => (double)$data->disc_rp,
                    'disc_rp1'          => (double)$data->disc_rp1,
                    'total'             => (double)$data->total_faktur,
                    'nomor_sj'          => strtoupper(trim($data->nomor_sj)),
                    'nomor_serah_terima'=> strtoupper(trim($data->nomor_serah_terima))
                ]);
            }

            if((double)$jumlah_faktur > 0) {
                $status_faktur = 1;
                $nomor_faktur = '';
                $sub_total = 0;

                foreach($data_faktur_temp as $data) {
                    if(strtoupper(trim($nomor_faktur)) != strtoupper(trim($data->nomor_faktur))) {
                        $sub_total = $data_faktur_detail_temp
                                        ->where('nomor_faktur', strtoupper(trim($data->nomor_faktur)))
                                        ->sum('total_detail');

                        $data_faktur->push((object) [
                            'nomor_faktur'      => strtoupper(trim($data->nomor_faktur)),
                            'tanggal'           => trim($data->tanggal),
                            'nomor_pof'         => strtoupper(trim($data->nomor_pof)),
                            'lokasi'            => ((object)[
                                'kode'          => strtoupper(trim($data->kode_lokasi)),
                                'nama'          => strtoupper(trim($data->nama_lokasi))
                            ]),
                            'jenis_beli'        => ((object)[
                                'kode'          => strtoupper(trim($data->kode_beli)),
                                'keterangan'    => strtoupper(trim($data->nama_beli))
                            ]),
                            'salesman'          => ((object)[
                                'kode'          => strtoupper(trim($data->kode_sales)),
                                'nama'          => strtoupper(trim($data->nama_sales))
                            ]),
                            'dealer'            => ((object)[
                                'kode'          => strtoupper(trim($data->kode_dealer)),
                                'nama'          => strtoupper(trim($data->nama_dealer))
                            ]),
                            'ekspedisi'         => ((object)[
                                'kode'          => strtoupper(trim($data->kode_ekspedisi)),
                                'nama'          => strtoupper(trim($data->nama_ekspedisi))
                            ]),
                            'kode_tpc'          => trim($data->kode_tpc),
                            'jatuh_tempo'       => ((object)[
                                'umur_faktur'   => (double)$data->umur_faktur,
                                'tanggal'       => trim($data->tanggal_akhir_faktur)
                            ]),
                            'status'            => ((object)[
                                'rh'            => strtoupper(trim($data->rh)),
                                'bo'            => strtoupper(trim($data->bo)),
                            ]),
                            'keterangan'        => trim($data->keterangan),
                            'nomor_sj'          => trim($data->nomor_sj),
                            'nomor_serah_terima'=> trim($data->nomor_serah_terima),
                            'total'             => ((object)[
                                'sub_total'     => (double)$sub_total,
                                'disc_header'   => (double)$data->disc_header,
                                'disc_header_rp' => round(((double)$sub_total * (double)$data->disc_header) / 100),
                                'disc_rp'       => (double)$data->disc_rp,
                                'disc_rp1'      => (double)$data->disc_rp1,
                                'total'         => (double)$data->total,
                            ]),
                            'detail'            => $data_faktur_detail_temp
                                                    ->where('nomor_faktur', strtoupper(trim($data->nomor_faktur)))
                                                    ->values()
                                                    ->all()
                        ]);

                        $nomor_faktur = strtoupper(trim($data->nomor_faktur));
                    }

                    $jumlah_faktur = 0;
                    $total_faktur_amount = 0;

                    foreach($data_faktur as $data) {
                        $jumlah_faktur = (double)$jumlah_faktur + 1;
                        $total_faktur_amount = (double)$total_faktur_amount + (double)$data->total->total;
                    }
                }
            } else {
                $status_faktur = 0;

                $sql = "select	top 1 isnull(salesman.kode_sales, '') as kode_sales,
                                isnull(salesman.nama_sales, '') as nama_sales,
                                isnull(dealer.kode_dealer, '') as kode_dealer,
                                isnull(dealer.nama_dealer, '') as nama_dealer,
                                isnull(jns_beli.kode_beli, '') as kode_beli,
                                isnull(jns_beli.nama_beli, '') as nama_beli,
                                isnull(lokasi.kode_lokasi, '') as kode_lokasi,
                                isnull(lokasi.nama_lokasi, '') as nama_lokasi,
                                isnull(ekspedisi.kode_ekspedisi, '') as kode_ekspedisi,
                                isnull(ekspedisi.nama_ekspedisi, '') as nama_ekspedisi
                        from
                        (
                            select	top 1 'SHOPEE' as marketplace
                        )	marketplace
                        left join
                        (
                            select	top 1 'SHOPEE' as marketplace,
                                    isnull(salesman.kd_sales, '') as kode_sales,
                                    isnull(salesman.nm_sales, '') as nama_sales
                            from	salesman with (nolock)
                            where	salesman.kd_sales='".config('constants.api.shopee.kode_sales')."' and
                                    salesman.companyid='".$request->get('companyid')."'
                        )	salesman on marketplace.marketplace=salesman.marketplace
                        left join
                        (
                            select	top 1 'SHOPEE' as marketplace,
                                    isnull(dealer.kd_dealer, '') as kode_dealer,
                                    isnull(dealer.nm_dealer, '') as nama_dealer
                            from	dealer with (nolock)
                            where	dealer.kd_dealer='".config('constants.app.shopee.kode_dealer')."' and
                                    dealer.companyid='".$request->get('companyid')."'
                        )	dealer on marketplace.marketplace=dealer.marketplace
                        left join
                        (
                            select	top 1 'SHOPEE' as marketplace,
                                    isnull(jns_beli.kd_beli, '') as kode_beli,
                                    isnull(jns_beli.nama, '') as nama_beli
                            from	jns_beli with (nolock)
                            where	jns_beli.kd_beli='".config('constants.api.shopee.kode_beli')."' and
                                    jns_beli.companyid='".$request->get('companyid')."'
                        )	jns_beli on marketplace.marketplace=jns_beli.marketplace
                        left join
                        (
                            select	top 1 'SHOPEE' as marketplace,
                                    isnull(lokasi.kd_lokasi, '') as kode_lokasi,
                                    isnull(lokasi.ket, '') as nama_lokasi
                            from	lokasi with (nolock)
                            where	lokasi.kd_lokasi='".config('constants.api.shopee.kode_lokasi')."' and
                                    lokasi.companyid='".$request->get('companyid')."'
                        )	lokasi on marketplace.marketplace=lokasi.marketplace
                        left join
                        (
                            select	top 1 'SHOPEE' as marketplace,
                                    isnull(ekspedisi_online_detail.kd_ekspedisi, '') as kode_ekspedisi,
                                    isnull(ekspedisi_online.nm_ekspedisi, '') as nama_ekspedisi
                            from
                            (
                                select	top 1 kd_ekspedisi, marketplace_id
                                from	ekspedisi_online_detail
                                where	ekspedisi_online_detail.marketplace_id='".$data_shopee[0]->logistics->id."'
                            )	ekspedisi_online_detail
                                    inner join ekspedisi_online with (nolock) on
                                        ekspedisi_online_detail.kd_ekspedisi=ekspedisi_online.kd_ekspedisi
                        )   ekspedisi on marketplace.marketplace=ekspedisi.marketplace";

                $result = DB::select($sql);

                $kode_sales = '';
                $nama_sales = '';
                $kode_dealer = '';
                $nama_dealer = '';
                $kode_beli = '';
                $nama_beli = '';
                $kode_lokasi = '';
                $nama_lokasi = '';
                $kode_ekspedisi = '';
                $nama_ekspedisi = '';
                $sub_total = 0;

                foreach($result as $data) {
                    $nomor_faktur = strtoupper(trim($request->get('companyid'))).Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')));
                    $tanggal_faktur = Carbon::now()->format('Y-m-d');
                    $nomor_pof = 'POF'.Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')));
                    $kode_tpc = '14';
                    $umur_faktur = 0;
                    $tanggal_akhir_faktur = Carbon::now()->format('Y-m-d');
                    $rh = 'R';
                    $bo = 'T';
                    $keterangan = $data_shopee[0]->order_id;

                    $kode_sales = strtoupper(trim($data->kode_sales));
                    $nama_sales = strtoupper(trim($data->nama_sales));
                    $kode_dealer = strtoupper(trim($data->kode_dealer));
                    $nama_dealer = strtoupper(trim($data->nama_dealer));
                    $kode_beli = strtoupper(trim($data->kode_beli));
                    $nama_beli = strtoupper(trim($data->nama_beli));
                    $kode_lokasi = strtoupper(trim($data->kode_lokasi));
                    $nama_lokasi = strtoupper(trim($data->nama_lokasi));
                    $kode_ekspedisi = strtoupper(trim($data->kode_ekspedisi));
                    $nama_ekspedisi = strtoupper(trim($data->nama_ekspedisi));
                }

                $sql = "select  isnull(part.shopee_id, 0) as product_id,
                                isnull(part.kd_part, '') as part_number,
                                isnull(part.ket, '') as nama_part,
                                isnull(part.het, 0) as het,
                                iif(isnull(stlokasi.jumlah, 0) - isnull(stlokasi.min, 0) - isnull(stlokasi.in_transit, 0) < 0, 0,
                                    isnull(stlokasi.jumlah, 0) - isnull(stlokasi.min, 0) - isnull(stlokasi.in_transit, 0)) as stock
                        from
                        (
                            select  part.companyid, part.kd_part, part.ket, part.shopee_id, part.het
                            from    part with (nolock)
                            where   part.companyid='".$request->get('companyid')."' and
                                    part.shopee_id in (".$data_product_id_marketplace.")
                        )   part
                                left join stlokasi with (nolock) on part.kd_part=stlokasi.kd_part and
                                            stlokasi.kd_lokasi='".config('constants.api.shopee.kode_lokasi')."' and
                                            part.companyid=stlokasi.companyid";

                $result = DB::select($sql);

                $data_internal = new Collection();
                $data_detail_order = new Collection();

                foreach($result as $data) {
                    $data_internal->push((object) [
                        'product_id'    => strtoupper(trim($data->product_id)),
                        'part_number'   => strtoupper(trim($data->part_number)),
                        'nama_part'     => strtoupper(trim($data->nama_part)),
                        'het'           => (double)$data->het,
                        'stock'         => (double)$data->stock,
                    ]);
                }

                foreach($data_product_shopee as $detail_product_shopee) {
                    if(empty(($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->part_number)) {
                        $nama_part = 'PART NUMBER TIDAK TERHUBUNG';
                    } else {
                        $nama_part = ($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->nama_part;
                    }

                    if(empty(($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->part_number)) {
                        $stock = 0;
                    } else {
                        $stock = ($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->stock;
                    }

                    if(empty(($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->part_number)) {
                        $het = 0;
                    } else {
                        $het = (double)($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->het;
                    }

                    $jml_jual = (double)$detail_product_shopee->model_quantity_purchased;

                    if((double)$het > (double)$detail_product_shopee->model_discounted_price) {
                        $selisih = (double)$het - (double)$detail_product_shopee->model_discounted_price;
                        $disc_detail = round(($selisih / (double)$het) * 100);
                    } else {
                        $disc_detail = 0;
                    }
                    $total = ((double)$het * (double)$jml_jual) - round((((double)$het * (double)$jml_jual) * (double)$disc_detail) / 100);
                    $sub_total = (double)$sub_total + (double)$total;

                    $keterangan_detail = '';

                    if(empty(($data_internal->where('product_id', trim($detail_product_shopee->item_id))->first())->nama_part)) {
                        $keterangan_detail = 'PART NUMBER TIDAK TERHUBUNG';
                    } else {
                        if((double)$jml_jual > (double)$stock) {
                            $keterangan_detail = 'JUMLAH STOCK TIDAK MENCUKUPI';
                        }
                    }


                    $data_detail_order->push((object) [
                        'pictures'      => $detail_product_shopee->pictures,
                        'nomor_faktur'  => $nomor_faktur,
                        'part_number'   => $detail_product_shopee->item_sku,
                        'nama_part'     => $nama_part,
                        'jml_order'     => (double)$detail_product_shopee->model_quantity_purchased,
                        'jml_jual'      => (double)$jml_jual,
                        'stock'         => (double)$stock,
                        'harga'         => (double)$het,
                        'disc_detail'   => (double)$disc_detail,
                        'total_detail'  => (double)$total,
                        'keterangan'    => trim($keterangan_detail),
                    ]);
                }

                $data_faktur->push((object) [
                    'nomor_faktur'      => $nomor_faktur,
                    'tanggal'           => $tanggal_faktur,
                    'nomor_pof'         => $nomor_pof,
                    'lokasi'            => ((object)[
                        'kode'          => $kode_lokasi,
                        'nama'          => $nama_lokasi
                    ]),
                    'jenis_beli'        => ((object)[
                        'kode'          => $kode_beli,
                        'keterangan'    => $nama_beli
                    ]),
                    'salesman'          => ((object)[
                        'kode'          => $kode_sales,
                        'nama'          => $nama_sales
                    ]),
                    'dealer'            => ((object)[
                        'kode'          => $kode_dealer,
                        'nama'          => $nama_dealer
                    ]),
                    'ekspedisi'         => ((object)[
                        'kode'          => $kode_ekspedisi,
                        'nama'          => $nama_ekspedisi
                    ]),
                    'kode_tpc'          => $kode_tpc,
                    'jatuh_tempo'       => ((object)[
                        'umur_faktur'   => (double)$umur_faktur,
                        'tanggal'       => trim($tanggal_akhir_faktur)
                    ]),
                    'status'            => ((object)[
                        'rh'            => strtoupper(trim($rh)),
                        'bo'            => strtoupper(trim($bo)),
                    ]),
                    'keterangan'        => trim($keterangan),
                    'total'             => ((object)[
                        'sub_total'     => (double)$sub_total,
                        'disc_header'   => 0,
                        'disc_header_rp'=> 0,
                        'disc_rp'       => 0,
                        'disc_rp1'      => 0,
                        'total'         => (double)$sub_total,
                    ]),
                    'detail'            => $data_detail_order
                                            ->where('nomor_faktur', $nomor_faktur)
                                            ->values()
                                            ->all()
                ]);
            }

            $jumlah_faktur = 0;
            $total_faktur_amount = 0;

            foreach($data_faktur as $data) {
                $jumlah_faktur = (double)$jumlah_faktur + 1;
                $total_faktur_amount = (double)$total_faktur_amount + (double)$data->total->total;
            }

            $data = [
                'shopee'            => $data_shopee->first(),
                'faktur'            => [
                    'status'        => (int)$status_faktur,
                    'jumlah_faktur' => (int)$jumlah_faktur,
                    'total_amount'  => (double)$total_faktur_amount,
                    'list'          => $data_faktur
                ],
            ];

            return Response::responseSuccess('success', $data);
        } catch (\Exception $exception) {
            return Response::responseError($request->get('user_id'), 'API', Route::getCurrentRoute()->action['controller'],
                        $request->route()->getActionMethod(), $exception->getMessage(), $request->get('companyid'));
        }
    }

    public function prosesFaktur(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'nomor_invoice'     => 'required',
                'tanggal'           => 'required',
                'companyid'         => 'required',
                'user_id'           => 'required',
            ]);

            if($validate->fails()) {
                return Response::responseWarning("Pilih nomor invoice terlebih dahulu");
            }

            $sql = DB::table('faktur')->lock('with (nolock)')
                    ->selectRaw("isnull(faktur.no_faktur, '') as nomor_faktur")
                    ->where('faktur.ket', strtoupper(trim($request->get('nomor_invoice'))))
                    ->where('faktur.companyid', strtoupper(trim($request->get('companyid'))))
                    ->first();

            if(!empty($sql->nomor_faktur)) {
                return Response::responseWarning("Nomor Invoice ".strtoupper(trim($request->get('nomor_invoice'))).
                            " sudah terdaftar di nomor faktur <strong>".strtoupper(trim($sql->nomor_faktur))."</strong>");
            }

            // ==========================================================================
            // CEK TANGGAL FAKTUR TERAKHIR DAN TANGGAL CLOSSING
            // ==========================================================================
            $sql = "select	isnull(company.companyid, '') as companyid,
                            isnull(stsclose.close_mkr, '') as close_mkr,
                            isnull(faktur.no_faktur, '') as nomor_faktur,
                            isnull(faktur.tgl_faktur, '') as tanggal_faktur
                    from
                    (
                        select	top 1 company.companyid
                        from	company with (nolock)
                        where	company.companyid='".$request->get('companyid')."'
                    )	company
                    left join
                    (
                        select	top 1 stsclose.companyid, stsclose.close_mkr
                        from	stsclose with (nolock)
                        where	stsclose.companyid='".$request->get('companyid')."'
                    )	stsclose on company.companyid=stsclose.companyid
                    left join
                    (
                        select	top 1 faktur.companyid, faktur.no_faktur, faktur.tgl_faktur
                        from	faktur with (nolock)
                        where	faktur.companyid='".$request->get('companyid')."'
                        order by faktur.tgl_faktur desc
                    )	faktur on company.companyid=faktur.companyid";

            $result = DB::select($sql);

            $jumlah_data = 0;

            foreach($result as $data) {
                $jumlah_data = (double)$jumlah_data + 1;

                if(strtotime($request->get('tanggal')) <= strtotime($data->close_mkr)) {
                    return Response::responseWarning("Tanggal yang ada pilih harus lebih besar dari tanggal clossing.".
                            "<br>Tanggal Clossing : <strong>".trim($data->close_mkr)."</strong>");
                }

                if(strtotime($request->get('tanggal')) < strtotime($data->tanggal_faktur)) {
                    return Response::responseWarning("Tanggal yang ada pilih tidak boleh lebih kecil dari tanggal faktur terakhir.".
                            "<br>Tanggal faktur terakhir : <strong>".trim($data->tanggal_faktur)."</strong>".
                            "<br>Nomor faktur terakhir : <strong>".strtoupper(trim($data->nomor_faktur))."</strong>");
                }
            }

            if((double)$jumlah_data <= 0) {
                return Response::responseWarning("Data company masih belum disetting, Hubungi IT Programmer");
            }

            $authorization = $request->header('Authorization');
            $token = explode(" ", $authorization);
            $auth_token = trim($token[1]);

            $token_shopee = '';

            $sql = DB::table('user_api_office')->lock('with (nolock)')
                    ->selectRaw("isnull(user_api_office.shopee_token, '') as shopee_token,
                                isnull(user_api_office.user_id, '') as user_id")
                    ->where('user_api_office.office_token', $auth_token)
                    ->orderByRaw("isnull(user_api_office.id, 0) desc")
                    ->first();

            if(empty($sql->shopee_token) || trim($sql->shopee_token) == '') {
                return Response::responseWarning('Token shopee tidak ditemukan, lakukan logout kemudian login kembali');
            } else {
                $token_shopee = $sql->shopee_token;
            }

            // ==========================================================================
            // CEK KONEKSI API SHOPEE
            // ==========================================================================
            $responseShopee = ServiceShopee::GetShopInfo(trim($token_shopee));
            $statusServer = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusServer == 0) {
                $authorization = $request->header('Authorization');
                $token = explode(" ", $authorization);
                $auth_token = trim($token[1]);

                $responseUpdateToken = UpdateToken::shopee($auth_token);

                if($responseUpdateToken->status == 1) {
                    $token_shopee = $responseUpdateToken->data->token;
                } else {
                    return Response::responseWarning($responseUpdateToken->message);
                }
            }

            // ==========================================================================
            // AMBIL DATA SHOPEE
            // ==========================================================================
            $responseShopee = ServiceShopee::GetOrderDetail(trim($token_shopee), trim($request->get('nomor_invoice')));
            $statusResponseShopee = (empty(json_decode($responseShopee)->error)) ? 1 : 0;

            if($statusResponseShopee == 0) {
                return Response::responseWarning(json_decode($responseShopee)->error);
            }

            $dataShopee = json_decode($responseShopee)->response;
            $data_product_marketplace = new Collection();

            $data_product_id_marketplace = '';
            $kode_ekspedisi_shopee = '';
            $nama_ekspedisi_shopee = '';

            foreach($dataShopee->order_list as $data) {
                $nama_ekspedisi_shopee = $data->shipping_carrier;

                $responseChannelList = ServiceShopee::GetChannelList(trim($token_shopee));
                $statusResponseChannelList = (empty(json_decode($responseChannelList)->error)) ? 1 : 0;

                if($statusResponseChannelList == 1) {
                    $dataChannelList = json_decode($responseChannelList)->response;

                    $data_logistics = collect($dataChannelList->logistics_channel_list);
                    $data_ekspedisi = $data_logistics
                                        ->where('logistics_channel_name', $nama_ekspedisi_shopee)
                                        ->first();

                    if(empty($data_ekspedisi->logistics_channel_id)) {
                        return Response::responseWarning('Gagal mengambil nilai kode ekspedisi, coba lagi');
                    } else {
                        $kode_ekspedisi_shopee = $data_ekspedisi->logistics_channel_id;
                    }
                } else {
                    return Response::responseWarning(json_decode($responseShopee)->error);
                }

                foreach($data->item_list as $item) {
                    if(strtoupper(trim($data_product_id_marketplace)) == '') {
                        $data_product_id_marketplace = "'".$item->item_id."'";
                    } else {
                        $data_product_id_marketplace .= ','."'".$item->item_id."'";
                    }

                    $data_product_marketplace->push((object) [
                        'id'        => $item->item_id,
                        'sku'       => $item->item_sku,
                        'name'      => $item->item_name,
                        'quantity'  => $item->model_quantity_purchased,
                        'price'     => $item->model_discounted_price,
                        'sub_total' => (double)$item->model_discounted_price * (double)$item->model_quantity_purchased,
                        'pictures'  => $item->image_info->image_url
                    ]);
                }
            }
            // ==========================================================================
            // CARI PRODUCT ID SHOPEE
            // ==========================================================================
            $sql = "select	isnull(part.kd_part, '') as part_number, isnull(part.shopee_id, 0) as product_id,
                            isnull(part.ket, '') as nama_part, isnull(part.het, 0) as het,
                            isnull(part.hrg_pokok, 0) as harga_pokok, isnull(part.jml1dus, 0) as jml1dus,
                            isnull(part.kode, '') as kode, isnull(stlokasi.kd_lokasi, '') as kode_lokasi,
                            isnull(lokasi.rakdefa, '') as kode_rak,
                            iif(isnull(stlokasi.jumlah, 0) - isnull(stlokasi.min, 0) - isnull(stlokasi.in_transit, 0) < 0, 0,
                                isnull(stlokasi.jumlah, 0) - isnull(stlokasi.min, 0) - isnull(stlokasi.in_transit, 0)) as stock
                    from
                    (
                        select	part.companyid, part.kd_part, part.shopee_id, part.ket, part.het,
                                part.hrg_pokok, part.jml1dus, part.kode
                        from	part with (nolock)
                        where	part.shopee_id in (".$data_product_id_marketplace.") and
                                part.companyid=?
                    )	part
                            left join stlokasi with (nolock) on part.kd_part=stlokasi.kd_part and
                                    stlokasi.kd_lokasi='".config('constants.api.shopee.kode_lokasi')."' and
                                    part.companyid=stlokasi.companyid
                            left join lokasi with (nolock) on lokasi.kd_lokasi='".config('constants.api.shopee.kode_lokasi')."' and
                                        part.companyid=lokasi.companyid";

            $result = DB::select($sql, [ $request->get('companyid') ]);

            $jumlah_item_parts = 0;
            $data_internal = new Collection();

            foreach($result as $data) {
                $jumlah_item_parts = (double)$jumlah_item_parts + 1;

                $data_internal->push((object) [
                    'product_id'    => strtoupper(trim($data->product_id)),
                    'part_number'   => strtoupper(trim($data->part_number)),
                    'nama_part'     => strtoupper(trim($data->nama_part)),
                    'het'           => (double)$data->het,
                    'harga_pokok'   => (double)$data->harga_pokok,
                    'jml1dus'       => (double)$data->jml1dus,
                    'kode'          => trim($data->kode),
                    'kode_lokasi'   => strtoupper(trim($data->kode_lokasi)),
                    'kode_rak'      => strtoupper(trim($data->kode_rak)),
                    'stock'         => (double)$data->stock,
                ]);
            }

            if((double)$jumlah_item_parts <= 0) {
                return Response::responseWarning('Data product id tidak terdaftar di database internal');
            }

            foreach($data_product_marketplace as $data_marketplace) {
                if(empty(($data_internal->where('product_id', trim($data_marketplace->id))->first())->part_number)) {
                    return Response::responseWarning('ProductID pada part number <strong>'.trim($data_marketplace->sku).'</strong> ',
                                'masih belum terdaftar di database internal');
                } else {
                    $part_number_internal = ($data_internal->where('product_id', trim($data_marketplace->id))->first())->part_number;

                    if(strtoupper(trim($data_marketplace->sku)) != strtoupper(trim($part_number_internal))) {
                        return Response::responseWarning('ProductID <strong>'.trim($data_marketplace->id).'</strong> '.
                                'sku shopee dan part number internal tidak sama.'.
                                '<br>SKU Shopee : <strong>'.trim($data_marketplace->sku).'</strong>'.
                                '<br>Part Number Internal : <strong>'.trim($part_number_internal)).'</strong>';
                    } else {
                        $jml_stock = (double)($data_internal->where('product_id', trim($data_marketplace->id))->first())->stock;
                        $jml_jual = (double)$data_marketplace->quantity;

                        if((double)$jml_stock <= 0) {
                            return Response::responseWarning('ProductID pada part number <strong>'.trim($data_marketplace->sku).'</strong> '.
                                'stock lokasi <strong>'.strtoupper(trim(config('constants.api.shopee.kode_lokasi'))).' : '.$jml_stock.'</strong>');
                        } else {
                            if((double)$jml_jual > (double)$jml_stock) {
                                return Response::responseWarning('ProductID pada part number <strong>'.trim($data_marketplace->sku).'</strong> '.
                                    'stock lokasi <strong>'.strtoupper(trim(config('constants.api.shopee.kode_lokasi'))).'</strong> '.
                                    'tidak mencukupi untuk memenuhi transaksi ini.
                                    <br><strong>Permintaan : '.$jml_jual.'</strong>
                                    <br><strong>Stock : '.$jml_stock.'</strong>');
                            }
                        }
                    }
                }
            }

            $data_detail_faktur_temp = new Collection();

            foreach($data_internal as $data_internal) {
                $data_detail_faktur_temp->push((object) [
                    'product_id'    => $data_internal->product_id,
                    'part_number'   => $data_internal->part_number,
                    'nama_part'     => $data_internal->nama_part,
                    'het'           => (double)$data_internal->het,
                    'harga_pokok'   => (double)$data_internal->harga_pokok,
                    'jml1dus'       => (double)$data_internal->jml1dus,
                    'kode'          => trim($data_internal->kode),
                    'kode_lokasi'   => strtoupper(trim($data_internal->kode_lokasi)),
                    'kode_rak'      => strtoupper(trim($data_internal->kode_rak)),
                    'stock'         => (double)$data_internal->stock,
                    'quantity'      => (double)($data_product_marketplace->where('id', trim($data_internal->product_id))->first())->quantity,
                    'price'         => (double)($data_product_marketplace->where('id', trim($data_internal->product_id))->first())->price,
                ]);
            }

            $kode_key = strtoupper(trim($request->get('companyid'))).Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')).Str::random(3));
            $nomor_pof = 'POF'.Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')));
            $total_baris = 0;
            $baris1 = 0;
            $baris2 = 0;
            $data_faktur_header_temp = [];
            $data_faktur_detail_temp = [];

            $kode_key_temp = '';
            $kode_key_list = '';
            $data_kode_key_temp = new Collection();

            foreach($data_detail_faktur_temp as $detail_faktur_temp) {
                // ===================================================================
                // CEK JUMLAH BARIS
                // ===================================================================
                $jml1dus = ((double)$detail_faktur_temp->jml1dus <= 0) ? 1 : (double)$detail_faktur_temp->jml1dus;
                $quantity = (double)$detail_faktur_temp->quantity;

                $baris1 = (double)$quantity / (double)$jml1dus;
                $baris2 = (double)$quantity % (double)$jml1dus;

                $hasil1 = ((double)$baris1 >= 1) ? 1 : (double)$baris1;
                $hasil2 = ((double)$baris2 >= 1) ? 1 : (double)$baris2;

                $hasil_baris = (double)$hasil1 + (double)$hasil2;


                // ==========================================================================
                // HITUNG JUMLAH BARIS FAKTUR
                // ==========================================================================
                if(((double)$total_baris + (double)$hasil_baris) > 11) {
                    $total_baris = 0;
                    $kode_key = strtoupper(trim($request->get('companyid'))).Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')).Str::random(3));
                    $nomor_pof = 'POF'.Carbon::now()->format('Ymdhis').strtoupper(trim($request->get('user_id')));
                }

                $total_baris = (double)$total_baris + (double)$hasil_baris;

                // ===================================================================
                // CEK HARGA DAN DISKON
                // ===================================================================
                $price = (double)$detail_faktur_temp->price;
                $jml_order = (double)$detail_faktur_temp->quantity;
                $jml_jual = (double)$detail_faktur_temp->quantity;
                $het = (double)$detail_faktur_temp->het;

                if((double)$het > (double)$price) {
                    $selisih = (double)$het - (double)$price;
                    $disc_detail = ((double)$selisih / (double)$het) * 100;
                } else {
                    $disc_detail = 0;
                }
                $total = ((double)$het * (double)$jml_jual) - round((((double)$het * (double)$jml_jual) * (double)$disc_detail) / 100);

                // ===================================================================
                // ISI DATA DETAIL FAKTUR TEMP
                // ===================================================================
                $data_faktur_detail_temp[] = array(
                    'kd_key'        => strtoupper(trim($kode_key)),
                    'no_faktur'     => strtoupper(trim($kode_key)),
                    'no_pof'        => strtoupper(trim($nomor_pof)),
                    'kd_part'       => $detail_faktur_temp->part_number,
                    'nm_part'       => $detail_faktur_temp->nama_part,
                    'kd_lokasi'     => strtoupper(trim($detail_faktur_temp->kode_lokasi)),
                    'kd_rak'        => strtoupper(trim($detail_faktur_temp->kode_rak)),
                    'jml_order'     => (double)$jml_order,
                    'jml_jual'      => (double)$jml_jual,
                    'harga'         => (double)$het,
                    'disc1'         => (double)$disc_detail,
                    'jumlah'        => (double)$total,
                    'hrg_pokok'     => (double)$detail_faktur_temp->harga_pokok,
                    'het'           => (double)$detail_faktur_temp->het,
                    'kode'          => trim($detail_faktur_temp->kode),
                    'companyid'     => strtoupper(trim($request->get('companyid'))),
                    'usertime'      => Carbon::now()->format('d-m-Y=h:i:s').':'.rand(100, 999).'='.strtoupper(trim($request->get('user_id')))
                );

                // ===================================================================
                // AMBIL KEY FAKTUR TEMP
                // ===================================================================
                if(strtoupper(trim($kode_key)) != strtoupper(trim($kode_key_temp))) {
                    $kode_key_temp = $kode_key;
                    $kode_key_temp = $kode_key;

                    if(trim($kode_key_list) == '') {
                        $kode_key_list = "'".strtoupper(trim($kode_key_temp))."'";
                    } else {
                        $kode_key_list .= ",'".strtoupper(trim($kode_key_temp))."'";
                    }

                    $data_kode_key_temp->push((object) [
                        'kd_key' => $kode_key_temp,
                        'no_pof' => $nomor_pof
                    ]);
                }
            }

            // ===================================================================
            // SELECT DATA DEFAULT FAKTUR SHOPEE
            // ===================================================================
            $sql = "select	isnull(company.companyid, '') as companyid,
                            isnull(stsclose.tanggal_faktur, '') as tanggal_faktur,
                            isnull(jns_beli.kd_beli, '') as kode_beli, isnull(jns_beli.nama, '') as nama_beli,
                            isnull(salesman.kd_sales, '') as kode_sales, isnulL(salesman.nm_sales, '') as nama_sales,
                            isnull(dealer.kd_dealer, '') as kode_dealer, isnull(dealer.nm_dealer, '') as nama_dealer,
                            isnull(ekspedisi.kd_ekspedisi, '') as kode_ekspedisi, isnull(ekspedisi.nm_ekspedisi, '') as nama_ekspedisi,
                            convert(varchar(10), getdate(), 105) + '=' + convert(varchar(8), getdate(), 114) + '=' +
                                'SUMA-HONDA.ID' + '=' + '".strtoupper(trim($request->get('user_id')))."' as usertime
                    from
                    (
                        select	top 1 company.companyid
                        from	company with (nolock)
                        where	company.companyid='".strtoupper(trim($request->get('companyid')))."'
                    )	company
                    left join
                    (
                        select	top 1 stsclose.companyid,
                                iif(stsclose.close_mkr >= convert(varchar(10), '".$request->get('tanggal')."', 120),
                                    convert(varchar(10), dateadd(day, 1, getdate()), 120),
                                    '".$request->get('tanggal')."') as tanggal_faktur
                        from	stsclose with (nolock)
                        where	stsclose.companyid='".strtoupper(trim($request->get('companyid')))."'
                    )	stsclose on company.companyid=stsclose.companyid
                    left join
                    (
                        select	top 1 salesman.companyid, salesman.kd_sales, salesman.nm_sales
                        from	salesman with (nolock)
                        where	salesman.companyid='".strtoupper(trim($request->get('companyid')))."' and
                                salesman.kd_sales='".strtoupper(trim(config('constants.api.shopee.kode_sales')))."'
                    )	salesman on company.companyid=salesman.companyid
                    left join
                    (
                        select	top 1 dealer.companyid, dealer.kd_dealer, dealer.nm_dealer
                        from	dealer with (nolock)
                        where	dealer.companyid='".strtoupper(trim($request->get('companyid')))."' and
                                dealer.kd_dealer='".strtoupper(trim(config('constants.app.shopee.kode_dealer')))."'
                    )	dealer on company.companyid=dealer.companyid
                    left join
                    (
                        select	top 1 jns_beli.companyid, jns_beli.kd_beli, jns_beli.nama
                        from	jns_beli with (nolock)
                        where	jns_beli.companyid='".strtoupper(trim($request->get('companyid')))."' and
                                jns_beli.kd_beli='".strtoupper(trim(config('constants.api.shopee.kode_beli')))."'
                    )	jns_beli on company.companyid=jns_beli.companyid
                    left join
                    (

                        select	top 1 '".strtoupper(trim($request->get('companyid')))."' as companyid,
                                isnull(ekspedisi_online_detail.kd_ekspedisi, '') as kd_ekspedisi,
                                isnull(ekspedisi_online.nm_ekspedisi, '') as nm_ekspedisi
                        from
                        (
                            select	top 1 kd_ekspedisi, marketplace_id
                            from	ekspedisi_online_detail with (nolock)
                            where	ekspedisi_online_detail.marketplace_id='".strtoupper(trim($kode_ekspedisi_shopee))."' and
                                    ekspedisi_online_detail.jenis_marketplace='SHOPEE'
                        )	ekspedisi_online_detail
                                inner join ekspedisi_online with (nolock) on
                                            ekspedisi_online_detail.kd_ekspedisi=ekspedisi_online.kd_ekspedisi
                    )	ekspedisi on company.companyid=ekspedisi.companyid ";

            $result = DB::select($sql);

            $jumlah_data = 0;
            $tanggal_faktur = Carbon::now()->format('d-m-Y');
            $kode_beli = '';
            $kode_sales = '';
            $kode_dealer = '';
            $kode_ekspedisi = '';
            $usertime = '';

            foreach($result as $data_header) {
                $jumlah_data = (double)$jumlah_data + 1;

                $tanggal_faktur = $data_header->tanggal_faktur;
                $kode_beli = strtoupper(trim($data_header->kode_beli));
                $kode_sales = strtoupper(trim($data_header->kode_sales));
                $kode_dealer = strtoupper(trim($data_header->kode_dealer));
                $kode_ekspedisi = strtoupper(trim($data_header->kode_ekspedisi));
                $usertime = strtoupper(trim($data_header->usertime));

                if(strtoupper(trim($kode_beli)) == '') {
                    return Response::responseWarning('Data kode beli
                        <strong>'.strtoupper(trim(config('constants.api.shopee.kode_beli'))).'</strong> '.
                        'tidak terdaftar di database internal');
                }
                if(strtoupper(trim($kode_sales)) == '') {
                    return Response::responseWarning('Data kode sales
                        <strong>'.strtoupper(trim(config('constants.api.shopee.kode_sales'))).'</strong> '.
                        'tidak terdaftar di database internal');
                }
                if(strtoupper(trim($kode_dealer)) == '') {
                    return Response::responseWarning('Data kode dealer
                        <strong>'.strtoupper(trim(config('constants.app.shopee.kode_dealer'))).'</strong> '.
                        'tidak terdaftar di database internal');
                }
                if(strtoupper(trim($kode_ekspedisi)) == '') {
                    return Response::responseWarning('Data kode ekspedisi
                        <strong>'.strtoupper(trim(config('constants.api.shopee.kode_beli'))).'</strong> '.
                        'tidak terdaftar di database internal');
                }
            }

            if($jumlah_data <= 0) {
                return Response::responseWarning('Data company tidak ditemukan hubungi IT Programmer');
            }

            foreach($data_kode_key_temp as $data_key) {
                $data_faktur_header_temp[] = array(
                    'kd_key'            => $data_key->kd_key,
                    'no_faktur'         => $data_key->kd_key,
                    'tgl_faktur'        => $tanggal_faktur,
                    'no_pof'            => $data_key->no_pof,
                    'kd_beli'           => $kode_beli,
                    'kd_sales'          => $kode_sales,
                    'kd_mkr'            => $kode_sales,
                    'kd_dealer'         => $kode_dealer,
                    'kd_ekspedisi'      => $kode_ekspedisi,
                    'ket'               => strtoupper(trim($request->get('nomor_invoice'))),
                    'disc2'             => 0,
                    'umur_faktur'       => 0,
                    'tgl_akhir_faktur'  => $tanggal_faktur,
                    'kd_tpc'            => 14,
                    'total'             => collect($data_faktur_detail_temp)
                                            ->where('kd_key', strtoupper(trim($data_key->kd_key)))
                                            ->sum('jumlah'),
                    'rh'                => 'R',
                    'bo'                => 'T',
                    'discrp1'           => 0,
                    'companyid'         => strtoupper(trim($request->get('companyid'))),
                    'usertime'          => $usertime,
                );
            }
            // ===================================================================
            // INSERT TABLE TEMPORARY
            // ===================================================================
            DB::transaction(function () use ($data_faktur_header_temp, $data_faktur_detail_temp) {
                DB::table('faktdtltmp')->insert($data_faktur_detail_temp);
                DB::table('fakturtmp')->insert($data_faktur_header_temp);
            });

            $data_faktur_header_temp[] = array(
                'kd_key'            => $data_key->kd_key,
                'no_faktur'         => $data_key->kd_key,
                'tgl_faktur'        => $tanggal_faktur,
                'no_pof'            => $data_key->no_pof,
                'kd_beli'           => $kode_beli,
                'kd_sales'          => $kode_sales,
                'kd_mkr'            => $kode_sales,
                'kd_dealer'         => $kode_dealer,
                'kd_ekspedisi'      => $kode_ekspedisi,
                'ket'               => strtoupper(trim($request->get('nomor_invoice'))),
                'disc2'             => 0,
                'umur_faktur'       => 0,
                'tgl_akhir_faktur'  => $tanggal_faktur,
                'kd_tpc'            => 14,
                'total'             => collect($data_faktur_detail_temp)
                                        ->where('kd_key', strtoupper(trim($data_key->kd_key)))
                                        ->sum('jumlah'),
                'rh'                => 'R',
                'bo'                => 'H',
                'discrp1'           => 0,
                'companyid'         => strtoupper(trim($request->get('companyid'))),
                'usertime'          => $usertime,
            );

            $data_result_faktur = new Collection();

            // ===================================================================================
            // CEK FAKTUR KE DUA
            // ===================================================================================
            $sql_cek_faktur = DB::table('faktur')->lock('with (nolock)')
                    ->selectRaw("isnull(faktur.no_faktur, '') as nomor_faktur")
                    ->where('faktur.ket', strtoupper(trim($request->get('nomor_invoice'))))
                    ->where('faktur.companyid', strtoupper(trim($request->get('companyid'))))
                    ->first();

            if(!empty($sql_cek_faktur->nomor_faktur)) {
                return Response::responseWarning("Nomor Invoice ".strtoupper(trim($request->get('nomor_invoice'))).
                            " sudah terdaftar di nomor faktur <strong>".strtoupper(trim($sql_cek_faktur->nomor_faktur))."</strong>");
            }

            $sql = "select	isnull(fakturtmp.kd_key, '') as kd_key, isnull(fakturtmp.no_faktur, '') as no_faktur,
                            isnull(convert(varchar(10), cast(fakturtmp.tgl_faktur as date), 105), '') as tgl_faktur,
                            isnull(fakturtmp.no_pof, '') as no_pof,
                            isnull(fakturtmp.kd_beli, '') as kd_beli, isnull(fakturtmp.kd_sales, '') as kd_sales,
                            isnull(fakturtmp.kd_mkr, '') as kd_mkr, isnull(fakturtmp.kd_dealer, '') as kd_dealer,
                            isnull(fakturtmp.ket, '') as ket, isnull(fakturtmp.disc2, 0) as disc2,
                            isnull(fakturtmp.umur_faktur, 0) as umur_faktur,
                            isnull(convert(varchar(10), cast(fakturtmp.tgl_akhir_faktur as date), 105), '') as tgl_akhir_faktur,
                            isnull(fakturtmp.kd_tpc, '') as kd_tpc, isnull(fakturtmp.total, 0) as total,
                            isnull(fakturtmp.rh, '') as rh, isnull(fakturtmp.bo, '') as bo,
                            isnull(fakturtmp.discrp1, 0) as discrp1, isnull(fakturtmp.companyid, '') as companyid,
                            isnull(fakturtmp.usertime, '') as usertime, isnull(fakturtmp.kd_ekspedisi, '') as kd_ekspedisi
                    from	fakturtmp with (nolock)
                    where   fakturtmp.kd_key in (".$kode_key_list.") and
                            fakturtmp.companyid=?
                    order by fakturtmp.usertime asc";

            $result = DB::select($sql, [ $request->get('companyid') ]);

            foreach($result as $data) {
                DB::transaction(function () use ($request, $data) {
                    DB::insert('exec SP_Faktur_Simpan_New8 ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?', [
                        trim(strtoupper($data->kd_key)), trim(strtoupper($data->no_faktur)), trim(strtoupper($data->no_faktur)),
                        trim($data->tgl_faktur), trim(strtoupper($data->no_pof)), trim(strtoupper($data->kd_beli)),
                        trim(strtoupper($data->kd_sales)), trim(strtoupper($data->kd_dealer)), trim(strtoupper($data->ket)),
                        (double)$data->disc2, (double)$data->umur_faktur, trim($data->tgl_akhir_faktur), trim($data->kd_tpc),
                        trim(strtoupper($data->rh)), trim(strtoupper($data->bo)), (double)$data->discrp1, 'T', '', '', '', '', '', '',
                        trim(strtoupper($request->get('user_id'))).'=SUMAOFFICE', trim(strtoupper($data->companyid)),
                        1, 1, '', 0, trim(strtoupper(config('constants.api.shopee.kode_lokasi'))),
                        trim(strtoupper($data->kd_ekspedisi)), 0, '', ''
                    ]);

                    DB::delete('exec sp_faktdtltmpDelAll ?,?,?', [
                        trim(strtoupper($data->kd_key)), trim(strtoupper($data->companyid)), 0
                    ]);
                });

                $sql = DB::table('fakturErr')->lock('with (nolock)')
                        ->selectRaw("isnull(fakturErr.kd_key, '') as kode_key,
                                isnull(fakturErr.no_faktur, '') as nomor_faktur,
                                isnull(fakturErr.ketErr, '') as keterangan,
                                isnull(fakturErr.stsErr, 0) as status_error")
                        ->where('fakturErr.kd_key', trim(strtoupper($data->kd_key)))
                        ->where('fakturErr.companyid', trim(strtoupper($data->companyid)))
                        ->first();


                $data_result_faktur[] = [
                    'nomor_faktur'  => strtoupper(trim($sql->nomor_faktur)),
                    'status_error'  => strtoupper(trim($sql->status_error)),
                    'keterangan'    => strtoupper(trim($sql->keterangan))
                ];
            }

            return Response::responseSuccess('Data faktur berhasil disimpan dan gagal accept order shopee. Lakukan accept order secara manual.'.
                                        '<br>Nomor Invoice : <strong>'.trim($request->get('nomor_invoice')).'</strong>', $data_result_faktur);
        } catch (\Exception $exception) {
            return Response::responseError($request->get('user_id'), 'API', Route::getCurrentRoute()->action['controller'],
                        $request->route()->getActionMethod(), $exception->getMessage(), $request->get('companyid'));
        }
    }
}
