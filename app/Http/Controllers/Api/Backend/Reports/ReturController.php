<?php

namespace app\Http\Controllers\Api\Backend\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReturController extends Controller
{
    public function data(Request $request)
    {
        try {
            if(empty($request->page)){
                $request->merge(['page' => 1]);
            }

            if (!in_array($request->per_page, ['10', '50', '100', '500'])) {
                $request->merge(['per_page' => 10]);
            }

            $sql = "
                select
                    klaim.no_faktur,
                    [retur].[no_retur],
                    klaim.no_dokumen as no_klaim,
                    rtoko.no_retur as no_rtoko,
                    klaim.kd_dealer,
                    IIF(klaim.pc = '0', dealer.nm_dealer, cabang.nm_cabang) as nm_dealer,
                    [klaim].[kd_sales],
                    [klaim].[kd_part],
                    klaim.qty_klaim,
                    klaim.tgl_klaim,
                    klaim.tgl_pakai,
                    klaim.pemakaian,
                    CASE WHEN retur.ket is not null THEN SUBSTRING(retur.ket, CHARINDEX('|', retur.ket) + 1, LEN(retur.ket) - CHARINDEX('|', retur.ket)) WHEN rtoko.ket is not null THEN rtoko.ket WHEN klaim.keterangan is not null THEN klaim.keterangan ELSE null END AS keterangan,
                    CASE WHEN klaim.sts_stock = 1 THEN 'Ganti Barang' WHEN klaim.sts_stock = 2 THEN 'Stock 0' WHEN klaim.sts_stock = 3 THEN 'Retur' ELSE null END AS sts_stock,
                    CASE WHEN klaim.sts_min = 1 THEN 'IYA' ELSE 'TIDAK' END AS sts_min,
                    CASE WHEN klaim.sts_klaim = 1 THEN 'IYA' ELSE 'TIDAK' END AS sts_klaim,
                    CASE WHEN klaim.status_approve = 1 THEN 1 ELSE 0 END AS sts_approve,
                    CASE WHEN klaim.status_end = 1 THEN 1 ELSE 0 END AS sts_selesai
                from
                (
                    select
                        klaim_dtl.no_faktur,
                        [klaim].[no_dokumen],
                        [klaim_dtl].[kd_part],
                        [klaim].[pc],
                        [klaim].[kd_dealer],
                        [klaim].[kd_sales],
                        REPLACE(CONVERT(NVARCHAR(10), tgl_pakai, 105), '-', '/') as tgl_pakai,
                        REPLACE(CONVERT(NVARCHAR(10), tgl_klaim, 105), '-', '/') as tgl_klaim,
                        CONVERT(NVARCHAR(10),(DATEDIFF(DAY, klaim_dtl.tgl_pakai, klaim_dtl.tgl_klaim))) AS pemakaian,
                        sum([klaim_dtl].[qty]) as [qty_klaim],
                        [klaim_dtl].[keterangan],
                        [klaim_dtl].[sts_klaim],
                        [klaim_dtl].[sts_min],
                        [klaim_dtl].[sts_stock],
                        [klaim].[status_approve],
                        [klaim].[status_end]
                    from [klaim]
                    inner join [klaim_dtl] on [klaim_dtl].[no_dokumen] = [klaim].[no_dokumen] and [klaim_dtl].[companyid] = [klaim].[companyid]
                    where [klaim].[companyid] = '$request->companyid' and
                    CONVERT(DATE, klaim_dtl.tgl_klaim) between '{$request->tanggal[0]}' and '{$request->tanggal[1]}'";
                    if (!empty($request->kd_dealer)){
                        $sql .= " and [klaim].[kd_dealer] = '$request->kd_dealer'";
                    }
                    if (!empty($request->kd_sales)){
                        $sql .= " and [klaim].[kd_sales] = '$request->kd_sales'";
                    }
                    if ($request->kd_jenis == 1) {
                        $sql .= " and [klaim].[pc] = '0'";
                    } else if ($request->kd_jenis == 2) {
                        $sql .= " and [klaim].[pc] = '1'";
                    }
                    $sql .="
                    group by
                        klaim_dtl.no_faktur,
                        [klaim].[no_dokumen],
                        [klaim_dtl].[kd_part],
                        [klaim].[pc],
                        [klaim].[kd_dealer],
                        [klaim].[kd_sales],
                        REPLACE(CONVERT(NVARCHAR(10), tgl_pakai, 105), '-', '/'),
                        REPLACE(CONVERT(NVARCHAR(10), tgl_klaim, 105), '-', '/'),
                        CONVERT(NVARCHAR(10),(DATEDIFF(DAY, klaim_dtl.tgl_pakai, klaim_dtl.tgl_klaim))),
                        [klaim_dtl].[keterangan],
                        [klaim_dtl].[sts_klaim],
                        [klaim_dtl].[sts_min],
                        [klaim_dtl].[sts_stock],
                        [klaim].[status_approve],
                        [klaim].[status_end]
                ) klaim
                left join (
                    select kd_dealer, nm_dealer from dealer where CompanyId = '$request->companyid'
                ) dealer on dealer.kd_dealer = klaim.kd_dealer
                left join (
                    select kd_cabang, nm_cabang from cabang where CompanyId = '$request->companyid'
                ) cabang on cabang.kd_cabang = klaim.kd_dealer
                inner join (
                    select
                        no_faktur,
                        kd_part
                    from
                        fakt_dtl
                    where CompanyId = '$request->companyid'
                ) as fakt_dtl on fakt_dtl.no_faktur = klaim.no_faktur and fakt_dtl.kd_part = klaim.kd_part
                left join
                (
                    select
                        [rtoko].[no_retur],
                        [rtoko_dtl].[no_klaim],
                        [rtoko_dtl].[kd_part],
                        [rtoko_dtl].[jumlah] as [qty_rtoko],
                        [rtoko].[tanggal],
                        [rtoko].[kd_dealer],
                        [rtoko].[kd_sales],
                        [rtoko_dtl].[ket],
                        [rtoko].[CompanyId]
                    from [rtoko]
                    inner join [rtoko_dtl] on [rtoko_dtl].[no_retur] = [rtoko].[no_retur] and [rtoko_dtl].[CompanyId] = [rtoko].[CompanyId]
                    where [rtoko].[companyid] = '$request->companyid' and rtoko_dtl.no_klaim is not null
                ) as [rtoko] on [rtoko].[no_klaim] = [klaim].[no_dokumen] and [rtoko].[kd_part] = [klaim].[kd_part]
                left join
                (
                    select
                        [retur].[no_retur],
                        [retur_dtl].[no_klaim],
                        [retur].[tglretur],
                        [retur].[kd_supp],
                        [retur_dtl].[kd_part],
                        [retur_dtl].[jmlretur],
                        [retur_dtl].[ket],
                        [retur_dtl].[tgl_jwb],
                        [retur_dtl].[qty_jwb],
                        [retur_dtl].[ket_jwb],
                        [retur].[CompanyId] from [retur]
                    inner join [retur_dtl] on [retur_dtl].[no_retur] = [retur].[no_retur] and [retur_dtl].[CompanyId] = [retur].[CompanyId]
                    where [retur].[CompanyId] = '$request->companyid'
                ) as [retur] on [retur].[no_klaim] = [rtoko].[no_retur] and [retur].[kd_part] = [klaim].[kd_part]
            ";

            $data = DB::table(DB::raw("($sql) as a"))
            ->selectRaw(
                "
                    no_faktur,
                    no_retur,
                    no_klaim,
                    no_rtoko,
                    kd_dealer,
                    nm_dealer,
                    kd_sales,
                    kd_part,
                    qty_klaim,
                    IIF(sts_klaim = 'IYA', 0, null) as qty_jwb,
                    tgl_klaim,
                    tgl_pakai,
                    pemakaian,
                    keterangan,
                    sts_stock,
                    sts_min,
                    sts_klaim,
                    sts_approve,
                    sts_selesai
                "
            )
            ->orderBy('kd_dealer', 'asc')
            ->orderBy('kd_sales', 'asc')
            ->orderBy('kd_part', 'asc')
            ->orderBy('tgl_klaim', 'asc')
            ->get();

            // ! ambil data jawaban
            $dataJawab = DB::table('jwb_claim')
            ->selectRaw(
                '
                    no_klaim,
                    kd_part,
                    no_retur,
                    sum(qty_jwb) as qty_jwb
                '
            )
            ->whereIn('no_klaim', collect($data)->pluck('no_rtoko')->unique()->values()->toArray())
            ->where('sts_end', 1)
            ->where('CompanyId', $request->companyid)
            ->groupByRaw(
                '
                    no_klaim,
                    kd_part,
                    no_retur
                '
            )
            ->get();

            // ! klompokan berdasarkan no rtoko dan kd part
            $dataFilter = collect($data)->groupBy('no_rtoko')->map(function($item){
                return $item->groupBy('kd_part');
            });

            // ? ---------------------------------------------------------------------------------------------
            // ? Info : $dataFilter = [no_rtoko => [kd_part => [data]]]
            // ? Info : $item->no_klaim merupakan sama dengan no_rtoko
            // ? ---------------------------------------------------------------------------------------------

            $dataJawab->map(function($item) use ($dataFilter, $data) {
                // ! jika data filter tidak kosong
                if(!empty($dataFilter[$item->no_klaim][$item->kd_part])){
                    // ! tampung qty jawaban dari data jawaban
                    (int)$qtyJwbTamp = (int)$item->qty_jwb;
                    // ! looping data yang sudah di group by
                    foreach($dataFilter[$item->no_klaim][$item->kd_part] as $key => $value){
                            if($qtyJwbTamp >= 0){
                                // ! membandingkan pada nomer rtoko dan part apakah jumlah jawabannya lebih besar dari jumlah klaim
                                if((int)$qtyJwbTamp > (int)$value->qty_klaim){
                                    // ! jika lebih besar maka qty jawaban di isi dengan qty klaim
                                    $data
                                        ->where('no_retur', $value->no_retur)
                                        ->where('no_faktur', $value->no_faktur)
                                        ->where('no_klaim', $value->no_klaim)
                                        ->where('kd_part', $value->kd_part)
                                        ->first()->qty_jwb = (int)$value->qty_klaim;
                                    // ! qty jawaban tampungan dikurangi dengan qty klaim
                                    (int)$qtyJwbTamp = ((int)$qtyJwbTamp - (int)$value->qty_klaim);

                                    // ! jika qty jawaban sama dengan qty klaim
                                } elseif((int)$qtyJwbTamp == (int)$value->qty_klaim){
                                    // ! maka qty jawaban di isi dengan qty klaim
                                    $data
                                        ->where('no_retur', $value->no_retur)
                                        ->where('no_faktur', $value->no_faktur)
                                        ->where('no_klaim', $value->no_klaim)
                                        ->where('kd_part', $value->kd_part)
                                        ->first()->qty_jwb = (int)$value->qty_klaim;
                                    // ! qty jawaban tampungan di isi dengan 0
                                    (int)$qtyJwbTamp = 0;

                                    // ! jika qty jawaban lebih kecil dari qty klaim
                                } elseif((int)$qtyJwbTamp < (int)$value->qty_klaim){
                                    // ! maka qty jawaban di isi dengan qty jawaban tampungan
                                    $data
                                        ->where('no_retur', $value->no_retur)
                                        ->where('no_faktur', $value->no_faktur)
                                        ->where('no_klaim', $value->no_klaim)
                                        ->where('kd_part', $value->kd_part)
                                        ->first()->qty_jwb = (int)$qtyJwbTamp;
                                    // ! qty jawaban tampungan di isi dengan 0
                                    (int)$qtyJwbTamp = 0;
                                }
                            } else {
                                // ! jika qty jawaban tampungan kurang dari 0 maka qty jawaban di isi dengan 0
                                $data
                                    ->where('no_retur', $value->no_retur)
                                    ->where('no_faktur', $value->no_faktur)
                                    ->where('no_klaim', $value->no_klaim)
                                    ->where('kd_part', $value->kd_part)
                                    ->first()->qty_jwb =  0;
                            }
                    }

                }
            });

            // buat pagination untuk $data
            $paginationData = new \Illuminate\Pagination\LengthAwarePaginator(
                ($data->slice(($request->page - 1) * $request->per_page, $request->per_page)),
                $data->count(),
                $request->per_page,
                $request->page
            );



            return Response()->json([
                'status'    => 1,
                'message'   => 'success',
                'data'      => $paginationData
            ], 200);
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

            $sql = "
                select
                    klaim.no_faktur,
                    [retur].[no_retur],
                    klaim.no_dokumen as no_klaim,
                    rtoko.no_retur as no_rtoko,
                    klaim.kd_dealer,
                    IIF(klaim.pc = '0', dealer.nm_dealer, cabang.nm_cabang) as nm_dealer,
                    [klaim].[kd_sales],
                    [klaim].[kd_part],
                    klaim.qty_klaim,
                    klaim.tgl_klaim,
                    klaim.tgl_pakai,
                    klaim.pemakaian,
                    CASE WHEN retur.ket is not null THEN SUBSTRING(retur.ket, CHARINDEX('|', retur.ket) + 1, LEN(retur.ket) - CHARINDEX('|', retur.ket)) WHEN rtoko.ket is not null THEN rtoko.ket WHEN klaim.keterangan is not null THEN klaim.keterangan ELSE null END AS keterangan,
                    CASE WHEN klaim.sts_stock = 1 THEN 'Ganti Barang' WHEN klaim.sts_stock = 2 THEN 'Stock 0' WHEN klaim.sts_stock = 3 THEN 'Retur' ELSE null END AS sts_stock,
                    CASE WHEN klaim.sts_min = 1 THEN 'IYA' ELSE 'TIDAK' END AS sts_min,
                    CASE WHEN klaim.sts_klaim = 1 THEN 'IYA' ELSE 'TIDAK' END AS sts_klaim,
                    CASE WHEN klaim.status_approve = 1 THEN 1 ELSE 0 END AS sts_approve,
                    CASE WHEN klaim.status_end = 1 THEN 1 ELSE 0 END AS sts_selesai
                from
                (
                    select
                        klaim_dtl.no_faktur,
                        [klaim].[no_dokumen],
                        [klaim_dtl].[kd_part],
                        [klaim].[pc],
                        [klaim].[kd_dealer],
                        [klaim].[kd_sales],
                        CONVERT(NVARCHAR(10), tgl_pakai, 105) as tgl_pakai,
                        CONVERT(NVARCHAR(10), tgl_klaim, 105) as tgl_klaim,
                        CONVERT(NVARCHAR(10),(DATEDIFF(DAY, klaim_dtl.tgl_pakai, klaim_dtl.tgl_klaim))) AS pemakaian,
                        sum([klaim_dtl].[qty]) as [qty_klaim],
                        [klaim_dtl].[keterangan],
                        [klaim_dtl].[sts_klaim],
                        [klaim_dtl].[sts_min],
                        [klaim_dtl].[sts_stock],
                        [klaim].[status_approve],
                        [klaim].[status_end]
                    from [klaim]
                    inner join [klaim_dtl] on [klaim_dtl].[no_dokumen] = [klaim].[no_dokumen] and [klaim_dtl].[companyid] = [klaim].[companyid]
                    where [klaim].[companyid] = '$request->companyid' and
                    CONVERT(DATE, klaim_dtl.tgl_klaim) between '{$request->tanggal[0]}' and '{$request->tanggal[1]}'";
                    if (!empty($request->kd_dealer)){
                        $sql .= " and [klaim].[kd_dealer] = '$request->kd_dealer'";
                    }
                    if (!empty($request->kd_sales)){
                        $sql .= " and [klaim].[kd_sales] = '$request->kd_sales'";
                    }
                    if ($request->kd_jenis == 1) {
                        $sql .= " and [klaim].[pc] = '0'";
                    } else if ($request->kd_jenis == 2) {
                        $sql .= " and [klaim].[pc] = '1'";
                    }
                    $sql .="
                    group by
                        klaim_dtl.no_faktur,
                        [klaim].[no_dokumen],
                        [klaim_dtl].[kd_part],
                        [klaim].[pc],
                        [klaim].[kd_dealer],
                        [klaim].[kd_sales],
                        CONVERT(NVARCHAR(10), tgl_pakai, 105),
                        CONVERT(NVARCHAR(10), tgl_klaim, 105),
                        CONVERT(NVARCHAR(10),(DATEDIFF(DAY, klaim_dtl.tgl_pakai, klaim_dtl.tgl_klaim))),
                        [klaim_dtl].[keterangan],
                        [klaim_dtl].[sts_klaim],
                        [klaim_dtl].[sts_min],
                        [klaim_dtl].[sts_stock],
                        [klaim].[status_approve],
                        [klaim].[status_end]
                ) klaim
                left join (
                    select kd_dealer, nm_dealer from dealer where CompanyId = '$request->companyid'
                ) dealer on dealer.kd_dealer = klaim.kd_dealer
                left join (
                    select kd_cabang, nm_cabang from cabang where CompanyId = '$request->companyid'
                ) cabang on cabang.kd_cabang = klaim.kd_dealer
                inner join (
                    select
                        no_faktur,
                        kd_part
                    from
                        fakt_dtl
                    where CompanyId = '$request->companyid'
                ) as fakt_dtl on fakt_dtl.no_faktur = klaim.no_faktur and fakt_dtl.kd_part = klaim.kd_part
                left join
                (
                    select
                        [rtoko].[no_retur],
                        [rtoko_dtl].[no_klaim],
                        [rtoko_dtl].[kd_part],
                        [rtoko_dtl].[jumlah] as [qty_rtoko],
                        [rtoko].[tanggal],
                        [rtoko].[kd_dealer],
                        [rtoko].[kd_sales],
                        [rtoko_dtl].[ket],
                        [rtoko].[CompanyId]
                    from [rtoko]
                    inner join [rtoko_dtl] on [rtoko_dtl].[no_retur] = [rtoko].[no_retur] and [rtoko_dtl].[CompanyId] = [rtoko].[CompanyId]
                    where [rtoko].[companyid] = '$request->companyid' and rtoko_dtl.no_klaim is not null
                ) as [rtoko] on [rtoko].[no_klaim] = [klaim].[no_dokumen] and [rtoko].[kd_part] = [klaim].[kd_part]
                left join
                (
                    select
                        [retur].[no_retur],
                        [retur_dtl].[no_klaim],
                        [retur].[tglretur],
                        [retur].[kd_supp],
                        [retur_dtl].[kd_part],
                        [retur_dtl].[jmlretur],
                        [retur_dtl].[ket],
                        [retur_dtl].[tgl_jwb],
                        [retur_dtl].[qty_jwb],
                        [retur_dtl].[ket_jwb],
                        [retur].[CompanyId] from [retur]
                    inner join [retur_dtl] on [retur_dtl].[no_retur] = [retur].[no_retur] and [retur_dtl].[CompanyId] = [retur].[CompanyId]
                    where [retur].[CompanyId] = '$request->companyid'
                ) as [retur] on [retur].[no_klaim] = [rtoko].[no_retur] and [retur].[kd_part] = [klaim].[kd_part]
            ";

            $data = DB::table(DB::raw("($sql) as a"))
            ->selectRaw(
                "
                    no_faktur,
                    no_retur,
                    no_klaim,
                    no_rtoko,
                    kd_dealer,
                    nm_dealer,
                    kd_sales,
                    kd_part,
                    qty_klaim,
                    IIF(sts_klaim = 'IYA', 0, null) as qty_jwb,
                    tgl_klaim,
                    tgl_pakai,
                    pemakaian,
                    keterangan,
                    sts_stock,
                    sts_min,
                    sts_klaim,
                    sts_approve,
                    sts_selesai
                "
            )
            ->orderBy('kd_dealer', 'asc')
            ->orderBy('kd_sales', 'asc')
            ->orderBy('kd_part', 'asc')
            ->orderBy('tgl_klaim', 'asc')
            ->get();

            $dataJawab = DB::table('jwb_claim')
            ->selectRaw(
                '
                    no_klaim,
                    kd_part,
                    no_retur,
                    sum(qty_jwb) as qty_jwb
                '
            )
            ->whereIn('no_klaim', collect($data)->pluck('no_rtoko')->unique()->values()->toArray())
            ->where('sts_end', 1)
            ->where('CompanyId', $request->companyid)
            ->groupByRaw(
                '
                    no_klaim,
                    kd_part,
                    no_retur
                '
            )
            ->get();

            $dataFilter = collect($data)->groupBy('no_rtoko')->map(function($item){
                return $item->groupBy('kd_part');
            });

            $dataJawab->map(function($item) use ($dataFilter, $data) {
                if(!empty($dataFilter[$item->no_klaim][$item->kd_part])){
                    (int)$qtyJwbTamp = (int)$item->qty_jwb;
                    foreach($dataFilter[$item->no_klaim][$item->kd_part] as $key => $value){
                        if($qtyJwbTamp >= 0){
                            if((int)$qtyJwbTamp > (int)$value->qty_klaim){
                                $data
                                    ->where('no_retur', $value->no_retur)
                                    ->where('no_faktur', $value->no_faktur)
                                    ->where('no_klaim', $value->no_klaim)
                                    ->where('kd_part', $value->kd_part)
                                    ->first()->qty_jwb = (int)$value->qty_klaim;

                                (int)$qtyJwbTamp = ((int)$qtyJwbTamp - (int)$value->qty_klaim);
                            } elseif((int)$qtyJwbTamp == (int)$value->qty_klaim){
                                $data
                                    ->where('no_retur', $value->no_retur)
                                    ->where('no_faktur', $value->no_faktur)
                                    ->where('no_klaim', $value->no_klaim)
                                    ->where('kd_part', $value->kd_part)
                                    ->first()->qty_jwb = (int)$value->qty_klaim;

                                (int)$qtyJwbTamp = 0;
                            } elseif((int)$qtyJwbTamp < (int)$value->qty_klaim){
                                $data
                                    ->where('no_retur', $value->no_retur)
                                    ->where('no_faktur', $value->no_faktur)
                                    ->where('no_klaim', $value->no_klaim)
                                    ->where('kd_part', $value->kd_part)
                                    ->first()->qty_jwb = (int)$qtyJwbTamp;

                                (int)$qtyJwbTamp = 0;
                            }
                        } else {
                            $data
                                ->where('no_retur', $value->no_retur)
                                ->where('no_faktur', $value->no_faktur)
                                ->where('no_klaim', $value->no_klaim)
                                ->where('kd_part', $value->kd_part)
                                ->first()->qty_jwb =  0;
                        }
                    }
                }
            });

            return Response()->json([
                'status'    => 1,
                'message'   => 'success',
                'data'      => $data
            ], 200);
        } catch (\Exception $e) {
            return Response()->json([
                'status'    => 2,
                'message'   => 'Maaf, terjadi kesalahan. Silahkan coba lagi',
                'data'      => ''
            ], 200);
        }
    }
}
