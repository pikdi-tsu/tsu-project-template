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
        @yield('link_href')
    </head>

    <body>
        @include('system::template/halamandepan/navbar')

        @yield('content')

        @include('system::template/halamandepan/footer')

        <script>
            @if (Session::has('alert'))
                Swal.fire('{{session('alert')['title']}}', '{{session('alert')['message']}}', '{{session('alert')['status']}}')
            @endif

            function sweetAlert(alert, desc, text) {
                const Alert = Swal.mixin({
                    showConfirmButton: true,
                    timer: 3000
                });

                Alert.fire({
                    type: alert,
                    title: desc,
                    text: text,
                });
            }
        </script>
        @yield('script')
    </body>
</html>
