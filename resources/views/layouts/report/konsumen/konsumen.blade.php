@extends('layouts.main.index')
@section('title', 'Report')
@section('subtitle', 'Konsumen')
@push('styles')
    <style>
        @media print {
            body * {
                overflow: unset !important;
            }

            #kt_aside,
            #kt_header_mobile,
            #kt_footer,
            #kt_header,
            #kt_header_title,
            #btn_filter,
            #table_list .card-footer {
                display: none !important;
            }

            #table_list {
                box-shadow: none !important;
            }

            #table_list table,
            #table_list thead,
            #table_list tbody,
            #table_list tfoot {
                border: 1px solid black;
            }

            #table_list .card-body {
                width: 100%;
                margin: 30px 0 0 0;
                padding: 0;
            }
        }

        .btn_filter:hover {
            color: #01be3a;
            cursor: pointer;
        }
    </style>
@endpush

@section('container')
    <div class="row gy-5 g-xl-8">
        <div class="card card-xl-stretch shadow" id="table_list">
            <!--begin::Card-->
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title" id="btn_filter">
                    @if (session('app_user_role_id') === 'MD_H3_MGMT')
                        <div class="dropdown d-inline-block me-2">
                            <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton1"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-arrow-down fs-4"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a id="btn_export" class="dropdown-item" href="#">EXEL</a></li>
                            </ul>
                        </div>
                    @endif
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                        data-bs-target="#filter_report">
                        <i class="bi bi-filter fs-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                        data-bs-target="#filter_urutkan">
                        <i class="bi bi-sort-down fs-1"></i> Urutkan Data
                    </button>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle border">
                        <thead class="border">
                            <tr class="fs-8 fw-bolder text-muted text-center">
                                <th rowspan="2" class="w-50px ps-3 pe-3">No</th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="tgl_input">Tgl Faktur</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="jenis_motor">Jenis Motor</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="tipe_motor">Tipe Motor</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="merek_motor">Merek Motor</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span class="text-part" id="jenis_part">Jenis
                                        Part</span></th>
                                <th rowspan="2" class="w-100px ps-3 pe-3"><span class="text-kd_part" id="kd_part">kode
                                        Part</span></th>
                                <th rowspan="2" class="w-100px ps-3 pe-3">Ket part</th>
                                <th rowspan="2" class="w-100px ps-3 pe-3" id="nama">Nama Konsumen</th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="tgl_lahir">Tgl lahir</span></th>
                                <th rowspan="2" class="w-100px ps-3 pe-3">Alamat</th>
                                <th rowspan="2" class="w-100px ps-3 pe-3" id="telepon">Telepon</th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span>Divisi</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span>Company</span></th>
                                <th rowspan="2" class="w-50px ps-3 pe-3"><span id="divisi">Lokasi</span></th>
                            </tr>
                        </thead>
                        <tbody class="border">
                            @if (!empty($data))
                                @if ($data->total == 0)
                                    <tr>
                                        <td colspan="15" class="text-center text-dark">Tidak ada data</td>
                                </tr @else @php
                                    $no = $data->from;
                                @endphp @foreach ($data->data as $key => $value)
                                    <tr class="fw-bolder fs-8 border">
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td class="text-center">{{ date('d/m/Y', strtotime($value->tgl_faktur)) }}</td>
                                        <td class="text-start">{{ $value->jenis ?? '-' }}</td>
                                        <td class="text-start">{{ $value->type ?? '-' }}</td>
                                        <td class="text-start">{{ $value->merk ?? '-' }}</td>
                                        <td class="text-start">{{ $value->ring ?? '-' }}</td>
                                        <td class="text-start">{{ $value->kd_part ?? '-' }}</td>
                                        <td class="text-start">{{ $value->ket ?? '-' }}</td>
                                        <td class="text-start">{{ $value->nama ?? '-' }}</td>
                                        <td class="text-start">
                                            {{ $value->tgl_lahir == null ? '-' : date('d/m/Y', strtotime($value->tgl_lahir)) }}
                                        </td>
                                        <td class="text-start">{{ $value->alamat ?? '-' }}</td>
                                        <td class="text-start">{{ $value->telepon ?? '-' }}</td>
                                        <td class="text-center">{{ strtoupper($value->divisi) ?? '-' }}</td>
                                        <td class="text-center">{{ strtoupper($value->CompanyId) ?? '-' }}</td>
                                        <td class="text-center">{{ strtoupper($value->kd_lokasi) ?? '-' }}</td>
                                    </tr>
                                @endforeach

                            @endif
                        @else
                            <tr>
                                <td colspan="15" class="text-center text-dark">Atur Filter terlebih dahulu</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                @if (!empty($data))
                    <div class="d-flex justify-content-between">
                        <div class="form-group">
                            <select class="form-select form-select-sm" name="per_page" id="per_page">
                                <option value="10" @if ($data->per_page == 10) selected @endif>10</option>
                                <option value="50" @if ($data->per_page == 50) selected @endif>50</option>
                                <option value="100" @if ($data->per_page == 100) selected @endif>100</option>
                                <option value="500" @if ($data->per_page == 500) selected @endif>500</option>
                            </select>
                        </div>
                        <nav aria-label="...">
                            <ul class="pagination justify-content-center">
                                @php
                                    $paginator = new Illuminate\Pagination\LengthAwarePaginator(
                                        $data->data,
                                        $data->total,
                                        $data->per_page,
                                        $data->current_page,
                                        [
                                            'path' => '#',
                                        ],
                                    );
                                @endphp
                                {{ $paginator->links() }}
                            </ul>
                        </nav>
                    </div>
                    <span class="mt-3 badge badge-success jmldta">Jumlah data :
                        {{ number_format($data->total, 0, ',') }}</span>
                @endif
            </div>
            <!--end::Card-->
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="filter_report">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 pb-15">
                            <label for="lokasi" class="form-label">Filter Lokasi : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <label for="divisi" class="form-label required">Divisi</label>
                                <select name="divisi" id="divisi" class="form-select"
                                    data-placeholder="Pilih Divisi" required>
                                    <option value="">Pilih Divisi</option>
                                    @if (!Str::contains(session('app_user_name'), 'EFO'))
                                        <option value="honda">HONDA</option>
                                    @endif
                                    <option value="fdr">FDR</option>
                                </select>
                                <label for="divisi" class="form-label required">Cabang</label>
                                <select name="company" id="company" class="form-select"
                                    data-placeholder="Pilih Company" required>
                                    <option value="">Pilih Cabang</option>
                                </select>
                                <label for="divisi" class="form-label">Lokasi</label>
                                <select name="lokasi" id="lokasi" class="form-select"
                                    data-placeholder="Pilih Lokasi">
                                    <option value="">Pilih Lokasi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 pb-15">
                            <label for="tgl_lahir" class="form-label">Filter Tanggal Transaksi : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <label for="tgl_tran" class="form-label">Range Tanggal</label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="Pick date rage" id="tgl_tran0" />
                                    <span class="input-group-text">-</span>
                                    <input class="form-control" placeholder="Pick date rage" id="tgl_tran1" />
                                </div>

                                <label for="tgl_tran" class="form-label">Bulan Tahun</label>
                                <input type="month" class="form-control" placeholder="Pick date rage" id="tgl_tran"
                                    value="" />

                                <span class="text-end text-gray-600 fs-7"><i class="required"></i> Isi inputan diatas
                                    dengan salah satu dari <b>Range Tanggal</b> atau <b>Bulan Tahun</b></span>
                            </div>
                        </div>
                        <div class="col-lg-4 pb-15">
                            <label for="tgl_lahir" class="form-label">Filter Tanggal Lahir : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <label for="tgl_lahir" class="form-label">Range Bulan Tanggal</label>
                                <div class="input-group">
                                    <select class="form-control" placeholder="Bulan" id="tgl_lahir1" />
                                    <option value="">Bulan</option>
                                    @if (!empty($bulan))
                                        @foreach ($bulan as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    @endif
                                    </select>
                                    <select class="form-control" placeholder="Tanggal" id="tgl_lahir2" />
                                    <option value="">Tanggal</option>
                                    @foreach (range(1, 31) as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                    </select>
                                    <span class="input-group-text">-</span>
                                    <select class="form-control" placeholder="Bulan" id="tgl_lahir3" />
                                    <option value="">Bulan</option>
                                    @if (!empty($bulan))
                                        @foreach ($bulan as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    @endif
                                    </select>
                                    <select class="form-control" placeholder="Tanggal" id="tgl_lahir4" />
                                    <option value="">Tanggal</option>
                                    @foreach (range(1, 31) as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <label for="tgl_lahir" class="form-label">Bulan</label>
                                <select class="form-select" placeholder="Pick date rage" id="tgl_lahir" />
                                <option value="">Pilih Bulan</option>
                                @if (!empty($bulan))
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                @endif
                                </select>
                                <span class="text-end text-gray-600 fs-7"><i class="required"></i> Isi inputan diatas
                                    dengan salah satu dari <b>Bulan Tanggal</b> atau <b>Bulan</b></span>
                            </div>
                        </div>
                        <div class="col-lg-4 pb-15">
                            <label for="produk" class="form-label">Filter Produk : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <label for="jenis_produk_HONDA" class="form-label">HONDA</label>
                                <select name="jenis_produk_HONDA" id="jenis_produk_HONDA" class="form-select"
                                    data-placeholder="Pilih Jenis Produk" disabled>
                                    <option value="">Pilih Jenis Produk</option>
                                    @if (!empty($produk->HONDA))
                                        {!! $produk->HONDA !!}
                                    @endif
                                </select>
                                <label for="jenis_produk_FDR" class="form-label">FDR</label>
                                <select name="jenis_produk_FDR" id="jenis_produk_FDR" class="form-select"
                                    data-placeholder="Pilih Jenis Produk" disabled>
                                    <option value="">Pilih Jenis Produk</option>
                                    @if (!empty($produk->FDR))
                                        {!! $produk->FDR !!}
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 pb-15">
                            <label for="kendaraan" class="form-label">Filter kendaraan : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <label for="merek_motor" class="form-label">Merek Motor</label>
                                <select name="merek_motor" id="merek_motor" class="form-select"
                                    data-placeholder="Pilih Merek Motor">
                                    <option value="">Pilih Merek Motor</option>
                                    @if (!empty($merk_motor))
                                        {!! $merk_motor !!}
                                    @endif
                                </select>
                                <label for="tipe_motor" class="form-label">Tipe Motor</label>
                                <select name="tipe_motor" id="tipe_motor" class="form-select"
                                    data-placeholder="Pilih Type Motor">
                                    <option value="">Pilih Tipe Motor</option>
                                </select>
                                <label for="jenis_motor" class="form-label">Jenis Motor</label>
                                <select name="jenis_motor" id="jenis_motor" class="form-select"
                                    data-placeholder="Pilih Jenis Motor">
                                    <option value="">Pilih Jenis Motor</option>
                                    <option value="Matic">Matic</option>
                                    <option value="Bebek">Bebek</option>
                                    <option value="Sport">Sport</option>
                                    <option value="Super Sport">Super Sport</option>
                                </select>
                                <span class="text-end text-gray-600 fs-7"><i class="required"></i> Jika type motor tidak
                                    muncul maka atur filter <b>Merek Motor</b> terlebih dahulu</span>
                            </div>
                        </div>
                        <div class="col-lg-4 pb-15">
                            <label for="part" class="form-label">Filter Lainya : </label>
                            <div class="w-100 h-100 border border-dark p-6 rounded">
                                <div id="jenis_part_selector" hidden>
                                    <label for="jenis_part" class="form-label">Ukuran ring Ban</label>
                                    <select name="jenis_part" id="jenis_part" class="form-select"
                                        data-placeholder="Pilih ukuran ring Ban">
                                        <option value="">Pilih ukuran ring Ban</option>
                                        @if (!empty($ring_ban))
                                            {!! $ring_ban !!}
                                        @endif
                                    </select>
                                </div>
                                <label for="kd_part" class="form-label text-kd_part">Kode Part</label>
                                <input class="form-control" name="kd_part" id="kd_part"
                                    placeholder="Contoh : 22535KWN901" />
                                <label for="dealer" class="form-label text-dealer">Dealer</label>
                                <div class="input-group">
                                    <input id="inputFilterDealer" name="dealer" type="text"
                                        placeholder="Semua Dealer" class="form-control" style="cursor: pointer;"
                                        value="">
                                    <button id="btnFilterPilihDealer" name="btnFilterPilihDealer"
                                        class="btn btn-icon btn-primary" type="button" role="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                <label for="konsumen" class="form-label">Kategori Konsumen</label>
                                <select class="form-select" placeholder="Pick date rage" id="konsumen" />
                                <option value="">Pilih Kategori Konsumen</option>
                                <option value="semua">Semua Konsumen</option>
                                <option value="ada">Data Konsumen diisi</option>
                                <option value="tidak">Data Konsumen tidak diisi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btn-smt">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="filter_urutkan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Urutkan Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-lg-6 pb-15">
                            <label for="urutkan_collom" class="form-label">Urutkan Berdasarkan : </label>
                            <div class="w-100 h-100">
                                <select name="urutkan_collom" id="urutkan_collom" class="form-select"
                                    data-placeholder="Pilih Urutan">
                                    <option value="">Pilih Collom</option>
                                    <option value="faktur.tgl_faktur">Tanggal Transaksi</option>
                                    <option value="fakt_dtl.kd_part" class="text-kd_part">Kode Part</option>
                                    <option value="konsumen.merk">Merek Motor</option>
                                    <option value="konsumen.type">Tipe Motor</option>
                                    <option value="konsumen.jenis">Jenis Motor</option>
                                    <option value="konsumen.nama">Nama</option>
                                    <option value="konsumen.tgl_lahir">Tanggal Lahir</option>
                                    <option value="konsumen.telepon">Telepon</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 pb-15">
                            <label for="urutkan" class="form-label">Urutan : </label>
                            <div class="w-100 h-100">
                                <select name="urutkan" id="urutkan" class="form-select"
                                    data-placeholder="Pilih Urutan">
                                    <option value="">Pilih Urutan</option>
                                    <option value="asc">Ascending</option>
                                    <option value="desc">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btn-smt">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.option.optiondealercompany')
@endsection

@push('scripts')
    <script language="JavaScript">
        @if (!empty($lokasi))
            const lokasi = @json($lokasi);
        @endif
        @if (!empty($type_motor))
            const tipemotor = @json($type_motor);
        @endif
    </script>
    <script language="JavaScript" src="{{ asset('assets/js/suma/report/konsumen/Konsumen.js') }}?v={{ time() }}">
    </script>
@endpush
