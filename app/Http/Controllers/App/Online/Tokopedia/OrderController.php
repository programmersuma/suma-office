<?php

namespace App\Http\Controllers\app\Online\Tokopedia;

use App\Helpers\ApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent as Agent;

class OrderController extends Controller
{
    public function daftarOrder(Request $request) {
        if(strtoupper(trim($request->session()->get('app_user_role_id'))) == 'MD_REQ_API') {
            return redirect()->back()->withInput()->with('failed', 'Anda tidak memiliki akses untuk membuka halaman ini');
        }
        $start_date = Carbon::now();
        $end_date = Carbon::now()->addDays(3);

        if(!empty($request->get('start_date'))) {
            $start_date = $request->get('start_date');
        }

        if(!empty($request->get('end_date'))) {
            $end_date = $request->get('end_date');
        }

        $per_page = 10;
        if(!empty($request->get('per_page')) && $request->get('per_page') != '') {
            if($request->get('per_page') == 10 || $request->get('per_page') == 25 || $request->get('per_page') == 50 || $request->get('per_page') == 100) {
                $per_page = $request->get('per_page');
            } else {
                $per_page = 10;
            }
        }

        $responseApi = ApiService::OnlineOrderTokopediaDaftar($request->get('page'), $per_page,
                        $start_date, $end_date, $request->get('status'),
                        strtoupper(trim($request->session()->get('app_user_company_id'))));
        $statusApi = json_decode($responseApi)->status;
        $messageApi =  json_decode($responseApi)->message;

        if($statusApi == 1) {
            $dataApi = json_decode($responseApi)->data;

            $data_filter = new Collection();
            $data_filter->push((object) [
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'status'        => trim($request->get('status')),
            ]);

            $view = view('layouts.online.tokopedia.orders.orders', [
                'title_menu'    => 'Orders Tokopedia',
                'data_filter'   => $data_filter->first(),
                'data_order'    => $dataApi
            ]);

            if ($request->ajax()) {
                return [
                    'status'    => $statusApi,
                    'message'   => $messageApi,
                    'data'      => Str::between($view->render(), '<!--Start List Order-->', '<!--End List Order-->')
                ];
            } else {
                return $view;
            }

        } else {
            return redirect()->back()->withInput()->with('failed', $messageApi);
        }
    }

    public function singleOrder(Request $request) {
        if(strtoupper(trim($request->session()->get('app_user_role_id'))) == 'MD_REQ_API') {
            return redirect()->back()->withInput()->with('failed', 'Anda tidak memiliki akses untuk membuka halaman ini');
        }
        $data_order = [];
        $data_filter = new Collection();
        $data_filter->push((object) [
            'nomor_invoice' => trim($request->get('nomor_invoice')),
        ]);

        if(!empty($request->get('nomor_invoice'))) {
            $responseApi = ApiService::OnlineOrderTokopediaSingle($request->get('nomor_invoice'),
                            strtoupper(trim($request->session()->get('app_user_company_id'))));
            $statusApi = json_decode($responseApi)->status;

            if($statusApi == 1) {
                $data_order = json_decode($responseApi)->data;
            }
        }

        return view ('layouts.online.tokopedia.orders.ordersingle', [
            'title_menu'    => 'Orders Tokopedia',
            'data_filter'   => $data_filter->first(),
            'data_order'    => $data_order
        ]);
    }

    public function formOrder($nomor_invoice, Request $request) {
        $responseApi = ApiService::OnlineOrderTokopediaForm($nomor_invoice,
                strtoupper(trim($request->session()->get('app_user_company_id'))),
                strtoupper(trim($request->session()->get('app_user_id'))));
        $statusApi = json_decode($responseApi)->status;
        $messageApi =  json_decode($responseApi)->message;

        if($statusApi == 1) {
            $dataApi = json_decode($responseApi)->data;

            return view ('layouts.online.tokopedia.orders.orderform', [
                'title_menu'    => 'Orders Tokopedia',
                'tanggal'       => date('Y-m-d'),
                'data'          => $dataApi
            ]);
        } else {
            return redirect()->back()->withInput()->with('failed', $messageApi);
        }
    }

    public function prosesOrder(Request $request) {
        $responseApi = ApiService::OnlineOrderTokopediaProses($request->get('nomor_invoice'),
                $request->get('tanggal'),
                strtoupper(trim($request->session()->get('app_user_company_id'))),
                strtoupper(trim($request->session()->get('app_user_id'))));
        return json_decode($responseApi, true);
    }
}
