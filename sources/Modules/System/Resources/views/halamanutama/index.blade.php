@extends('system::template/halamandepan/master')
@section('title', 'Tiga Serangkai University')

@section('link_href')
@endsection
@section('content')

    <section class="home_banner_area">
        <div class="blurred-overlay"></div>
        <div class="banner_inner">
            <div class="container">
              <div class="row">
                <div class="col-lg-12">
                  <div class="banner_content text-center">
                    <p class="text-uppercase">
                      TIGA SERANGKAI UNIVERSITY
                    </p>
                    <h2 class="text-uppercase mt-4 mb-5">
                        {{ $project_name }}
                    </h2>
                    <div>
                      <a href="{{route('login')}}" class="primary-btn2 ml-sm-3 ml-0">Login</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </section>

@endsection
@section('script')

@endsection
