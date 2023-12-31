@extends('layouts.main.index')
@section('title','Retur')
@section('subtitle','Supplier')
@push('styles')
@endpush

@section('container')
<!--begin::Row-->
<div class="row gy-5 g-xl-8">
	<div class="card card-xl-stretch shadow">
		<div class="card-header align-items-center py-5 gap-2 gap-md-5">
			<div class="card-title">
				<!--begin::Search-->
				<div class="d-flex align-items-center position-relative my-1">
					<span class="svg-icon svg-icon-1 position-absolute ms-4"><i class="bi bi-search"></i></span>
					<input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Search" id="cari" name="cari" value="{{ $old_request->no_retur }}" data-bs-trigger="hover focus" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="Dapat mencari No Dokumen, Kode Supplier, no Klaim, Part Number"/>
				</div>
				<!--end::Search-->
			</div>
			<div class="card-toolbar flex-row-fluid justify-content-end gap-5">
				<!--begin::Menu Tambah-->
				<a href="{{ route('retur.supplier.form') }}" class="btn btn-primary">
					Tambah Retur
				</a>
				<!--end::Menu Tambah-->
			</div>
		</div>
		<div class="table-responsive">
			<table id="datatable_classporduk" class="table table-row-dashed table-row-gray-300 align-middle">
				<thead class="border">
					<tr class="fs-8 fw-bolder text-muted text-center">
						<th rowspan="2" class="w-50px ps-3 pe-3">No</th>
						<th rowspan="2" class="w-100px ps-3 pe-3">No Retur</th>
						<th rowspan="2" class="w-100px ps-3 pe-3">Tanggal</th>
						<th rowspan="2" class="w-100px ps-3 pe-3">Kode Supplier</th>
						<th colspan="2" class="w-100px ps-3 pe-3">Qty</th>
						<th colspan="2" class="w-auto">Detail</th>
						<th rowspan="2" class="w-auto ps-3 pe-3">Action</th>
					</tr>
					<tr class="fs-8 fw-bolder text-muted text-center">
						<th class="w-100px ps-3 pe-3">Retur</th>
						<th class="w-100px ps-3 pe-3">Jawab</th>

						<th class="w-100px ps-3 pe-3">No Klaim</th>
						<th class="w-100px ps-3 pe-3">Part Number</th>
					</tr>
				</thead>
				<tbody class="border">
					@if ($data->total == 0 )
					<tr>
						<td colspan="9" class="text-center">
							<span class="text-active-danger">Tidak ada data</span>
						</td>
					</tr>
					@else
					@php
						$no = $data->from;
					@endphp
					@foreach ($data->data as $a)
					<tr class="fw-bolder fs-8 border">
						<td class="text-center">{{  $no++ }}</td>
						<td>{{ $a->no_retur }}</td>
						<td>{{ date('Y/m/d', strtotime($a->tglretur)) }}</td>
						<td class="text-center">{{ $a->kd_supp }}</td>
						<td class="text-end">{{ $a->total??0 }}</td>
						<td class="text-end">{{ $a->qty_jwb??0 }}</td>
						<td>
							@foreach (collect($a->detail)->unique('no_klaim')->pluck('no_klaim')->sort() as $b)
								{{ $b }}<br>
							@endforeach
						</td>
						<td>
							@foreach (collect($a->detail)->unique('kd_part')->pluck('kd_part')->sort() as $b)
								{{ $b }}<br>
							@endforeach
						</td>
						<td class="text-center">
							<a href="{{ route('retur.supplier.jawab.form',['no_retur' => $a->no_retur,]) }}" class="btn-sm btn-icon btn-success text-white d-inline-block mt-1" role="button" data-id="{{ $a->no_retur}}"><span class="bi bi-envelope"></span></a>
						</td>
					</tr>
					@endforeach
					@endif
				</tbody>
			</table>
		</div>
		
		<div class="card-footer justify-content-center">
			<div colspan="8" class="d-flex justify-content-between">
				<div class="form-group">
					<select class="form-select form-select-sm" name="per_page" id="per_page">
						<option value="10" @if ($old_request->per_page == 10 ) selected @endif>10</option>
						<option value="50" @if ($old_request->per_page == 50 ) selected @endif>50</option>
						<option value="100" @if ($old_request->per_page == 100 ) selected @endif>100</option>
						<option value="500" @if ($old_request->per_page == 500 ) selected @endif>500</option>
					</select>
				</div>
				<div id="page">
					@php
						$paginator = new Illuminate\Pagination\LengthAwarePaginator(
							$data->data,
							$data->total,
							$data->per_page,
							$data->current_page,
							['path' => Request::url(), 'query' => ['page' => $data->current_page,'per_page' => $old_request->per_page,'no_retur' => $old_request->no_retur]]
						);
					@endphp
                    {{ $paginator->links() }}
				</div>
			</div>
		</div>
	</div>
</div>
<!--end::Row-->
@endsection

@push('scripts')
<!-- JQuery CDN -->
{{-- <script src="https://code.jquery.com/jquery-3.6.0.js"></script> --}}

<script language="JavaScript" src="{{ asset('assets/js/suma/retur/supplier/index.js') }}?v={{ time() }}"></script>
@endpush