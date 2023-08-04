function Part(requst){
    if($('#no_klaim').val() != ''){
        loading.block();
        $('#part-list').find('tbody').html(`
        <tr>
            <td colspan="5" class="text-center text-primary">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </td>
        </tr>`);
        $.get(base_url+'/part',{
            option: requst.option,
            no_retur: $('#detail_modal #no_klaim').val(),
            kd_part: requst.kd_part,
            page : requst.page,
            per_page : requst.per_page
        }, function (response) {
            if(response.status == '1'){
                let dataJson = response.data;
                if(requst.option[0] == 'first'){
                    if (jQuery.isEmptyObject(dataJson)) {
                        toastr.error('Part Number Tidak Ditemukan!', "info");
                        $('#kd_part').addClass('is-invalid');
                        $('#kd_part').removeClass('is-valid');
                    } else {
                        $('#kd_part').val(dataJson.kd_part);
                        $('#nm_part').val(dataJson.nm_part);
                        $('#qty_klaim').val(dataJson.jumlah);
                        $('#no_produksi').val(dataJson.no_produksi);
                        $('#kd_part').removeClass('is-invalid');
                        $('#kd_part').addClass('is-valid');
                    }
                } else if (requst.option[0] == 'page') {
                    $('#part-list').html(response.data);
                    $('#part-list').modal('show');
                } else {
                    $('#dealer-list .close').trigger('click')
                }
            }
            if (response.status == '0') {
                toastr.error(response.message, "Error");
            }
            if (response.status == '2') {
                swal.fire({
                    title: 'Perhatian!',
                    text: response.message,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-secondary'
                    },
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        }).fail(function (jqXHR, textStatus, error) {
            $('#part-list .close').trigger('click')
            Swal.fire({
                title: 'Error ' + jqXHR.status,
                text: 'Maaf, Terjadi Kesalahan, Silahkan coba lagi!',
                icon: 'error',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-secondary'
                },
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        });
        loading.release();
    } else {
        toastr.error('No Klaim Tidak Boleh Kosong!', "Error");
    }
}


$('#part-list').on('click','.pilih' ,function () {
    const data = JSON.parse(atob($(this).data('a')));
    $('#kd_part').val(data.kd_part);
    $('#nm_part').val(data.nm_part);
    $('#qty_klaim').val(data.jumlah);
    $('#no_produksi').val(data.no_produksi);
    $('#part-list .close').trigger('click')
})

$('#kd_part').on('change', function () {
    $('#ket_part').val('');
    if($('#kd_part').val() == ''){
        $('#kd_part').removeClass('is-valid');
        return false;
    }

    Part({
        option: ['first'],
        kd_part: $(this).val(),
        page : 1,
        per_page : 10
    });
});

$('.list-part').on('click', function () {
    Part({
        option: ['page'],
        page : 1,
        per_page : 10
    });
});

$('#part-list').on('click', '.pagination .page-item', function () {
    Part({
        option: ['page'],
        page : $(this).find('a').attr('href').split('page=')[1],
        per_page : $('#part-list').find('#per_page').val()
    });
});

$('#part-list').on('change','#per_page', function () {
    Part({
        option: ['page'],
        kd_part: $('#part-list').find('#cari').val(),
        page : 1,
        per_page : $(this).val()
    });
});

$('#part-list').on('click','#btn_cari', function () {
    Part({
        option: ['page'],
        kd_part: $('#part-list').find('#cari').val(),
        page : 1,
        per_page : $('#part-list').find('#per_page').val()
    });
});
