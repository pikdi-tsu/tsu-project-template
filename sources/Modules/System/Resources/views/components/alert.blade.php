<div id="pikdi-alert-data" style="display: none;" 
    data-success="{{ session('success') }}" 
    data-warning="{{ session('warning') }}" 
    data-error="{{ session('error') }}"
    data-validation-errors="{{ json_encode($errors->all()) }}">
</div>
<script>
    // --- KONFIGURASI KONTAK PIKDI ---
    // Ubah link/text di sini agar berlaku global
    const pikdiInfo = {
        html: `<div class="mt-3 pt-3 border-top">
                  <p class="text-muted mb-1" style="font-size: 0.85em;">Butuh bantuan teknis?</p>
                  <a href="mailto:pikdi@tsu.ac.id" class="btn btn-sm btn-outline-dark font-weight-bold pl-3 pr-3 shadow-sm" style="border-radius: 20px;">
                      <i class="fas fa-headset mr-1"></i> Hubungi Helpdesk PIKDI
                  </a>
               </div>`,
        footer: `<div class="w-100 text-center">
                    <p class="text-muted mb-2" style="font-size: 0.9em;">Mengalami kendala berulang?</p>
                    <a href="mailto:pikdi@tsu.ac.id" class="btn btn-danger font-weight-bold pl-4 pr-4 shadow" style="border-radius: 5px;">
                        <i class="fas fa-life-ring mr-2"></i> LAPORKAN KE PIKDI
                    </a>
                 </div>`
    };

    // Intial Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    if ($.fn.dataTable) {
        // Matikan alert bawaan DataTables
        $.fn.dataTable.ext.errMode = 'none';

        // Tangkap event error
        $(document).on('error.dt', function(e, settings, techNote, message) {

            let xhr = settings.jqXHR;
            let statusCode = xhr ? xhr.status : 0;
            let errorTitle = 'Gagal Memuat Data';
            let errorText  = 'Terjadi gangguan saat mengambil data tabel.';
            let isSessionExpired = false;

            // SESSION EXPIRED (401 / 419)
            if (statusCode === 401 || statusCode === 419) {
                errorTitle = 'Sesi Telah Berakhir';
                errorText  = 'Waktu sesi login Anda habis. Silakan login ulang untuk melanjutkan.';
                isSessionExpired = true;
            }
            // AKSES DITOLAK ATAU VALIDASI (403 / 422)
            else if (statusCode === 403 || statusCode === 422) {
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    errorText = xhr.responseJSON.message;
                }
            }
            // ERROR SERVER INTERNAL (500)
            else if (statusCode >= 500) {
                errorTitle = 'Gangguan Server';
                errorText  = 'Terjadi kendala pada server kami. Tim teknis telah diinformasikan untuk penanganan lebih lanjut.';
            }
            else {
                errorTitle = 'Gangguan Sistem';
                errorText  = 'Terjadi kendala saat mengambil data tabel. Silakan hubungi tim teknis jika masalah ini terus berulang.';
            }

            // SweetAlert
            Swal.fire({
                icon: isSessionExpired ? 'warning' : 'error',
                title: errorTitle,
                text: errorText,
                footer: isSessionExpired ? '' : pikdiInfo.footer,

                showCancelButton: isSessionExpired,
                confirmButtonColor: isSessionExpired ? '#3085d6' : '#d33',
                confirmButtonText: isSessionExpired ? 'Login Ulang' : 'Tutup',
                cancelButtonText: 'Batal',

                allowOutsideClick: !isSessionExpired,
                allowEscapeKey: !isSessionExpired
            }).then((result) => {
                if (result.isConfirmed && isSessionExpired) {
                    window.location.reload();
                }
            });

            console.error("DataTables Error:", message);
        });
    }

    // Fungsi Manual (file JS / AJAX)
    function notifalert(title, text, type) {
        let bgColor = '#28a745'; // Default Hijau (Success)
        let txtColor = '#fff';   // Teks Putih
        let iconColor = 'white'; // Icon Putih

        if (type === 'error') {
            bgColor = '#dc3545'; // Merah
        } else if (type === 'warning') {
            bgColor = '#ffc107'; // Kuning
            txtColor = '#000';   // Teks Hitam
            iconColor = 'black';
        } else if (type === 'info') {
            bgColor = '#17a2b8'; // Biru Muda
        }

        Toast.fire({
            icon: type,
            title: title,
            text: text,
            background: bgColor,
            color: txtColor,
            iconColor: iconColor
        });
    }

    // --- GLOBAL AJAX WRAPPER ---
    // Menggantikan pemanggilan $.ajax() manual untuk otomatisasi SweetAlert & Response Standardization (Rule #10)
    function pikdiAjax(options) {
        let defaults = {
            type: 'POST',
            url: '',
            data: {},
            showLoading: true,
            loadingText: 'Sedang Memproses...',
            successMessage: null, // Jika null, ambil dari response.message
            onSuccess: function(res) {},
            onError: function(res, xhr) {}
        };
        
        let settings = $.extend({}, defaults, options);
        
        let ajaxConfig = {
            url: settings.url,
            type: settings.type,
            data: settings.data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                if (settings.showLoading) {
                    Swal.fire({
                        title: settings.loadingText,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            },
            success: function(response) {
                // Rule #10: format response.success
                if (response.success || response.status === 'success') { // Fallback to status === success for backward compatibility
                    let msg = settings.successMessage || response.message || 'Proses berhasil dilakukan!';
                    Swal.fire({
                        title: 'Berhasil!',
                        text: msg,
                        icon: 'success'
                    }).then((result) => {
                        settings.onSuccess(response);
                    });
                } else {
                    let msg = response.message || 'Terjadi kesalahan sistem.';
                    let errCode = (response.errors && response.errors.code) ? 'Kode: ' + response.errors.code : '';
                    Swal.fire('Gagal!', msg + (errCode ? '<br><small>'+errCode+'</small>' : ''), 'error');
                    settings.onError(response, null);
                }
            },
            error: function(xhr, status, error) {
                let res = xhr.responseJSON;
                let msg = (res && res.message) ? res.message : error;
                let errCode = (res && res.errors && res.errors.code) ? res.errors.code : '';
                
                if (xhr.status === 422) {
                    if (res && res.errors && !res.errors.code) { // Validasi standar laravel
                        let errorsObj = res.errors;
                        msg = '<ul class="text-left mt-3 text-sm" style="font-size:0.9em">';
                        for(let key in errorsObj) {
                            if(typeof errorsObj[key] === 'string') {
                                msg += '<li>' + errorsObj[key] + '</li>';
                            } else if (Array.isArray(errorsObj[key])) {
                                msg += '<li>' + errorsObj[key][0] + '</li>';
                            }
                        }
                        msg += '</ul>';
                        Swal.fire({
                            title: 'Validasi Gagal!',
                            html: msg,
                            icon: 'warning'
                        });
                    } else {
                        Swal.fire('Validasi Gagal!', msg, 'warning');
                    }
                } else {
                    Swal.fire({
                        title: 'Terjadi Kesalahan!',
                        html: `<p>${msg}</p>` + (errCode ? `<p class="text-muted"><small>Kode: ${errCode}</small></p>` : ''),
                        icon: 'error',
                        footer: pikdiInfo.footer
                    });
                }
                settings.onError(res, xhr);
            }
        };

        // Otomatis deteksi FormData
        if (settings.data instanceof FormData) {
            ajaxConfig.processData = false;
            ajaxConfig.contentType = false;
        }

        $.ajax(ajaxConfig);
    }

    $(document).ready(function() {
        // 403 alert
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            // Cek error 403 (Unauthorized / Permission)
            if (jqxhr.status === 403) {
                let response = jqxhr.responseJSON;
                let msg = response ? response.message : 'Anda tidak memiliki akses untuk aksi ini.';

                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak!',
                    html: msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Tutup',
                    backdrop: `rgba(0,0,0,0.4) left top no-repeat`
                });
            }
        });

        let alertDataElement = document.getElementById('pikdi-alert-data');
        let sessionSuccess = alertDataElement.getAttribute('data-success');
        let sessionWarning = alertDataElement.getAttribute('data-warning');
        let sessionError = alertDataElement.getAttribute('data-error');

        // Sukses Setup
        if (sessionSuccess) {
            Toast.fire({
                icon: 'success',
                html: sessionSuccess,
                background: '#28a745', // Hijau
                color: '#fff',         // Teks Putih
                iconColor: 'white'     // Icon Putih
            });
        }

        // Warning Setup
        if (sessionWarning) {
            Toast.fire({
                icon: 'warning',
                html: `<div class="mb-2">${sessionWarning}</div>` + pikdiInfo.html,
                background: '#ffc107', // Kuning
                color: '#000',
                iconColor: 'white'
            });
        }

        // Error Setup
        if (sessionError) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: `<p class="text-bold">${sessionError}</p>`,
                footer: pikdiInfo.footer,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup',
                backdrop: `rgba(0,0,0,0.4) left top no-repeat`
            });
        }

        // Error validasi
        let validationErrorsRaw = alertDataElement.getAttribute('data-validation-errors');
        let validationErrors = validationErrorsRaw ? JSON.parse(validationErrorsRaw) : [];
        if (validationErrors && validationErrors.length > 0) {
            let errorMsg = '<ul class="text-left mt-3" style="font-size: 0.9em;">';
            validationErrors.forEach(function(error) {
                errorMsg += '<li>' + error + '</li>';
            });
            errorMsg += '</ul>';

            Swal.fire({
                icon: 'warning',
                title: 'Periksa Inputan Anda!',
                html: errorMsg,
                footer: '<span class="text-muted font-italic">Pastikan semua field bertanda bintang (*) terisi.</span>',
                confirmButtonColor: '#f39c12',
                confirmButtonText: 'OK, Saya Perbaiki'
            });
        }
    });
</script>
