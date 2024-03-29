<?php

namespace app\Http\Controllers\App\Retur;

use App\Helpers\App\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\App\Option\OptionController;

class KonsumenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!in_array($request->per_page, [10,50,100,500])){
            $request->merge([
                'per_page' => 10
            ]);
        }

        $request->merge(['option' => ['page']]);
        $responseApiBelum = json_decode(Service::ReturKonsumenDaftar($request));
        if (($responseApiBelum->status??0) == 1) {
            return view(
                'layouts.retur.konsumen.index',
                [
                    'request' => $request,
                    'data' => $responseApiBelum->data,
                    'title_menu' => 'Retur Konsumen',
                ]
            );
        }else {
            return redirect()->back()->withInput()->with('failed', 'Maaf terjadi kesalahan, silahkan coba lagi');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function form(Request $request)
    {
        $option = [
            'with_detail',
        ];

        if(!empty($request->id)){
            $request->merge(['no_retur' => base64_decode($request->id)]);
        } else {
            array_push($option, 'tamp');
        }
        $request->merge(['option' => $option]);
        $responseApiRetur = json_decode(Service::ReturKonsumenDaftar($request));

        $statusApiRetur = $responseApiRetur->status??0;

        $request->merge(['option' => 'select']);
        $responseApi_cabang = OptionController::cabang($request)->getData();
        $statusApi_cabang = $responseApi_cabang->status??0;

        $responseApi_sales = OptionController::salesman($request)->getData();
        $statusApi_sales = $responseApi_sales->status??0;

        if ($statusApi_sales == 1 && $statusApi_cabang == 1 && $statusApiRetur == 1) {
            $data = [
                'tamp' => in_array('tamp', $option) ? true : false,
                'sales' => $responseApi_sales->data,
                'cabang' => $responseApi_cabang->data,
                'data' => $responseApiRetur->data,
                'title_menu' => 'Retur Konsumen',
                'title_page' => 'Tambah',
            ];

            return view('layouts.retur.konsumen.form', $data);
        }else {
            return redirect()->back()->withInput()->with('failed', 'Maaf terjadi kesalahan, silahkan coba lagi');
        }
    }

    // public function formPengantian(Request $request)
    // {
    //     $option = [
    //         'with_detail',
    //     ];

    //     if(!empty($request->id)){
    //         $request->merge(['no_retur' => base64_decode($request->id)]);
    //     } else {
    //         array_push($option, 'tamp');
    //     }
    //     $request->merge(['option' => $option]);

    //     $responseApiRetur = json_decode(Service::ReturKonsumenDaftar($request));
    //     $statusApiRetur = $responseApiRetur->status??0;

    //     $request->merge(['option' => 'select']);
    //     $responseApi_cabang = OptionController::cabang($request)->getData();
    //     $statusApi_cabang = $responseApi_cabang->status??0;

    //     $responseApi_sales = OptionController::salesman($request)->getData();
    //     $statusApi_sales = $responseApi_sales->status??0;

    //     if ($statusApi_sales == 1 && $statusApi_cabang == 1 && $statusApiRetur == 1) {
    //         $data = [
    //             'sales' => $responseApi_sales->data,
    //             'cabang' => $responseApi_cabang->data,
    //             'data' => $responseApiRetur->data,
    //             'title_menu' => 'Retur Konsumen',
    //             'title_page' => 'Tambah',
    //         ];

    //         return view('layouts.retur.konsumen.form', $data);
    //     }else {
    //         return redirect()->back()->withInput()->with('failed', 'Maaf terjadi kesalahan, silahkan coba lagi');
    //     }
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $rules = [];
            $messages = [];
            // ! ------------------------------------
            // ! Jika menambahkan validasi
            if($request->tamp == "1"){
                if(!empty($request->pc) && $request->pc == 1){
                    $rules += ['kd_cabang' => 'required'];
                    $messages += ['kd_cabang.required' => 'Kode Cabang Kososng'];
                } else {
                    $rules += ['kd_dealer' => 'required'];
                    $messages += ['kd_dealer.required' => 'kode Dealer Kososng'];
                }

                $rules += [
                    'kd_part' => 'required',
                    'qty_retur' => 'required|numeric|min:1',
                    'sts_stock' => 'required',
                ];
                $messages += [
                    'kd_part.required' => 'Part Number tidak boleh kososng',
                    'qty_retur.required' => 'QTY Claim Tidak Boleh Kososng',
                    'qty_retur.min' => 'QTY Pada Claim Minimal 1',
                    'sts_stock.required' => 'Status Stock tidak boleh kososng',
                ];

                if($request->sts_stock == "1"){
                    $rules += ['sts_minimum' => 'required|in:1'];
                    $messages += ['sts_minimum.in' => 'Status Minimum hanya boleh Minimum'];
                    $rules += ['sts_klaim' => 'required|in:1,2'];
                    $messages += ['sts_klaim.in' => 'Status klaim hanya boleh Klaim ke Supplier atau Tidak Melakukan Apapun'];
                }

                if($request->sts_stock == "2"){
                    $rules += ['sts_minimum' => 'required|in:1'];
                    $messages += ['sts_minimum.in' => 'Status Minimum hanya boleh Minimum'];
                    $rules += ['sts_klaim' => 'required|in:1'];
                    $messages += ['sts_klaim.in' => 'Status Retur hanya boleh Klaim ke Supplier'];
                }

                if($request->sts_stock == "3"){
                    $rules += ['sts_minimum' => 'required|in:0,1'];
                    $messages += ['sts_minimum.in' => 'Status Minimum hanya boleh Minimum atau Tidak Minimum'];

                    if ($request->sts_minimum == "0") {
                        $rules += ['sts_klaim' => 'required|in:2'];
                        $messages += ['sts_klaim.in' => 'Status Retur hanya boleh Tidak Melakukan Apapun'];
                    } else if ($request->sts_minimum == "1") {
                        $rules += ['sts_klaim' => 'required|in:1,2'];
                        $messages += ['sts_klaim.in' => 'Status Retur hanya boleh Klaim ke Supplier atau Tidak Melakukan Apapun'];
                    }
                }
            }

            // ! megecek validasi dan menampilkan pesan error
            // ! ------------------------------------
            $validate = Validator::make($request->all(), $rules,$messages);
            if ($validate->fails()) {
                return Response()->json([
                    'status'    => 0,
                    'message'   => $validate->errors()->first(),
                    'data'      => ''
                ]);
            }

            $responseApi = json_decode(Service::ReturKonsumenSimpan($request));
            $statusApi = $responseApi->status;
            $messageApi =  $responseApi->message;
            $data = $responseApi->data;

            if ($statusApi == 1) {
                return Response()->json([
                    'status'    => 1,
                    'message'   => 'Data berhasil disimpan',
                    'data'      => $data
                ], 200);
            }else {
                return Response()->json([
                    'status'    => 0,
                    'message'   => $messageApi,
                    'data'      => $data
                ], 200);
            }
        } catch (\Throwable $th) {
            return Response()->json([
                'status'    => 2,
                'message'   => 'Maaf terjadi kesalahan, silahkan coba lagi',
                'data'      => ''
            ], 200);
        }
    }

    public function destroy(Request $request)
    {
        $rules = [
            'no_retur' => 'required',
        ];
        $messages = [
            'no_retur.required' => 'No Retur Tidak Boleh Kososng',
        ];

        if(!empty($request->kd_part)){
            $rules += [
                'kd_part' => 'required',
                'no_produksi' => 'required',
            ];
            $messages += [
                'kd_part.required' => 'Part Number Tidak boleh kososng',
                'no_produksi.required' => 'No Produksi Tidak boleh kososng',
            ];
        }

        $validate = Validator::make($request->all(), $rules,$messages);
        if ($validate->fails()) {
            return Response()->json([
                'status'    => 1,
                'message'   => $validate->errors()->first(),
                'data'      => ''
            ]);
        }

        $responseApi = json_decode(Service::ReturKonsumenDelete($request));
        $statusApi = $responseApi->status;

        if ($statusApi == 1) {
            return Response()->json([
                'status'    => 1,
                'message'   => 'Data berhasil dihapus',
                'data'      => $responseApi->data
            ], 200);
        }else {
            return Response()->json([
                'status'    => 0,
                'message'   => 'Terjadi kesalahan, silahkan cek jika data masih ada maka belum terhapus',
                'data'      => ''
            ], 200);
        }
    }
}
