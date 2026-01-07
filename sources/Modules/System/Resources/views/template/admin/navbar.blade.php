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
        <li class="nav-item dropdown" style="margin-right: 10px;">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> 4 new messages
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-users mr-2"></i> 8 friend requests
                    <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> 3 new reports
                    <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>

        <li class="dropdown user user-menu" style="margin-top: 8px;">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {{-- <img src="{{ asset('public/assets/dist/img/user2-160x160.jpg') }}" class="user-image" alt="User Image"> --}}
                <i class="fas fa-users-cog"></i>
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
                    <a href="{{route('profile')}}" class="btn btn-primary">Profile</a>
                    <button type="submit" class="btn btn-danger float-right" form="form-logout" style="background-color: red;">Sign out</button>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Zoom Page">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
