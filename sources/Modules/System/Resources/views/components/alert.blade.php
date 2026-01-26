<script>
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
            html: `{!! session('warning') !!}`,
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
            html: `{!! session('error') !!}`,
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
            confirmButtonColor: '#f39c12',
            confirmButtonText: 'Siap, Saya Perbaiki'
        });
        @endif
    });
</script>
