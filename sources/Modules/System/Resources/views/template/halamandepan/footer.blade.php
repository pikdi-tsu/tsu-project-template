    <!--================ Start footer Area  =================-->
    <footer class="footer-area section_gap">
      <div class="container">
        <div class="row footer-bottom d-flex justify-content-between">
          <p class="col footer-text m-0 text-white text-center">
                Copyright &copy; {{ date('Y') }} <a href="{{ route('indexing') }}">Tiga Serangkai University</a> All rights reserved
          </p>
        </div>
      </div>
    </footer>
    <!--================ End footer Area  =================-->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="{{asset('public/assetsku/js/jquery-3.2.1.min.js')}}"></script>
    <script src="{{asset('public/assetsku/js/popper.js')}}"></script>
    <script src="{{asset('public/assetsku/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('public/assetsku/vendors/nice-select/js/jquery.nice-select.min.js')}}"></script>
    <script src="{{asset('public/assetsku/vendors/owl-carousel/owl.carousel.min.js')}}"></script>
    <script src="{{asset('public/assetsku/js/owl-carousel-thumb.min.js')}}"></script>
    <script src="{{asset('public/assetsku/js/jquery.ajaxchimp.min.js')}}"></script>
    <script src="{{asset('public/assetsku/js/mail-script.js')}}"></script>
    <!--gmaps Js-->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjCGmQ0Uq4exrzdcL6rvxywDDOvfAu6eE"></script>
    <script src="{{asset('public/assetsku/js/gmaps.min.js')}}"></script>
    <script src="{{asset('public/assetsku/js/theme.js')}}"></script>
{{--    <script src="{{ asset('public/assets/dist/js/sweetalert.js') }}"></script>--}}

    <script>
        @if (Session::has('alert'))
            Swal.fire('{{session('alert')['title']}}', '{{session('alert')['message']}}', '{{session('alert')['status']}}')
        @endif
    </script>
    @include('system::components.alert')
    @yield('script')
