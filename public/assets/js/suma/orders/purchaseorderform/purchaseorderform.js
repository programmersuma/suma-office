
$(document).ready(function () {
    // ===============================================================
    // Daftar
    // ===============================================================
    function loadMasterData(page = 1, per_page = 10, year = '', month = '', salesman = '', dealer = '') {
        loading.block();
        window.location.href = window.location.origin + window.location.pathname + '?year=' + year.trim() + '&month=' + month.trim() +
            '&salesman=' + salesman.trim() + '&dealer=' + dealer.trim() + '&per_page=' + per_page + '&page=' + page;
    }

    $('#selectPerPageMasterData').change(function() {
        var start_record = data_page.start_record;
        var per_page = $('#selectPerPageMasterData').val();
        var year = $('#inputFilterYear').val();
        var month = $('#selectFilterMonth').val();
        var salesman = $('#inputFilterSalesman').val();
        var dealer = $('#inputFilterDealer').val();
        var page = Math.ceil(start_record / per_page);

        loadMasterData(page, per_page, year, month, salesman, dealer);
    });

    $(document).on('click', '#paginationMasterData .page-item a', function () {
        var page = $(this)[0].getAttribute('data-page');
        var per_page = $('#selectPerPageMasterData').val();
        var year = $('#inputFilterYear').val();
        var month = $('#selectFilterMonth').val();
        var salesman = $('#inputFilterSalesman').val();
        var dealer = $('#inputFilterDealer').val();

        loadMasterData(page, per_page, year, month, salesman, dealer);
    });

    // ===============================================================
    // Filter Salesman
    // ===============================================================
    $('#inputFilterSalesman').on('click', function (e) {
        e.preventDefault();
        if(data_user.role_id != 'D_H3' && data_user.role_id != 'MD_H3_SM') {
            loadDataOptionSalesman();
            $('#formOptionSalesman').trigger('reset');
            $('#modalOptionSalesman').modal('show');
        }
    });

    $('#btnFilterPilihSalesman').on('click', function (e) {
        e.preventDefault();
        if(data_user.role_id != 'D_H3' && data_user.role_id != 'MD_H3_SM') {
            loadDataOptionSalesman();
            $('#formOptionSalesman').trigger('reset');
            $('#modalOptionSalesman').modal('show');
        }
    });

    $('body').on('click', '#optionSalesmanContentModal #selectedOptionSalesman', function (e) {
        e.preventDefault();
        $('#inputFilterSalesman').val($(this).data('kode_sales'));
        $('#modalOptionSalesman').modal('hide');
    });

    // ===============================================================
    // Filter Dealer
    // ===============================================================
    $('#inputFilterDealer').on('click', function (e) {
        e.preventDefault();
        var kode_sales = $('#inputFilterSalesman').val();

        if(data_user.role_id != 'D_H3') {
            if(data_user.role_id == 'MD_H3_SM' || data_user.role_id == 'MD_H3_KORSM') {
                if(kode_sales == '') {
                    Swal.fire({
                        text: 'Data salesman tidak boleh kosong',
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-warning"
                        }
                    });
                } else {
                    $('#formOptionDealerSalesman').trigger('reset');
                    loadDataOptionDealerSalesman(kode_sales.trim(), 1, 10, '');
                    $('#modalOptionDealerSalesman').modal('show');
                }
            } else {
                $('#formOptionDealer').trigger('reset');
                loadDataOptionDealer(1, 10, '');
                $('#modalOptionDealer').modal('show');
            }
        }
    });

    $('#btnFilterPilihDealer').on('click', function (e) {
        e.preventDefault();
        var kode_sales = $('#inputFilterSalesman').val();

        if(data_user.role_id != 'D_H3') {
            if(data_user.role_id == 'MD_H3_SM' || data_user.role_id == 'MD_H3_KORSM') {
                if(kode_sales == '') {
                    Swal.fire({
                        text: 'Data salesman tidak boleh kosong',
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-warning"
                        }
                    });
                } else {
                    $('#formOptionDealerSalesman').trigger('reset');
                    loadDataOptionDealerSalesman(kode_sales.trim(), 1, 10, '');
                    $('#modalOptionDealerSalesman').modal('show');
                }
            } else {
                $('#formOptionDealer').trigger('reset');
                loadDataOptionDealer(1, 10, '');
                $('#modalOptionDealer').modal('show');
            }
        }
    });

    $('body').on('click', '#optionDealerContentModal #selectedOptionDealer', function (e) {
        e.preventDefault();
        $('#inputFilterDealer').val($(this).data('kode_dealer'));
        $('#modalOptionDealer').modal('hide');
    });

    $('body').on('click', '#optionDealerSalesmanContentModal #selectedOptionDealerSalesman', function (e) {
        e.preventDefault();
        $('#inputFilterDealer').val($(this).data('kode_dealer'));
        $('#modalOptionDealerSalesman').modal('hide');
    });

    $('#modalFilter').on('click', '#btnFilterReset', function (e) {
        $('#selectFilterMonth').val(new Date().getMonth() + 1);
        $('#inputFilterYear').val(new Date().getFullYear());
        $('#inputFilterSalesman').val('');
        $('#inputFilterDealer').val('');
        loadMasterData(1,10,$('#inputFilterYear').val(),$('#selectFilterMonth').val(),$('#inputFilterSalesman').val(),$('#inputFilterDealer').val());
    });

    $('#modalFilter').on('click', '#btnFilterProses', function (e) {
        loadMasterData(1,10,$('#inputFilterYear').val(),$('#selectFilterMonth').val(),$('#inputFilterSalesman').val(),$('#inputFilterDealer').val())
    });
});
