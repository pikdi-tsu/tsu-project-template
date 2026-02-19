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
            // ERROR SERVER (500, dll)
            else if(xhr && xhr.responseJSON && xhr.responseJSON.message) {
                errorText = xhr.responseJSON.message;
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

    $(document).ready(function() {
        // Sukses Setup
        @if(session('success'))
        Toast.fire({
            icon: 'success',
            html: `{!! session('success') !!}`,
            background: '#28a745', // Hijau
            color: '#fff',         // Teks Putih
            iconColor: 'white'     // Icon Putih
        });
        @endif

        // Warning Setup
        @if(session('warning'))
        Toast.fire({
            icon: 'warning',
            html: `<div class="mb-2">{!! session('warning') !!}</div>` + pikdiInfo.html,
            background: '#ffc107', // Kuning
            color: '#000',
            iconColor: 'white'
        });
        @endif

        // Error Setup
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan!',
            html: `<p class="text-bold">{!! session('error') !!}</p>`,
            footer: pikdiInfo.footer,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Tutup',
            backdrop: `rgba(0,0,0,0.4) left top no-repeat`
        });
        @endif

        // Error validasi
        @if($errors->any())
        let errorMsg = '<ul class="text-left mt-3" style="font-size: 0.9em;">';
        @foreach ($errors->all() as $error)
            errorMsg += '<li>{!! $error !!}</li>';
        @endforeach
            errorMsg += '</ul>';

        Swal.fire({
            icon: 'warning',
            title: 'Periksa Inputan Anda!',
            html: errorMsg,
            footer: '<span class="text-muted font-italic">Pastikan semua field bertanda bintang (*) terisi.</span>',
            confirmButtonColor: '#f39c12',
            confirmButtonText: 'OK, Saya Perbaiki'
        });
        @endif
    });
</script>
