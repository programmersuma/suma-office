$(document).ready(function () {
    function loadMasterData(page = 1, per_page = 10, year = '', month = '') {
        loading.block();
        window.location.href = window.location.origin + window.location.pathname + '?year=' + year.trim() + '&month=' + month.trim() +
            '&per_page=' + per_page + '&page=' + page;
    }

    // ===============================================================
    // Buat Dokumen
    // ===============================================================
    $('#btnBuatDokumen').on('click', function (e) {
        e.preventDefault();
        $('#inputKodeUpdateHarga').val('');
        $('#modalBuatDokumen').modal('show');
    });

    $('#inputKodeUpdateHarga').on('click', function (e) {
        e.preventDefault();
        if(data_user.role_id != 'D_H3' && data_user.role_id != 'MD_H3_SM') {
            loadDataOptionUpdateHarga();
            $('#formOptionUpdateHarga').trigger('reset');
            $('#modalOptionUpdateHarga').modal('show');
        }
    });

    $('#btnKodeUpdateHarga').on('click', function (e) {
        e.preventDefault();
        if(data_user.role_id != 'D_H3' && data_user.role_id != 'MD_H3_SM') {
            loadDataOptionUpdateHarga();
            $('#formOptionUpdateHarga').trigger('reset');
            $('#modalOptionUpdateHarga').modal('show');
        }
    });

    $('body').on('click', '#optionUpdateHargaContentModal #selectedOptionUpdateHarga', function (e) {
        e.preventDefault();
        $('#inputKodeUpdateHarga').val($(this).data('kode'));
        $('#modalOptionUpdateHarga').modal('hide');
    });

    $('#btnCreateDocument').on('click', function (e) {
        e.preventDefault();
        var kode = $('#inputKodeUpdateHarga').val();
        var _token = $('input[name="_token"]').val();

        if(kode == '') {
            Swal.fire({
                text: 'Pilih nomor dokumen terlebih dahulu',
                icon: 'warning',
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Ok, got it!',
                customClass: {
                    confirmButton: 'btn btn-warning'
                }
            });
        } else {
            Swal.fire({
                html: `Apakah anda yakin akan membuat dokumen update harga
                        berdasarkan data user <strong>`+ kode + `</strong> ?`,
                icon: "info",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: 'No',
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: 'btn btn-primary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    loading.block();
                    $.ajax({
                        url: url.buat_dokumen,
                        method: "POST",
                        data: { kode: kode, _token: _token },

                        success: function (response) {
                            loading.release();

                            if (response.status == true) {
                                window.location.href = url.daftar_update_harga;
                            } else {
                                Swal.fire({
                                    text: response.message,
                                    icon: 'warning',
                                    buttonsStyling: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    confirmButtonText: 'Ok, got it!',
                                    customClass: {
                                        confirmButton: 'btn btn-warning'
                                    }
                                });
                            }
                        },
                        error: function () {
                            loading.release();
                            Swal.fire({
                                text: 'Server Not Responding',
                                icon: 'error',
                                buttonsStyling: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                confirmButtonText: 'Ok, got it!',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    })
                }
            });
        }
    });


    // ===============================================================
    // Filter Update Harga
    // ===============================================================
    $('#btnFilterMasterData').on('click', function (e) {
        e.preventDefault();
        $('#inputFilterYear').val(data_filter.year);
        $('#selectFilterMonth').prop('selectedIndex', data_filter.month - 1).change();
        $('#modalFilter').modal('show');
    });

    $('#btnFilterProses').on('click', function (e) {
        e.preventDefault();
        var per_page = $('#selectPerPageMasterData').val();
        var year = $('#inputFilterYear').val();
        var month = $('#selectFilterMonth').val();
        loadMasterData(1, per_page, year, month);
    });

    $('#btnFilterReset').on('click', function (e) {
        e.preventDefault();
        var dateObj = new Date();
        var month = dateObj.getUTCMonth() + 1;
        var year = dateObj.getUTCFullYear();

        loading.block();
        $.ajax({
            url: url.clossing_marketing,
            method: "get",
            success: function(response) {
                loading.release();
                if (response.status == false) {
                    Swal.fire({
                        text: response.message,
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-warning"
                        }
                    });
                } else {
                    month = response.data.bulan_aktif;
                    year = response.data.tahun_aktif;

                    $('#selectFilterMonth').prop('selectedIndex', month - 1).change();
                    $('#inputFilterYear').val(year);

                    if(data_user.role_id == 'D_H3') {
                        $('#inputFilterNomorFaktur').val('');
                    } else if(data_user.role_id == 'MD_H3_SM') {
                        $('#inputFilterDealer').val('');
                        $('#inputFilterNomorFaktur').val('');
                    } else {
                        $('#inputFilterSalesman').val('');
                        $('#inputFilterDealer').val('');
                        $('#inputFilterNomorFaktur').val('');
                    }
                }
            },
            error: function() {
                loading.release();
                Swal.fire({
                    text: 'Server tidak merespon, coba lagi',
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-danger"
                    }
                });
            }
        });
    });
});
