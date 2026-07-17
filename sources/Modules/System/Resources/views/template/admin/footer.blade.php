<footer class="main-footer">
    <strong>Copyright &copy; {{ date('Y') }} <a href="https://adminlte.io">Tiga Serangkai University</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.2.0
    </div>
</footer>

<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('public/assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('public/assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('public/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('public/assets/plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('public/assets/plugins/sparklines/sparkline.js') }}"></script>
<!-- JQVMap -->
<script src="{{ asset('public/assets/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('public/assets/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('public/assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('public/assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('public/assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('public/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('public/assets/dist/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
{{-- <script src="{{ asset('public/assets/dist/js/demo.js') }}"></script> --}}
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{-- <script src="{{ asset('public/assets/dist/js/pages/dashboard.js') }}"></script> --}}
{{-- alert --}}
{{--<script src="{{ asset('public/assets/dist/js/sweetalert.js') }}"></script>--}}
<!-- DataTables  & Plugins -->
<script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script>
    @if (Session::has('alert'))
        Swal.fire('{{ session('alert')['title'] }}', '{{ session('alert')['message'] }}',
            '{{ session('alert')['status'] }}')
    @endif

    bsCustomFileInput.init();
</script>
@include('system::components.alert')
{{-- WebSockets / Laravel Reverb Setup --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    if (typeof Echo !== 'undefined' && typeof Pusher !== 'undefined') {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 80) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 443) }},
            forceTLS: {{ config('broadcasting.connections.reverb.options.scheme', 'http') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }
        });
        
        // Add Listener
        @auth
            window.Echo.private('App.Models.User.{{ Auth::user()->id }}')
                .notification((notification) => {
                    
                    // 1. Silent Notification Check
                    // Jika is_silent true, jangan tambah angka di badge
                    let shouldIncrementBadge = (notification.is_silent !== true);
                    
                    if (shouldIncrementBadge) {
                        // Update Unread Badge Count on Navbar
                        let badge = $('#main-notification-badge');
                        let count = parseInt(badge.text()) || 0;
                        count++;
                        badge.text(count).show();

                        let inboxCount = $('#inbox-count');
                        if (inboxCount.length) {
                            inboxCount.text(parseInt(inboxCount.text() || 0) + 1);
                        }
                        
                        // Update Sidebar Badge (jika nama rute sesuai dengan modul)
                        if (notification.module) {
                            let sidebarBadge = $('#sidebar-badge-' + notification.module);
                            if (sidebarBadge.length > 0) {
                                let sbCount = parseInt(sidebarBadge.text()) || 0;
                                sbCount++;
                                sidebarBadge.text(sbCount);
                            } else {
                                // Inject badge dynamically jika elemennya belum dirender (Rule #3)
                                let sidebarLink = $('a.nav-link[href*="' + notification.module + '"]');
                                if (sidebarLink.length > 0) {
                                    sidebarLink.find('p').append('<span id="sidebar-badge-' + notification.module + '" class="right badge badge-danger">1</span>');
                                }
                            }
                        }
                    }
                    
                    // 2. Inject Notification HTML to Inbox Dropdown
                    let actionText = notification.action_text || 'Detail';
                    let icon = notification.icon || 'fas fa-bell text-secondary';

                    let notifHtml = `
                        <a href="${notification.action_url || '#'}" class="dropdown-item font-weight-bold bg-light db-notif-item" style="white-space: normal;">
                            <div class="media">
                                <i class="${icon} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <h3 class="dropdown-item-title mb-1 text-dark font-weight-bold">${notification.title || 'Pemberitahuan Sistem'}</h3>
                                    <p class="text-sm text-dark font-weight-bold mb-1">
                                        <i class="fas fa-circle text-primary mr-1" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        ${notification.message || ''}
                                    </p>
                                    ${
                                        notification.download_url 
                                        ? '<span class="badge badge-success mt-1"><i class="fas fa-download"></i> File Siap</span>' 
                                        : (notification.error_detail 
                                            ? '<span class="badge badge-danger mt-1">Gagal</span>' 
                                            : `<span class="badge badge-primary mt-1"><i class="fas fa-arrow-right"></i> ${actionText}</span>`)
                                    }
                                    <p class="text-xs text-muted mb-0 mt-1"><i class="far fa-clock mr-1"></i> Baru Saja</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                    
                    $('#empty-notif-msg').hide();
                    $('#notification-list').prepend(notifHtml);
                    
                    // Jaga batas 5 notifikasi di dropdown
                    if ($('#notification-list a.dropdown-item').length > 5) {
                        $('#notification-list a.dropdown-item:last').next('.dropdown-divider').remove();
                        $('#notification-list a.dropdown-item:last').remove();
                    }
                    
                    // 3. Show SweetAlert2 Toast (Pause on Hover)
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: 'info',
                        title: notification.message || 'Pemberitahuan Baru'
                    });
                });
        @endauth
    }
</script>

@yield('script')
