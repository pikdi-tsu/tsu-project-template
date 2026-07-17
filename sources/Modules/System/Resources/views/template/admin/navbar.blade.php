<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link"><i class="fa fa-circle fa-sm text-success"></i> Online</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        @php
            $unreadCount = Auth::user()->unreadNotifications->count();
            $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        @endphp
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge" id="main-notification-badge" style="display: {{ $unreadCount > 0 ? 'inline-block' : 'none' }};">{{ $unreadCount }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notification-dropdown-menu">
                <span class="dropdown-item dropdown-header font-weight-bold bg-light text-left" id="inbox-header">
                    <i class="fas fa-inbox mr-1"></i> Kotak Masuk (<span id="inbox-count">{{ $unreadCount }}</span> Baru)
                </span>
                <div class="dropdown-divider"></div>

                <div id="notification-list" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                    @forelse($notifications as $notification)
                        @php
                            $isUnread = is_null($notification->read_at);
                            $bgColor = $isUnread ? 'bg-light' : 'bg-white';
                            $textColor = $isUnread ? 'text-dark font-weight-bold' : 'text-muted';
                            $data = $notification->data;
                            $icon = $data['icon'] ?? 'fas fa-bell text-secondary';
                            $actionText = $data['action_text'] ?? 'Detail';
                        @endphp
                        <a href="{{ $data['action_url'] ?? '#' }}" class="dropdown-item {{ $bgColor }} border-bottom db-notif-item" style="white-space: normal;">
                            <div class="media">
                                <i class="{{ $icon }} mr-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div class="media-body">
                                    <h3 class="dropdown-item-title mb-1 {{ $textColor }}">{{ $data['title'] ?? 'Pemberitahuan Sistem' }}</h3>
                                    <p class="text-sm {{ $textColor }} mb-1">
                                        @if($isUnread)
                                            <i class="fas fa-circle text-primary mr-1" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        @endif
                                        {{ Str::limit($data['message'] ?? '', 60) }}
                                    </p>
                                    @if(isset($data['download_url']))
                                        <span class="badge badge-success mt-1"><i class="fas fa-download"></i> File Siap</span>
                                    @elseif(isset($data['error_detail']))
                                        <span class="badge badge-danger mt-1">Gagal</span>
                                    @else
                                        <span class="badge badge-primary mt-1"><i class="fas fa-arrow-right"></i> {{ $actionText }}</span>
                                    @endif
                                    <p class="text-xs text-muted mb-0 mt-1">
                                        <i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    @empty
                        <a href="#" class="dropdown-item text-center text-muted py-3" id="empty-notif-msg">
                            <i class="fas fa-check-circle mb-2" style="font-size: 1.5rem;"></i><br>
                            Tidak ada notifikasi baru
                        </a>
                        <div class="dropdown-divider"></div>
                    @endforelse
                </div>

                <a href="{{ route('system.notifications.index') ?? '#' }}" class="dropdown-item dropdown-footer text-center">Lihat Semua Notifikasi</a>
            </div>
        </li>

        <li class="dropdown user user-menu" style="margin-top: 8px;">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {{-- <img src="{{ asset('public/assets/dist/img/user2-160x160.jpg') }}" class="user-image" alt="User Image"> --}}
                <i class="fas fa-user-cog"></i>
                {{-- <span class="hidden-xs">Hi, {{Auth::user()->name}}</span> --}}
            </a>
            <ul class="dropdown-menu">
                <!-- User image -->
                <li class="user-header">
                    <img src="{{ Auth::user()->profile_photo_url }}" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #adb5bd;" class="img-circle" alt="User Image">
                    <p>
                        {{Auth::user()->name}}
                        <small> </small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <form action="{{route('logout')}}" method="POST" id="form-logout">
                        @csrf
                    </form>
                    <a href="{{route('users.profile.index')}}" class="btn btn-primary">Profile</a>
                    <button type="submit" class="btn btn-danger float-right" form="form-logout" style="background-color: red;">Sign out</button>
                </li>
            </ul>
        </li>
{{--        <li class="nav-item">--}}
{{--            <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Zoom Page">--}}
{{--                <i class="fas fa-expand-arrows-alt"></i>--}}
{{--            </a>--}}
{{--        </li>--}}
    </ul>
</nav>
<!-- /.navbar -->
