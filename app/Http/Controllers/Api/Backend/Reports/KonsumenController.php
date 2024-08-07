<?php

namespace app\Http\Controllers\Api\Backend\Reports;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KonsumenController extends Controller
{
    public function daftarKonsumen(Request $request)
    {
        try {
            //!  validasi
            $validator = Validator::make($request->all(), [
                'divisi' => 'required',
                'companyid' => 'required',
            ], [
                'divisi.required' => 'Divisi harus diisi',
                'companyid.required' => 'Companyid harus diisi',
            ]);

            if ($validator->fails()) {
                return Response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first(),
                    'data'      => ''
                ], 200);
            }

            if($request->divisi == 'honda'){
                $request->merge(['db' => 'dbhonda.dbo.']);
            } else {
                $request->merge(['db' => 'dbsuma.dbo.']);
            }
            // ! end validasi
            $data = DB::table(
                function ($query) use ($request) {
                    $query->select(
                        'fakt_dtl.no_faktur',
                        'faktur.kd_dealer',
                        'faktur.tgl_faktur',
                        'fakt_dtl.kd_part',
                        'fakt_dtl.companyid',
                        'fakt_dtl.kd_lokasi'
                    )->from(DB::raw($request->db.'fakt_dtl'))
                    ->join($request->db.'faktur', function ($join) {
                        $join->on('faktur.no_faktur', 'fakt_dtl.no_faktur')
                        ->on('faktur.CompanyId', 'fakt_dtl.companyid');
                    })
                    ->where('fakt_dtl.companyid', $request->companyid);
                    if(!empty($request->tgl_transaksi)){
                        if (count(Arr::wrap($request->tgl_transaksi)) == 1) {
                            $query = $query->whereMonth('faktur.tgl_faktur', date('m', strtotime($request->tgl_transaksi[0])))
                            ->whereYear('faktur.tgl_faktur', date('Y', strtotime($request->tgl_transaksi[0])));
                        } else {
                            $query = $query->whereBetween('faktur.tgl_faktur', [$request->tgl_transaksi[0], $request->tgl_transaksi[1]]);
                        }
                    }
                    if (!empty($request->kd_lokasi)){
                        $query = $query->whereIn('fakt_dtl.kd_lokasi', Arr::wrap($request->kd_lokasi));
                    }
                    if (!empty($request->kd_part)) {
                        $query = $query->where('fakt_dtl.kd_part', 'like', '%' . $request->kd_part . '%');
                    }
                }, 'faktur'
            )
            ->select(
                'konsumen.nama',
                'konsumen.tgl_lahir',
                'konsumen.alamat',
                'konsumen.telepon',
                'konsumen.jenis',
                'konsumen.type',
                'konsumen.merk',
                'part.kd_part',
                'part.ket',
                DB::raw("CASE WHEN part.jenis = '' THEN NULL ELSE part.jenis END as ring"),
                'faktur.tgl_faktur as tgl_faktur',
                DB::raw("'".$request->divisi."' as divisi"),
                'faktur.CompanyId',
                'faktur.kd_lokasi'
            )
            ->leftJoinSub(function ($query) use ($request) {
                $query->select(
                    'companyid',
                    'no_faktur',
                    'tanggal',
                    'nik',
                    'nama',
                    'tempat_lahir',
                    'tgl_lahir',
                    'alamat',
                    'telepon',
                    'email',
                    'nopol',
                    'jenis',
                    'merk',
                    'type',
                    'tahun_motor',
                    'keterangan',
                    'mengetahui',
                    'keterangan_mengetahui'
                )->from(DB::raw($request->db.'konsumen'))
                ->where('companyid', $request->companyid);
                if(!empty($request->tgl_lahir)){
                    if (count(Arr::wrap($request->tgl_lahir)) == 1) {
                        $query = $query->whereMonth('tgl_lahir', Arr::wrap($request->tgl_lahir)[0]);
                    } else {
                        $query = $query->whereRaw('MONTH(tgl_lahir) BETWEEN ? AND ?', [date('m', strtotime($request->tgl_lahir[0])), date('m', strtotime($request->tgl_lahir[1]))])
                        ->whereRaw('DAY(tgl_lahir) BETWEEN ? AND ?', [date('d', strtotime($request->tgl_lahir[0])), date('d', strtotime($request->tgl_lahir[1]))]);
                    }
                }
                if (!empty($request->merek_motor)) {
                    $query = $query->where('merk', 'like', '%'.$request->merek_motor.'%');
                }
                if (!empty($request->tipe_motor)) {
                    $query = $query->where('type', 'like', '%'.$request->tipe_motor.'%');
                }
                if(!empty($request->jenis_motor)){
                    $query = $query->where('jenis', 'like', '%'.$request->jenis_motor.'%');
                }

                $query = $query->groupBy(
                    'companyid',
                    'no_faktur',
                    'tanggal',
                    'nik',
                    'nama',
                    'tempat_lahir',
                    'tgl_lahir',
                    'alamat',
                    'telepon',
                    'email',
                    'nopol',
                    'jenis',
                    'merk',
                    'type',
                    'tahun_motor',
                    'keterangan',
                    'mengetahui',
                    'keterangan_mengetahui'
                );
            }, 'konsumen', function ($join) {
                $join->on('faktur.no_faktur', 'konsumen.no_faktur');
            })
            ->leftJoinSub(function ($query) use ($request) {
                $query->select('*')
                ->from(DB::raw($request->db.'part'))
                ->where('CompanyId', $request->companyid);
                if (!empty($request->kd_part)) {
                    $query = $query->where('part.kd_part', 'like', '%' . $request->kd_part . '%');
                }
            }, 'part', function ($join) {
                $join->on('part.kd_part', 'faktur.kd_part');
            })
            ->leftjoin($request->db.'sub', function ($join) {
                $join->on('part.kd_sub', 'sub.kd_sub');
            })
            ->leftJoin($request->db.'produk', function ($join) {
                $join->on('sub.kd_produk', 'produk.kd_produk');
            });

            if(!empty($request->produk)){
                $data = $data->where('produk.kd_produk', $request->produk);
            }
            if(!empty($request->kategori_konsumen)){
                if ($request->kategori_konsumen == 'ada') {
                    // konsumen.nama tidak kosong
                    $data = $data->whereNotNull('konsumen.nama');
                } else if ($request->kategori_konsumen == 'tidak') {
                    // konsumen.nama kosong
                    $data = $data->whereNull('konsumen.nama');
                }
            }
            if (!empty($request->dealer)) {
                $data = $data->where('faktur.kd_dealer', $request->dealer);
            }

            $data = $data->orderBy(($request->filter['collom']??'faktur.tgl_faktur'), ($request->filter['by']??'asc'))
            ->orderBy('faktur.no_faktur', 'ASC')
            ->paginate($request->per_page);

            return Response()->json([
                'status'    => 1,
                'data'      => ['data' => $data],
                'message'   => 'success',
            ], 200);
        } catch (\Exception $e) {
            return Response()->json([
                'status'    => 0,
                'data'      => '',
                'message'   => $e->getMessage(),
            ], 200);
        }
    }

    public function exportDaftarKonsumen(Request $request){
        try {
            if($request->divisi == 'honda'){
                $request->merge(['db' => 'dbhonda.dbo.']);
            } else {
                $request->merge(['db' => 'dbsuma.dbo.']);
            }
            // ! end validasi
            $data = DB::table(
                function ($query) use ($request) {
                    $query->select(
                        'fakt_dtl.no_faktur',
                        'faktur.kd_dealer',
                        'dealer.nm_dealer',
                        'faktur.tgl_faktur',
                        'fakt_dtl.kd_part',
                        'fakt_dtl.companyid',
                        'fakt_dtl.kd_lokasi',
                        'fakt_dtl.jml_jual'
                    )->from(DB::raw($request->db.'fakt_dtl'))
                    ->join($request->db.'faktur', function ($join) {
                        $join->on('faktur.no_faktur', 'fakt_dtl.no_faktur')
                        ->on('faktur.CompanyId', 'fakt_dtl.companyid');
                    })
                    ->join($request->db.'dealer', function ($join) {
                        $join->on('dealer.kd_dealer', 'faktur.kd_dealer')
                        ->on('dealer.CompanyId', 'faktur.CompanyId');
                    })
                    ->where('fakt_dtl.companyid', $request->companyid);
                    if(!empty($request->tgl_transaksi)){
                        if (count(Arr::wrap($request->tgl_transaksi)) == 1) {
                            $query = $query->whereMonth('faktur.tgl_faktur', date('m', strtotime($request->tgl_transaksi[0])))
                            ->whereYear('faktur.tgl_faktur', date('Y', strtotime($request->tgl_transaksi[0])));
                        } else {
                            $query = $query->whereBetween('faktur.tgl_faktur', [$request->tgl_transaksi[0], $request->tgl_transaksi[1]]);
                        }
                    }
                    if (!empty($request->kd_lokasi)){
                        $query = $query->whereIn('fakt_dtl.kd_lokasi', Arr::wrap($request->kd_lokasi));
                    }
                    if (!empty($request->kd_part)) {
                        $query = $query->where('fakt_dtl.kd_part', 'like', '%' . $request->kd_part . '%');
                    }
                }, 'faktur'
            )
            ->select(
                'faktur.no_faktur',
                'faktur.tgl_faktur',
                'produk.kd_produk',
                'faktur.kd_part',
                'part.ket as nama_part',
                'part.type as type_part',
                'part.jenis as jenis_part',
                'part.kategori as kategori_part',
                'part.pattern',
                'faktur.jml_jual',
                'konsumen.tanggal as tgl_input_konsumen',
                'konsumen.nik',
                'konsumen.nama',
                'konsumen.tempat_lahir',
                'konsumen.tgl_lahir',
                'konsumen.alamat',
                'konsumen.telepon',
                'konsumen.email',
                'konsumen.nopol',
                'konsumen.jenis as jenis_motor',
                'konsumen.merk as merk_motor',
                'konsumen.type as type_motor',
                'konsumen.tahun_motor',
                'konsumen.keterangan',
                'konsumen.mengetahui',
                'konsumen.keterangan_mengetahui',
                'faktur.kd_dealer',
                'faktur.nm_dealer',
                'faktur.kd_lokasi',
                'faktur.CompanyId',
                DB::raw("'".$request->divisi."' as divisi")
            )
            ->leftJoinSub(function ($query) use ($request) {
                $query->select(
                    'companyid',
                    'no_faktur',
                    'tanggal',
                    'nik',
                    'nama',
                    'tempat_lahir',
                    'tgl_lahir',
                    'alamat',
                    'telepon',
                    'email',
                    'nopol',
                    'jenis',
                    'merk',
                    'type',
                    'tahun_motor',
                    'keterangan',
                    'mengetahui',
                    'keterangan_mengetahui'
                )->from(DB::raw($request->db.'konsumen'))
                ->where('companyid', $request->companyid);
                if(!empty($request->tgl_lahir)){
                    if (count(Arr::wrap($request->tgl_lahir)) == 1) {
                        $query = $query->whereMonth('tgl_lahir', Arr::wrap($request->tgl_lahir)[0]);
                    } else {
                        $query = $query->whereRaw('MONTH(tgl_lahir) BETWEEN ? AND ?', [date('m', strtotime($request->tgl_lahir[0])), date('m', strtotime($request->tgl_lahir[1]))])
                        ->whereRaw('DAY(tgl_lahir) BETWEEN ? AND ?', [date('d', strtotime($request->tgl_lahir[0])), date('d', strtotime($request->tgl_lahir[1]))]);
                    }
                }
                if (!empty($request->merek_motor)) {
                    $query = $query->where('merk', 'like', '%'.$request->merek_motor.'%');
                }
                if (!empty($request->tipe_motor)) {
                    $query = $query->where('type', 'like', '%'.$request->tipe_motor.'%');
                }
                if(!empty($request->jenis_motor)){
                    $query = $query->where('jenis', 'like', '%'.$request->jenis_motor.'%');
                }

                $query = $query->groupBy(
                    'companyid',
                    'no_faktur',
                    'tanggal',
                    'nik',
                    'nama',
                    'tempat_lahir',
                    'tgl_lahir',
                    'alamat',
                    'telepon',
                    'email',
                    'nopol',
                    'jenis',
                    'merk',
                    'type',
                    'tahun_motor',
                    'keterangan',
                    'mengetahui',
                    'keterangan_mengetahui'
                );
            }, 'konsumen', function ($join) {
                $join->on('faktur.no_faktur', 'konsumen.no_faktur');
            })
            ->leftJoinSub(function ($query) use ($request) {
                $query->select('*')
                ->from(DB::raw($request->db.'part'))
                ->where('CompanyId', $request->companyid);
                if (!empty($request->kd_part)) {
                    $query = $query->where('part.kd_part', 'like', '%' . $request->kd_part . '%');
                }
            }, 'part', function ($join) {
                $join->on('part.kd_part', 'faktur.kd_part')
                ->on('part.CompanyId', 'faktur.CompanyId');
            })
            ->leftjoin($request->db.'sub', function ($join) {
                $join->on('part.kd_sub', 'sub.kd_sub');
            })
            ->leftJoin($request->db.'produk', function ($join) {
                $join->on('sub.kd_produk', 'produk.kd_produk');
            });

            if(!empty($request->produk)){
                $data = $data->where('produk.kd_produk', $request->produk);
            }

            if(!empty($request->kategori_konsumen)){
                if ($request->kategori_konsumen == 'ada') {
                    // konsumen.nama tidak kosong
                    $data = $data->whereNotNull('konsumen.nama');
                } else if ($request->kategori_konsumen == 'tidak') {
                    // konsumen.nama kosong
                    $data = $data->whereNull('konsumen.nama');
                }
            }

            if (!empty($request->dealer)) {
                $data = $data->where('faktur.kd_dealer', $request->dealer);
            }

            $data = $data->orderBy(($request->filter['collom']??'faktur.tgl_faktur'), ($request->filter['by']??'asc'))
            ->orderBy('faktur.no_faktur', 'ASC')
            ->get();

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

    // public function exportDaftarKonsumen(Request $request){
    //     try {
    //         if($request->divisi == 'honda'){
    //             $request->merge(['db' => 'dbhonda.dbo.']);
    //         } else {
    //             $request->merge(['db' => 'dbsuma.dbo.']);
    //         }

    //         $data = DB::table(
    //             function ($query) use ($request) {
    //                 $query->select(
    //                     'fakt_dtl.no_faktur',
    //                     'faktur.tgl_faktur',
    //                     'fakt_dtl.kd_part',
    //                     'fakt_dtl.companyid',
    //                     'fakt_dtl.kd_lokasi',
    //                     'fakt_dtl.jml_jual'
    //                 )->from(DB::raw($request->db.'fakt_dtl'))
    //                 ->join('faktur', function ($join) {
    //                     $join->on('faktur.no_faktur', 'fakt_dtl.no_faktur')
    //                     ->on('faktur.CompanyId', 'fakt_dtl.companyid');
    //                 })
    //                 ->where('fakt_dtl.companyid', $request->companyid);
    //                 if (!empty($request->kd_lokasi)){
    //                     $query = $query->whereIn('fakt_dtl.kd_lokasi', Arr::wrap($request->kd_lokasi));
    //                 }
    //                 if (!empty($request->kd_part)) {
    //                     $query = $query->where('fakt_dtl.kd_part', 'like', '%' . $request->kd_part . '%');
    //                 }
    //             }, 'faktur'
    //         )
    //         ->select(
    //             'faktur.no_faktur',
    //             'faktur.tgl_faktur',
    //             'produk.kd_produk',
    //             'faktur.kd_part',
    //             'part.ket as nama_part',
    //             'part.type as type_part',
    //             'part.jenis as jenis_part',
    //             'part.kategori as kategori_part',
    //             'part.pattern',
    //             'faktur.jml_jual',
    //             'konsumen.tanggal as tgl_input_konsumen',
    //             'konsumen.nik',
    //             'konsumen.nama',
    //             'konsumen.tempat_lahir',
    //             'konsumen.tgl_lahir',
    //             'konsumen.alamat',
    //             'konsumen.telepon',
    //             'konsumen.email',
    //             'konsumen.nopol',
    //             'konsumen.jenis as jenis_motor',
    //             'konsumen.merk as merk_motor',
    //             'konsumen.type as type_motor',
    //             'konsumen.tahun_motor',
    //             'konsumen.keterangan',
    //             'konsumen.mengetahui',
    //             'konsumen.keterangan_mengetahui',
    //             'faktur.kd_lokasi',
    //             'faktur.CompanyId',
    //             DB::raw("'".$request->divisi."' as divisi")
    //         )->JoinSub(function ($query) use ($request) {
    //             $query->select(
    //                 'companyid',
    //                 'no_faktur',
    //                 'tanggal',
    //                 'nik',
    //                 'nama',
    //                 'tempat_lahir',
    //                 'tgl_lahir',
    //                 'alamat',
    //                 'telepon',
    //                 'email',
    //                 'nopol',
    //                 'jenis',
    //                 'merk',
    //                 'type',
    //                 'tahun_motor',
    //                 'keterangan',
    //                 'mengetahui',
    //                 'keterangan_mengetahui'
    //             )->from(DB::raw($request->db.'konsumen'))
    //             ->where('companyid', $request->companyid);
    //             if(!empty($request->tgl_transaksi)){
    //                 if (count(Arr::wrap($request->tgl_transaksi)) == 1) {
    //                     $query = $query->whereMonth('tanggal', date('m', strtotime($request->tgl_transaksi[0])))
    //                     ->whereYear('tanggal', date('Y', strtotime($request->tgl_transaksi[0])));
    //                 } else {
    //                     $query = $query->whereBetween('tanggal', [$request->tgl_transaksi[0], $request->tgl_transaksi[1]]);
    //                 }
    //             }
    //             if(!empty($request->tgl_lahir)){
    //                 if (count(Arr::wrap($request->tgl_lahir)) == 1) {
    //                     $query = $query->whereMonth('tgl_lahir', Arr::wrap($request->tgl_lahir)[0]);
    //                 } else {
    //                     $query = $query->whereRaw('MONTH(tgl_lahir) BETWEEN ? AND ?', [date('m', strtotime($request->tgl_lahir[0])), date('m', strtotime($request->tgl_lahir[1]))])
    //                     ->whereRaw('DAY(tgl_lahir) BETWEEN ? AND ?', [date('d', strtotime($request->tgl_lahir[0])), date('d', strtotime($request->tgl_lahir[1]))]);
    //                 }
    //             }
    //             if (!empty($request->merek_motor)) {
    //                 $query = $query->where('merk', 'like', '%'.$request->merek_motor.'%');
    //             }
    //             if (!empty($request->tipe_motor)) {
    //                 $query = $query->where('type', 'like', '%'.$request->tipe_motor.'%');
    //             }
    //             if(!empty($request->jenis_motor)){
    //                 $query = $query->where('jenis', 'like', '%'.$request->jenis_motor.'%');
    //             }

    //             $query = $query->groupBy(
    //                 'companyid',
    //                 'no_faktur',
    //                 'tanggal',
    //                 'nik',
    //                 'nama',
    //                 'tempat_lahir',
    //                 'tgl_lahir',
    //                 'alamat',
    //                 'telepon',
    //                 'email',
    //                 'nopol',
    //                 'jenis',
    //                 'merk',
    //                 'type',
    //                 'tahun_motor',
    //                 'keterangan',
    //                 'mengetahui',
    //                 'keterangan_mengetahui'
    //             );
    //         }, 'konsumen', function ($join) {
    //             $join->on('faktur.no_faktur', 'konsumen.no_faktur');
    //         })
    //         ->leftJoinSub(function ($query) use ($request) {
    //             $query->select('*')
    //             ->from(DB::raw($request->db.'part'))
    //             ->where('CompanyId', $request->companyid);
    //             if (!empty($request->kd_part)) {
    //                 $query = $query->where('part.kd_part', 'like', '%' . $request->kd_part . '%');
    //             }
    //         }, 'part', function ($join) {
    //             $join->on('part.kd_part', 'faktur.kd_part');
    //         }) ->leftJoinSub(function ($query) use ($request){
    //             $query->select('*')
    //             ->from(DB::raw($request->db.'sub'));
    //         }, 'sub', function ($join) {
    //             $join->on('part.kd_sub', 'sub.kd_sub');
    //         })
    //         ->leftJoinSub(function ($query) use ($request){
    //             $query->select('*')
    //             ->from(DB::raw($request->db.'produk'));
    //         }, 'produk', function ($join) {
    //             $join->on('sub.kd_produk', 'produk.kd_produk');
    //         })
    //         ->get();

    //         return Response()->json([
    //             'status'    => 1,
    //             'message'   => 'success',
    //             'data'      => $data
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return Response()->json([
    //             'status'    => 2,
    //             'message'   => 'Maaf, terjadi kesalahan. Silahkan coba lagi',
    //             'data'      => ''
    //         ], 200);
    //     }
    // }
}
