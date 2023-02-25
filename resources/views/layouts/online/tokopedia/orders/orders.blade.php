@extends('layouts.main.index')
@section('title','Tokopedia')
@section('subtitle','Orders')
@section('container')
<div class="row g-0">
    <div class="card card-flush">
        <div class="card-header align-items-center border-0 mt-4 mb-4">
            <h3 class="card-title align-items-start flex-column">
                <span class="fw-bolder mb-2 text-dark">Orders</span>
                <span class="text-muted fw-bold fs-7">Daftar order marketplace tokopedia</span>
                <span class="text-muted fw-bold fs-7 mt-2">Periode
                    <span class="text-dark fw-bolder fs-7">{{ date('d F Y', strtotime($data_filter->start_date)) }}</span> s/d
                    <span class="text-dark fw-bolder fs-7">{{ date('d F Y', strtotime($data_filter->end_date)) }}</span>
                </span>
            </h3>
            <div class="card-toolbar">
                <img src="{{ asset('assets/images/logo/tokopedia_lg.png') }}" class="h-75px" />
            </div>
        </div>
        <div class="card-header align-items-center border-0">
            <div class="align-items-start flex-column">
                <div class="input-group">
                    <input id="inputStartDate" name="start_date" class="form-control w-md-200px" placeholder="Dari Tanggal"
                        value="{{ $data_filter->start_date }}">
                    <span class="input-group-text">s/d</span>
                    <input id="inputEndDate" name="end_date" class="form-control w-md-200px" placeholder="Sampai Dengan"
                        value="{{ $data_filter->end_date }}">
                    <button id="btnFilterMasterData" class="btn btn-icon btn-primary" type="button">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="position-relative w-md-200px me-md-2">
                    <select id="selectStatus" name="status" class="form-select" aria-label="Status">
                        <option value="" @if($data_filter->status == '') selected @endif>ALL</option>
                        <option value="0" @if($data_filter->status == '0') selected @endif>Seller cancel order</option>
                        <option value="3" @if($data_filter->status == '3') selected @endif>Order Reject Due Empty Stock</option>
                        <option value="5" @if($data_filter->status == '5') selected @endif>Order Canceled by Fraud</option>
                        <option value="6" @if($data_filter->status == '6') selected @endif>Order Rejected (Auto Cancel Out of Stock)</option>
                        <option value="10" @if($data_filter->status == '10') selected @endif>Order rejected by seller</option>
                        <option value="15" @if($data_filter->status == '15') selected @endif>Instant Cancel by Buyer</option>
                        <option value="100" @if($data_filter->status == '100') selected @endif>Order Created</option>
                        <option value="103" @if($data_filter->status == '103') selected @endif>Wait for payment confirmation from third party</option>
                        <option value="220" @if($data_filter->status == '220') selected @endif>Payment verified, order ready to process</option>
                        <option value="221" @if($data_filter->status == '221') selected @endif>Waiting for partner approval</option>
                        <option value="400" @if($data_filter->status == '400') selected @endif>Seller accept order</option>
                        <option value="450" @if($data_filter->status == '450') selected @endif>Waiting for pickup</option>
                        <option value="500" @if($data_filter->status == '500') selected @endif>Order shipment</option>
                        <option value="501" @if($data_filter->status == '501') selected @endif>Status changed to waiting resi have no input</option>
                        <option value="520" @if($data_filter->status == '520') selected @endif>Invalid shipment reference number (AWB)</option>
                        <option value="530" @if($data_filter->status == '530') selected @endif>Requested by user to correct invalid entry of shipment reference number</option>
                        <option value="540" @if($data_filter->status == '540') selected @endif>Delivered to Pickup Point</option>
                        <option value="550" @if($data_filter->status == '550') selected @endif>Return to Seller</option>
                        <option value="600" @if($data_filter->status == '660') selected @endif>Order delivered</option>
                        <option value="601" @if($data_filter->status == '601') selected @endif>Buyer open a case to finish an order</option>
                        <option value="690" @if($data_filter->status == '690') selected @endif>Fraud Review</option>
                        <option value="700" @if($data_filter->status == '700') selected @endif>Order finished</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="postOrder">
    <!--Start List Order-->
    @foreach($data_order as $data)
        <div class="card mb-5 mb-xl-8 mt-6">
            <div class="row pt-4 pb-4 ps-6 pe-6">
                <div class="col-lg-6 text-start">
                    <span class="fs-7 fw-bolder text-gray-600">Order ID:
                        <span class="ms-2">
                            <span class="fs-7 fw-bolder text-primary">{{ trim($data->order_id) }}</span>
                        </span>
                    </span>
                </div>
                <div class="col-lg-6 text-end">
                    <span class="fs-7 fw-bolder text-gray-600">Status:
                        <span class="ms-2">
                            @if($data->order_status->kode == 0)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 3)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 5)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 6)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 10)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 15)
                            <span class="fs-7 fw-boldest badge badge-danger">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 100)
                            <span class="fs-7 fw-boldest badge badge-warning">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 103)
                            <span class="fs-7 fw-boldest badge badge-warning">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 220)
                            <span class="fs-7 fw-boldest badge badge-primary">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 221)
                            <span class="fs-7 fw-boldest badge badge-primary">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 400)
                            <span class="fs-7 fw-boldest badge badge-primary">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 450)
                            <span class="fs-7 fw-boldest badge badge-primary">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 500)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 501)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 520)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 530)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 540)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 550)
                            <span class="fs-7 fw-boldest badge badge-info">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 600)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 601)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 690)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @elseif($data->order_status->kode == 700)
                            <span class="fs-7 fw-boldest badge badge-success">{{ strtoupper($data->order_status->keterangan) }}</span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
            <div class="separator"></div>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6 pt-6 pb-6 ps-10">
                    <span class="fs-7 fw-bolder text-gray-500 d-block">No Invoice:</span>
                    <span class="fs-7 fw-bolder text-dark d-block mt-1">{{ $data->nomor_invoice }}</span>
                    <span class="fs-7 fw-bolder text-gray-800 d-block">{{ date('d F Y', strtotime($data->tanggal)) }}</span>

                    <span class="fs-7 fw-bolder text-gray-500 d-block mt-6">Logistics:</span>
                    <div class="fw-bolder fs-7 text-gray-800 d-flex align-items-center flex-wrap mt-1">
                        <span class="pe-2">{{ $data->logistics->shipping_agency }}</span>
                        <span class="fs-7 text-danger d-flex align-items-center">
                        <span class="bullet bullet-dot bg-danger me-2"></span>{{ $data->logistics->service_type }}</span>
                    </div>

                    <span class="fs-7 fw-bolder text-gray-500 d-block mt-6">Amounts:</span>
                    <div class="d-flex flex-stack w-200px mt-1">
                        <div class="fs-7 fw-bolder text-gray-800">Product:</div>
                        <div class="fs-7 fw-boldest text-danger text-end">Rp. {{ number_format($data->amount->product) }}</div>
                    </div>
                    <div class="d-flex flex-stack w-200px mt-1">
                        <div class="fs-7 fw-bolder text-gray-800">Shipping:</div>
                        <div class="fs-7 fw-bolder text-dark text-end">Rp. {{ number_format($data->amount->shipping) }}</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 pt-6 pb-6 ps-6">
                    <span class="fs-7 fw-bolder text-gray-500 d-block">Recipient:</span>
                    <span class="fs-7 fw-bolder text-dark d-block mt-1">{{ $data->recipient->address->city }}</span>
                    <span class="fs-7 fw-bolder text-gray-800 d-block">{{ $data->recipient->address->province }}, {{ $data->recipient->address->country }}</span>
                    <span class="fs-7 fw-bolder text-gray-800 d-block">{{ $data->recipient->address->postal_code }}</span>

                    <span class="fs-7 fw-bolder text-gray-500 d-block mt-6">Deadline Confirmation:</span>
                    <div class="d-flex flex-stack w-250px mt-1">
                        <div class="fs-7 fw-bolder text-gray-800">Accept:</div>
                        <div class="fs-7 fw-bolder text-danger text-end">{{ $data->shipment_fulfillment->accept_deadline }}</div>
                    </div>
                    <div class="d-flex flex-stack w-250px mt-1">
                        <div class="fs-7 fw-bolder text-gray-800">Shipping:</div>
                        <div class="fs-7 fw-bolder text-danger text-end">{{ $data->shipment_fulfillment->confirm_shipping_deadline }}</div>
                    </div>

                    @if($data->is_plus == true)
                    <span class="badge badge-success mt-6" style="background-color: #006a0b;">
                        <img src="{{ asset('assets/images/logo/tokopedia_plus.png') }}" class="h-20px" />
                    </span>
                    @endif
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 pt-6 pb-6 ps-6 pe-10">
                    <span class="fs-7 fw-bolder text-gray-500 d-block">No Faktur:</span>
                    <span class="fs-7 fw-boldest text-danger d-block mt-1">{{ empty($data->faktur->nomor_faktur) ? '(Not Found)' : $data->faktur->nomor_faktur }}</span>
                    <span class="fs-7 fw-bolder text-gray-800 d-block">{{ empty($data->faktur->tanggal) ? '-' : date('d F Y', strtotime($data->faktur->tanggal)) }}</span>
                    <div class="mt-5">
                        <span class="fs-8 fw-boldest badge badge-danger">{{ empty($data->faktur->kode_lokasi) ? '-' : strtoupper($data->faktur->kode_lokasi) }}</span>
                        <span class="fs-8 fw-boldest badge badge-primary">{{ empty($data->faktur->kode_sales) ? '-' : strtoupper($data->faktur->kode_sales) }}</span>
                        <span class="fs-8 fw-boldest badge badge-info">{{ empty($data->faktur->kode_dealer) ? '-' : strtoupper($data->faktur->kode_dealer) }}</span>
                    </div>
                    <span class="fs-7 fw-bolder text-gray-500 d-block mt-6">Total Faktur:</span>
                    <span class="fs-6 fw-boldest text-danger">Rp. {{ empty($data->faktur->total) ? '-' : number_format($data->faktur->total) }}</span>
                </div>
            </div>
            <div class="separator"></div>
            <div class="row pt-4 pb-4 ps-6 pe-6">
                <div class="d-flex align-items-center">
                    <div class="col-lg-6 text-start">
                        <a href="{{ route('online.orders.tokopedia.form.form', trim($data->nomor_invoice)) }}" class="btn btn-primary w-250px">
                            <i class="fa fa-check text-white" data-toggle="tooltip" data-placement="top" title="Select"></i> Lihat Detail Transaksi
                        </a>
                    </div>
                    <div class="col-lg-6 text-end">
                        <div class="fs-5 fw-bolder text-dark text-end">{{ empty($data->faktur->nomor_faktur) ? '(Not Found)' : $data->faktur->nomor_faktur }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    <!--End List Order-->
</div>

@push('scripts')
<script>
    const url = {
        'daftar_order': "{{ route('online.orders.tokopedia.daftar') }}",
    }
</script>
<script src="{{ asset('assets/js/suma/online/tokopedia/orders/daftar.js') }}?v={{ time() }}"></script>
@endpush
@endsection

