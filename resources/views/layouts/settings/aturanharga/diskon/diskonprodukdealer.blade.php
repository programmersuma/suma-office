@extends('layouts.main.index')
@section('title','Pengaturan ')
@section('subtitle','Diskon Produk (Dealer)')
@section('container')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
@endpush
<div class="card mb-4">
    <div class="card-body">
        <!--begin::Compact form-->
        <div class="d-flex align-items-center">
            <!--begin::Input group-->
            <div class="position-relative w-md-400px me-md-2">
                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                <span class="svg-icon svg-icon-3 svg-icon-gray-500 position-absolute top-50 translate-middle ms-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                        <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
                <input type="text" class="form-control form-control-solid ps-10" name="search" id="filterSearch" value="" oninput="this.value = this.value.toUpperCase()" placeholder="Search">
            </div>
            <!--end::Input group-->
            <!--begin:Action-->
            <div class="d-flex align-items-center ms-3">
                <button type="reset" class="btn btn-primary" id="btn-adddiskonproduk" data-bs-toggle="modal" data-bs-target="#tambah_diskon_dealer"><i class="bi bi-plus-circle fs-1"></i> Tambah</button>
            </div>
            <!--end:Action-->
        </div>
        <!--end::Compact form-->
    </div>
</div>

<!-- Modal tambah diskon-->
<div class="modal fade" id="tambah_diskon_dealer" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="tambah_diskon_dealerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="tambah_diskon_dealerLabel">Tambah Diskon Dealer</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('setting.diskon.prosentase.produk.dealer.simpan') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="produk" class="form-label required mt-1">Produk</label>
                                <input type="text" class="form-control" id="produk" name="produk" placeholder="Produk" value="{{ old('produk') }}" autocomplete="fasle" oninput="this.value = this.value.toUpperCase()" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_produk" class="form-label mt-1">Nama produk</label>
                                <input type="text" class="form-control bg-secondary" id="nama_produk" name="nama_produk" placeholder="Nama produk" value="{{ old('nama_produk') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dealer" class="form-label required mt-1">Kode Dealer</label>
                                <input type="text" class="form-control" id="dealer" name="dealer" placeholder="Kode Dealer" value="{{ old('dealer')??'' }}" oninput="this.value = this.value.toUpperCase()" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_dealer" class="form-label mt-1">Nama Dealer</label>
                                <input type="text" class="form-control bg-secondary" id="nama_dealer" name="nama_dealer" placeholder="Nama dealer" value="{{ old('nama_dealer') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="companyid" class="form-label required mt-1">Cabang</label>
                                <input type="text" class="form-control bg-secondary" id="companyid" name="companyid" placeholder="Keterangan" value="{{$companyid}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="keterangan" class="form-label required mt-1">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan')??'' }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end modal --}}

<div class="d-flex flex-wrap flex-stack pb-7" data-select2-id="select2-data-131-enac">
    <!--begin::Title-->
    <div class="d-flex flex-wrap align-items-center my-1">
        <h3 class="fw-bolder me-5 my-1">{{ $data_disc->total }} Data Diskon Dealer
        <span class="text-gray-400 fs-6">↓</span></h3>
    </div>
    <!--end::Title-->
</div>

<div class="tab-content">
    <!--begin::Tab pane-->
    <!--end::Tab pane-->
    @if (\Agent::isDesktop())
    <!--begin::table-->
    <div id="kt_project_users_table_pane" class="tab-pane fade active show">
        <div class="card card-flush">
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table container-->
                <div class="table-responsive mt-10">
                    <!--begin::Table-->
                    <div id="kt_project_users_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table id="kt_project_users_table" class="table table-row-dashed table-row-gray-300 align-middle">
                                <!--begin::Head-->
                                <thead class="border">
                                    <tr class="fw-bolder text-muted text-center">
                                        <th>No</th>
                                        <th>Kode Produk</th>
                                        <th>nama Produk</th>
                                        <th>Kode Dealer</th>
                                        <th>Cabang</th>
                                        <th>Diskon</th>
                                        <th>Keterangan</th>
                                        <th>User Time</th>
                                        <th class="min-w-60px">Action</th>
                                    </tr>
                                </thead>
                                <!--end::Head-->
                                <!--begin::Body-->
                                <tbody class="fs-6 border">
                                    @if ($data_disc->total > 0)
                                    @php
                                        $no = $data_disc->from;
                                    @endphp
                                    @foreach ( $data_disc->data as $data)
                                    <tr class="fs-6 fw-bold text-gray-700">
                                        <td class="text-center">{{ $no }}</td>
                                        <td>
                                            {{ $data->kode_produk }}
                                        </td>
                                        <td>
                                            {{ $data->nama_produk }}
                                        </td>
                                        <td>
                                            {{ $data->kode_dealer }}
                                        </td>
                                        <td>
                                            {{ $data->companyid }}
                                        </td>
                                        <td>
                                            <span class="text-success fw-bolder fs-5">{{ $data->disc == '.00' ? '0' : $data->disc }}</span>
                                        </td>
                                        <td>
                                                {{ $data->keterangan}}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{
                                                    substr(substr($data->usertime,strpos($data->usertime,"=")+1),strpos(substr($data->usertime,strpos($data->usertime,"=")+1),"=")+1)
                                                }}
                                            </span><br>
                                            {{
                                                date('d:F:Y', date_timestamp_get(date_create(substr($data->usertime,0,10))))
                                            }}
                                        </td>
                                        <td class="text-center">
                                            <button type="reset" class="btn btn-sm btn-icon btn-danger d-inline-block mt-1 btn-delete" data-array="{{
                                                            json_encode([
                                                                'produk' => $data->kode_produk,
                                                                'dealer' => $data->kode_dealer,
                                                                'cabang' => $data->companyid,
                                                            ])
                                                        }}" data-bs-toggle="modal" data-bs-target="#delet_model">
                                                <span class="bi bi-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    @php
                                        $no++;
                                    @endphp
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endif
                                </tbody>
                                <!--end::Body-->
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                                <div class="dataTables_length" id="kt_project_users_table_length">
                                    <label>
                                        <select name="kt_project_users_table_length" aria-controls="kt_project_users_table" class="form-select form-select-sm form-select-solid">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                                <div class="dataTables_paginate paging_simple_numbers" id="kt_project_users_table_paginate">
                                    <ul class="pagination">
                                        @foreach ($data_disc->links as $data)
                                            @if (strpos($data->label, 'Next') !== false)
                                                <li class="page-item next {{ ($data->url == null)?'disabled':'' }}">
                                                    <a role="button" data-page="{{ (string)((int)($data_disc->current_page) + 1) }}" class="page-link">
                                                        <i class="next"></i>
                                                    </a>
                                                </li>
                                            @elseif (strpos($data->label, 'Previous') !== false)
                                                <li class="page-item previous {{ ($data->url == null)?'disabled':'' }}">
                                                    <a role="button" data-page="{{ (string)((int)($data_disc->current_page) - 1) }}" class="page-link">
                                                        <i class="previous"></i>
                                                    </a>
                                                </li>
                                            @elseif ($data->active == true)
                                                <li class="page-item active {{ ($data->url == null)?'disabled':'' }}">
                                                    <a role="button" data-page="{{ $data->label }}" class="page-link">{{ $data->label }}</a>
                                                </li>
                                            @elseif ($data->active == false)
                                                <li class="page-item {{ ($data->url == null)?'disabled':'' }}">
                                                    <a role="button" data-page="{{ $data->label }}" class="page-link">{{ $data->label }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Table-->
                </div>
            <!--end::Table container-->
            </div>
        <!--end::Card body-->
        </div>
    </div>
    <!--end::table-->
    @else
    <!--begin::Tab pane-->
    <div id="kt_project_users_card_pane" class="tab-pane fade active show">
        <!--begin::Row-->
        <div class="row g-3" id="dataDiskon">
        @if ($data_disc->total > 0)
        @foreach ( $data_disc->data as $data)
        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-6 col-12">
            <!--begin::Card-->
            <div class="card h-xl-100 flex-row flex-stack flex-wrap p-6">
                <!--begin::Info-->
                <div class="d-flex flex-column py-2 w-100">
                    <!--begin::Owner-->
                    <div class="d-flex align-items-center fs-2 fw-bolder mb-5">
                        <span class="text-gray-800"> {{ $data->kode_produk }} </span>
                        <span class="text-muted fs-3 fw-bold ms-2">({{ $data->nama_produk }})</span>
                    </div>
                    <!--end::Owner-->
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center w-100 rounded border border-gray-300 p-3">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fs-7 fw-bolder text-gray-400 border-bottom-1">
                                    <th>Kd Dealer</th>
                                    <th>Company</th>
                                    <th>Diskon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bolder">{{ $data->kode_dealer }}</td>
                                    <td><span class="fw-bolder">{{ $data->companyid }}</span></td>
                                    <td><span class="fw-bolder">{{ $data->disc == '.00' ? '0' : $data->disc }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--end::Wrapper-->
                    <div class="d-flex align-items-center w-100 p-3">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fs-7 fw-bolder text-gray-400 border-bottom-1">
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bolder">{{ $data->keterangan }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Info-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center justify-content-between py-2 w-100">
                    <button type="reset" class="btn btn-sm btn-danger me-3 btn-delete" data-array="{{
                                    json_encode([
                                        'produk' => $data->kode_produk,
                                        'dealer' => $data->kode_dealer,
                                        'cabang' => $data->companyid,
                                    ])
                                }}" data-bs-toggle="modal" data-bs-target="#delet_model">
                        Delete
                    </button>
                    <div>
                        <span class="badge badge-info">{{substr(substr($data->usertime,strpos($data->usertime,"=")+1),strpos(substr($data->usertime,strpos($data->usertime,"=")+1),"=")+1)}}</span>
                        <br>
                        <span class="fw-bold">{{date('d:F:Y', date_timestamp_get(date_create(substr($data->usertime,0,10))))}}</span>
                    </div>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Card-->
        </div>
        @endforeach
        @else
        <div class="col-12">
            <!--begin::Card-->
            <div class="card h-xl-100 flex-row flex-stack flex-wrap p-6">
                <!--begin::Owner-->
                <div class="text-center w-100">
                    <span class="fw-bold text-gray-800">Tidak ada data</span>
                </div>
                <!--end::Owner-->
            </div>
            <!--end::Card-->
        </div>
        @endif
        </div>
        <div class="d-flex flex-stack flex-wrap pt-10">
            <div class="fs-6 fw-bold text-gray-700">Showing {{ $data_disc->from }} to {{ $data_disc->to }} of {{ $data_disc->total }} entries</div>
            <!--begin::Pages-->
                <ul class="pagination">
                    @foreach ($data_disc->links as $data)
                        @if (strpos($data->label, 'Next') !== false)
                            <li class="page-item next {{ ($data->url == null)?'disabled':'' }}">
                                <a role="button" data-page="{{ (string)((int)($data_disc->current_page) + 1) }}" class="page-link">
                                    <i class="next"></i>
                                </a>
                            </li>
                        @elseif (strpos($data->label, 'Previous') !== false)
                            <li class="page-item previous {{ ($data->url == null)?'disabled':'' }}">
                                <a role="button" data-page="{{ (string)((int)($data_disc->current_page) - 1) }}" class="page-link">
                                    <i class="previous"></i>
                                </a>
                            </li>
                        @elseif ($data->active == true)
                            <li class="page-item active {{ ($data->url == null)?'disabled':'' }}">
                                <a role="button" data-page="{{ $data->label }}" class="page-link">{{ $data->label }}</a>
                            </li>
                        @elseif ($data->active == false)
                            <li class="page-item {{ ($data->url == null)?'disabled':'' }}">
                                <a role="button" data-page="{{ $data->label }}" class="page-link">{{ $data->label }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            <!--end::Pages-->
        </div>
    </div>
    @endif
</div>

<!--begin::Modal delet data-->
<div class="modal fade" tabindex="-1" id="delet_model">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Peringatan !</h5>
				<!--begin::Close-->
				<div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
					<i class="bi bi-x-lg"></i>
				</div>
				<!--end::Close-->
			</div>
			<form id="form" action="{{ route('setting.diskon.prosentase.produk.dealer.hapus') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
					<div div class= "mx-auto text-center" >
						<!--begin::Icon-->
							<i class="bi bi-exclamation-circle text-danger fs-5x"></i>
						<!--end::Icon-->
						<p class="mt-10 ms-text"></p>
					</div >
					<input type="hidden" name="produk" id="produk" value="">
					<input type="hidden" name="dealer" id="dealer" value="">
					<input type="hidden" name="cabang" id="cabang" value="">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger">Hapus</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!--end::Modal delet data-->



    {{-- <div id="dataLoadDiskon"></div> --}}

    @push('scripts')
    <script type="text/javascript">
            const current_page = "{{ $data_disc->current_page }}"
            let old = {
                'produk': '{{ old('produk') }}',
            }
    </script>
    <script language="JavaScript" src="{{ asset('assets/js/suma/settings/aturanharga/diskon/diskonprodukdealer/diskonprodukdealer.js') }}?v={{ time() }}"></script>
    
    @if (\Agent::isDesktop())
    <script language="JavaScript" src="{{ asset('assets/js/suma/settings/aturanharga/diskon/diskonprodukdealer/diskonprodukdealerTable.js') }}?v={{ time() }}"></script>
    @else
    <script language="JavaScript" src="{{ asset('assets/js/suma/settings/aturanharga/diskon/diskonprodukdealer/diskonprodukdealerCard.js') }}?v={{ time() }}"></script>
    @endif
    @endpush
@endsection
