@extends('system::template.admin.header')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-inbox text-primary mr-2"></i> Kotak Masuk Notifikasi</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @if(isset($filter) && $filter == 'unread')
                        <form action="{{ route('system.notifications.readAll') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                            </button>
                        </form>
                    @elseif(isset($filter) && $filter == 'read' && isset($readCount) && $readCount > 0)
                        <a href="{{ route('system.notifications.backupClear') }}" class="btn btn-danger btn-backup-clear">
                            <i class="fas fa-file-excel mr-1"></i> Backup & Bersihkan Riwayat
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header p-2">
                    @php $filter = $filter ?? 'all'; @endphp
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ $filter == 'unread' ? 'active' : '' }}" href="{{ route('system.notifications.index', ['filter' => 'unread']) }}">
                                <i class="fas fa-envelope mr-1"></i> Pesan Baru
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span class="badge badge-warning ml-1">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $filter == 'read' ? 'active' : '' }}" href="{{ route('system.notifications.index', ['filter' => 'read']) }}">
                                <i class="fas fa-history mr-1"></i> Riwayat (Sudah Dibaca)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $filter == 'all' ? 'active' : '' }}" href="{{ route('system.notifications.index', ['filter' => 'all']) }}">
                                <i class="fas fa-list mr-1"></i> Semua
                            </a>
                        </li>
                    </ul>
                </div><!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <tbody>
                                @forelse($notifications as $notif)
                                    @php
                                        $isUnread = is_null($notif->read_at);
                                        $bgColor = $isUnread ? 'bg-light' : '';
                                        $fontWeight = $isUnread ? 'font-weight-bold' : '';
                                        $data = $notif->data;
                                        
                                        $icon = 'fas fa-bell text-secondary';
                                        $title = $data['title'] ?? 'Pemberitahuan';
                                        
                                        if (isset($data['statusatasan'])) {
                                            if ($data['statusatasan'] == 'export-ready') {
                                                $icon = 'fas fa-file-excel text-success';
                                                $title = 'Export Selesai';
                                            } elseif ($data['statusatasan'] == 'export-failed') {
                                                $icon = 'fas fa-exclamation-triangle text-danger';
                                                $title = 'Export Gagal';
                                            }
                                        }
                                        
                                        if (isset($data['module'])) {
                                            if ($data['module'] == 'izin') {
                                                $icon = 'fas fa-envelope-open-text text-primary';
                                            } elseif ($data['module'] == 'cuti') {
                                                $icon = 'fas fa-umbrella-beach text-warning';
                                            } elseif ($data['module'] == 'lembur') {
                                                $icon = 'fas fa-clock text-info';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $bgColor }}">
                                        <td class="text-center align-middle" style="width: 50px;">
                                            <i class="{{ $icon }} fa-lg"></i>
                                        </td>
                                        <td class="align-middle {{ $fontWeight }}">
                                            <div class="d-block mb-1 text-dark">{{ $title }}</div>
                                            <div class="text-sm text-muted">
                                                {{ $data['message'] ?? 'Ada notifikasi sistem baru.' }}
                                                @if(isset($data['error_detail']))
                                                    <br><span class="text-danger"><i class="fas fa-times-circle"></i> {{ $data['error_detail'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle text-right" style="width: 250px;">
                                            <span class="text-muted text-sm d-block mb-2"><i class="far fa-clock"></i> {{ $notif->created_at->format('d M Y, H:i') }}</span>
                                            
                                            <div class="btn-group">
                                                @if(isset($data['action_url']))
                                                    <a href="{{ route('system.notifications.read', $notif->id) }}" class="btn btn-sm btn-primary" title="{{ $data['action_text'] ?? 'Proses' }}">
                                                        <i class="fas fa-arrow-right"></i> {{ $data['action_text'] ?? 'Proses' }}
                                                    </a>
                                                @elseif(isset($data['download_url']))
                                                    <a href="{{ route('system.notifications.read', $notif->id) }}" class="btn btn-sm btn-success" title="Download">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                @endif
                                                
                                                @if($isUnread)
                                                    <a href="{{ route('system.notifications.read', $notif->id) }}" class="btn btn-sm btn-outline-secondary" title="Tandai Dibaca">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary disabled" title="Sudah Dibaca" style="pointer-events: none;">
                                                        <i class="fas fa-check-double text-primary"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                            @if($filter == 'unread')
                                                <h5>Horee! Kosong.</h5>
                                                <p>Tidak ada pesan baru yang masuk.</p>
                                            @elseif($filter == 'read')
                                                <h5>Belum ada riwayat</h5>
                                                <p>Notifikasi yang sudah dibaca akan dikumpulkan di sini.</p>
                                            @else
                                                <h5>Belum ada notifikasi sama sekali</h5>
                                                <p>Semua notifikasi sistem yang masuk akan tampil di sini.</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-end">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-backup-clear').on('click', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                
                Swal.fire({
                    title: 'Backup & Bersihkan?',
                    text: "Anda yakin ingin mem-backup {{ isset($readCount) ? $readCount : '' }} riwayat ini ke Excel dan menghapusnya dari sistem?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Backup & Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endsection
