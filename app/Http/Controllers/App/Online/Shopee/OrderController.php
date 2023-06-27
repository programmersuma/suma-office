<?php

namespace app\Http\Controllers\App\Online\Shopee;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\App\ServiceShopee;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function daftarOrder(Request $request) {
        if(strtoupper(trim($request->session()->get('app_user_role_id'))) == 'MD_REQ_API') {
            return redirect()->back()->withInput()->with('failed', 'Anda tidak memiliki akses untuk membuka halaman ini');
        }
        $start_date = Carbon::now()->addDay(-2)->format('Y-m-d');
        $end_date = Carbon::now()->format('Y-m-d');

        if(!empty($request->get('start_date'))) {
            $start_date = $request->get('start_date');
        }

        if(!empty($request->get('end_date'))) {
            $end_date = $request->get('end_date');
        }

        $fields = (empty($request->get('fields'))) ? 'create_time' : $request->get('fields');

        $page_size = 10;
        if(!empty($request->get('page_size')) && $request->get('page_size') != '') {
            if($request->get('page_size') == 10 || $request->get('page_size') == 25 || $request->get('page_size') == 50) {
                $page_size = $request->get('page_size');
            } else {
                $page_size = 10;
            }
        }

        $responseApi = ServiceShopee::OrderDaftar($fields, $start_date, $end_date, $page_size,
                        $request->get('cursor'), $request->get('status'),
                        strtoupper(trim($request->session()->get('app_user_company_id'))));
        $statusApi = json_decode($responseApi)->status;
        $messageApi =  json_decode($responseApi)->message;

        if($statusApi == 1) {
            $dataApi = json_decode($responseApi)->data;

            $data_filter = new Collection();
            $data_filter->push((object) [
                'fields'        => $fields,
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'status'        => trim($request->get('status')),
            ]);

            $view = view('layouts.online.shopee.orders.orders', [
                'title_menu'    => 'Orders Shopee',
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
            $responseApi = ServiceShopee::OrderSingle($request->get('nomor_invoice'),
                            strtoupper(trim($request->session()->get('app_user_company_id'))));
            $messageApi = json_decode($responseApi)->message;
            $statusApi = json_decode($responseApi)->status;

            if($statusApi == 1) {
                $data_order = json_decode($responseApi)->data;
            } else {
                return redirect()->back()->withInput()->with('failed', $messageApi);
            }
        }

        return view ('layouts.online.shopee.orders.ordersingle', [
            'title_menu'    => 'Orders Shopee',
            'data_filter'   => $data_filter->first(),
            'data_order'    => $data_order
        ]);
    }

    public function formOrder($nomor_invoice, Request $request) {
        $responseApi = ServiceShopee::OrderForm($nomor_invoice,
                strtoupper(trim($request->session()->get('app_user_company_id'))),
                strtoupper(trim($request->session()->get('app_user_id'))));
        $statusApi = json_decode($responseApi)->status;
        $messageApi =  json_decode($responseApi)->message;

        if($statusApi == 1) {
            $dataApi = json_decode($responseApi)->data;

            return view ('layouts.online.shopee.orders.orderform', [
                'title_menu'    => 'Orders Tokopedia',
                'tanggal'       => date('Y-m-d'),
                'data'          => $dataApi
            ]);
        } else {
            return redirect()->back()->withInput()->with('failed', $messageApi);
        }
    }

    public function prosesOrder(Request $request) {
        $responseApi = ServiceShopee::OrderProses($request->get('nomor_invoice'),
                $request->get('tanggal'),
                strtoupper(trim($request->session()->get('app_user_company_id'))),
                strtoupper(trim($request->session()->get('app_user_id'))));
        return json_decode($responseApi, true);
    }

    public function prosesPickup(Request $request) {
        $responseApi = ServiceShopee::OrderPickup($request->get('nomor_invoice'),
                            strtoupper(trim($request->session()->get('app_user_company_id'))));
        return json_decode($responseApi, true);
    }
}
