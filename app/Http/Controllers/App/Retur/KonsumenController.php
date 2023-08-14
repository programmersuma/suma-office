<?php

namespace app\Http\Controllers\App\Retur;

use App\Helpers\App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
        $responseApi = json_decode(Service::ReturKonsumenDaftar($request));
        $statusApi = $responseApi->status??0;

        if ($statusApi == 1) {
            return view(
                'layouts.retur.konsumen.index',
                [
                    'old_request' => (object)[
                        'no_retur' => $request->no_retur ?? '',
                        'per_page' => $request->per_page ?? 10,
                    ],
                    'data' => $responseApi->data,
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

        // if((!empty($request->id) && $statusApiRetur == 1 && !empty($responseApiRetur->data)) && ($responseApiRetur->data->status_approve == 1 || $responseApiRetur->data->status_end == 1)){
        //     if($responseApiRetur->data->status_approve == 1){
        //         $massege = 'Data Sudah di Approve, tidak bisa diubah';
        //     } else if($responseApiRetur->data->status_end == 1){
        //         $massege = 'Proses Retur Sudah Selesai, tidak bisa diubah';
        //     }
        //     return redirect()->route('retur.konsumen.index')->withInput()->with('failed', $massege);
        // }

        $request->merge(['option' => 'select']);
        $responseApi_cabang = OptionController::cabang($request)->getData();
        $statusApi_cabang = $responseApi_cabang->status??0;

        $responseApi_sales = OptionController::salesman($request)->getData();
        $statusApi_sales = $responseApi_sales->status??0;

        if ($statusApi_sales == 1 && $statusApi_cabang == 1 && $statusApiRetur == 1) {
            $data = [
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
            if($request->no_retur == $request->user_id){
                if(!empty($request->pc) && $request->pc == 1){
                    $rules += ['kd_cabang' => 'required'];
                    $messages += ['kd_cabang.required' => 'Kode Cabang Kososng'];
                } else {
                    $rules += ['kd_dealer' => 'required'];
                    $messages += ['kd_dealer.required' => 'kode Dealer Kososng'];
                }

                if(!empty($request->kd_part)){
                    $rules += [
                        'kd_part' => 'required',
                        'qty_retur' => 'required|numeric|min:1',
                        'sts_stock' => 'required',
                        'sts_klaim' => 'required',
                        'sts_min' => 'required',
                    ];
                    $messages += [
                        'kd_part.required' => 'Part Number Kososng',
                        'qty_retur.required' => 'QTY Claim Kososng',
                        'qty_retur.min' => 'QTY Pada Claim Minimal 1',
                        'sts_stock.required' => 'Status Stock Kososng',
                        'sts_klaim.required' => 'Status Retur Kososng',
                        'sts_min.required' => 'Status Min Kososng',
                    ];
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
            ];
            $messages += [
                'kd_part.required' => 'Part Number Tidak boleh kososng',
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