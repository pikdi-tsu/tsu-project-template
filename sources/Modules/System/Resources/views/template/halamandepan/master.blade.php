<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <link rel="icon" href="{{asset('public/assetsku/img/logotsu.png')}}" type="image/png"/>
        <title>Tiga Serangkai University</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{asset('public/assetsku/css/bootstrap.css')}}" />
        <link rel="stylesheet" href="{{asset('public/assetsku/css/flaticon.css')}}" />
        <link rel="stylesheet" href="{{asset('public/assetsku/css/themify-icons.css')}}" />
        <link rel="stylesheet" href="{{asset('public/assetsku/vendors/owl-carousel/owl.carousel.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/assetsku/vendors/nice-select/css/nice-select.css')}}" />
        <!-- main css -->
        <link rel="stylesheet" href="{{asset('public/assetsku/css/style.css')}}" />
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="{{asset('public/assets/plugins/fontawesome-free-7.1.0-web/css/all.min.css')}}">
        <!-- SweetAlert 2 -->
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
        <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
        @yield('link_href')
    </head>

    <body>
        @include('system::template/halamandepan/navbar')

        @yield('content')

        @include('system::template/halamandepan/footer')
    </body>
</html>
