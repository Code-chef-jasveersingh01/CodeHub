<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/error-title.ico') }}">
    <!-- Layout config Js -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .auth-one-bg{
            background-image:url('{{ asset('assets/images/company/error-bg.png') }}') !important;
            filter: blur(1px)  !important;
            background-position:center  !important;
            background-size:cover  !important;
        }
        .auth-one-bg .bg-overlay{
            background: linear-gradient(to right, #38c7ee, #3d78e3)  !important;
            opacity: .9  !important;
        }
        .bg-overlay{
            background-image:url('{{ asset('assets/images/company/error-bg.png') }}') !important;
            background-position:center  !important;
            background-size: 100% 105%  !important;
            background-repeat: no-repeat  !important;
            opacity:1  !important;
            background-color:transparent  !important;
        }
        .error-content{
            margin-top: 20%;
        }
    </style>
</head>
<body>
    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center pt-4 error-content">
                            @yield('error')
                            <div class="main">
                                @yield('message')
                                <a class="btn btn-info rounded-circle" href="{{ (Auth::check()) ? route('admin.dashboard') : route('login') }}"> {{ __('main.go_back') }} </a>
                            </div>

                        </div>
                    </div>
                </div>
        </div>
    </div>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/js/plugins.js') }}"></script>
   <script src="{{ asset('assets/libs/particles.js/particles.js')}}"></script>
   <script src="{{ asset('assets/js/pages/particles.app.js')}}"></script>
   <script>
    //redirect after 3 seconds to dashboard
    setTimeout(function () {
        window.location.href = "{{ (Auth::check()) ? route('admin.dashboard') : route('login') }}";
    }, 1000);
   </script>
</body>
</html>
