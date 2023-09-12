<?php

namespace app\Http\Controllers\App\Reports;

use App\Helpers\App\Service;
use Illuminate\Http\Request;
use App\Exports\Packing;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\App\Option\OptionController;

class PackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->merge(['option' => 'select']);
        $responseApimeja = OptionController::MejaPackingOnline()->getData();
        $responseApipacker = OptionController::PackerPackingOnline()->getData();
        if ($responseApimeja->status == 1 && $responseApipacker->status == 1) {
            return view(
                'layouts.report.packing',
                [
                    'title_menu' => 'Report Packing',
                    'meja' => $responseApimeja->data,
                    'packer' => $responseApipacker->data
                ]
            );
        } else {
            return redirect()->back()->with('failed', 'Maaf, terjadi kesalahan. Silahkan coba lagi');
        }
    }

    public function data(Request $request)
    {
        try {
            $responseApi = Service::ReportPackingData($request);
            if (json_decode($responseApi)->status == 1) {
                return Response()->json([
                    'status'    => 1,
                    'message'   => 'success',
                    'data'      => json_decode($responseApi)->data,
                ], 200);
            } else {
                return Response()->json([
                    'status'    => 0,
                    'message'   => json_decode($responseApi)->message,
                    'data'      => ''
                ], 200);
            }
        } catch (\Exception $e) {
            return Response()->json([
                'status'    => 2,
                'message'   => 'Maaf, terjadi kesalahan. Silahkan coba lagi',
                'data'      => ''
            ], 200);
        }
    }

    public function export(Request $request){
        try {
            $responseApi = Service::ExprotReportPacking($request);
            if (json_decode($responseApi)->status == 1) {
                $data = json_decode($responseApi)->data;
                return Excel::download(new Packing($data), 'Packing.xlsx');
            } else {
                return Response()->json([
                    'status'    => 0,
                    'message'   => json_decode($responseApi)->message,
                    'data'      => ''
                ], 200);
            }
        } catch (\Exception $e) {
            return Response()->json([
                'status'    => 2,
                'message'   => 'Maaf, terjadi kesalahan. Silahkan coba lagi',
                'data'      => ''
            ], 200);
        }
    }
}
