<?php $setting = App\Helpers\Utility::setting();?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('meta')
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link href="/assets/css/style.css" media="screen" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{Storage::disk('admin')->url($setting->favicon)}}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light clearfix" role="navigation" id="BB-nav">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="logo"> <a href="index.html"><img src="{{Storage::disk('admin')->url($setting->logo)}}" alt="" /></a> </h1>
                <div class="h_box clearfix">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="menu clearfix">
                            <li class="active"><a href="#">Trang chủ</a></li>
                            <li><a href="#sp">Sản phẩm và Dịch vụ</a></li>
                            <li><a href="#tscct">Triển khai</a></li>
                            <li><a href="#lh">Liên hệ hỗ trợ</a></li>
                        </ul>
                    </div>
                    <div class="h_phone d-none"><a href="https://pk.healthchain.vn/portal" target="_blank">Patient Portal</a><a href="https://pk.healthchain.vn"
                                                                                                                                target="_blank">Đăng nhập</a></div>
                </div>
            </div>
        </div>
    </div>
</nav>
@yield('content')
<section class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <h2 class="f_logo"><a href="/"><img id="img-footer" src="{{Storage::disk('admin')->url($setting->logo_footer)}}" alt="" /></a></h2>
                <p class="f_text">
                    {!! $setting->textfooter !!}
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <h3>Thông tin liên hệ</h3>
                <ul class="f_link">
                    <li>
                        {{$setting->address}}
                    </li>
                    <li>
                        <a href="tel:+84{{substr($setting->phone,1)}}">{{$setting->phone}}</a>
                    </li>
                    <li>
                        <a href="mailto:{{$setting->email}}?subject=Liên hệ công việc">{{$setting->email}}</a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                <h3>Liên kết nhanh</h3>
                <ul class="f_menu">
                    <li><a href="#gt">Giới thiệu</a></li>
                    <li><a href="#sp">Dịch vụ</a></li>
                    <li><a href="#lh">Liên hệ</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="copy-right">
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <p>&copy; Copyright 2025 - Truong An Digital Data Conversion Jsc.</p>
            </div>
        </div>
    </div>
</section>
<p class="scrolltop"><a href="#" class="scrollToTop"><img src="/assets/images/pageTop.png" alt="" /></a></p>
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/popper.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery-1.12.4.min.js"></script>
<script type="text/javascript">
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (scroll >= 90) {
            $("#BB-nav").addClass("fixHeader");
        } else {
            $("#BB-nav").removeClass("fixHeader");
        }
    });
</script>
<script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "100%";
    }
    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
    $(document).ready(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 500) {
                $('.scrollToTop').fadeIn();
            } else {
                $('.scrollToTop').fadeOut();
            }
        });
        $('.scrollToTop').click(function () {
            $('html, body').animate({ scrollTop: 0 }, 800);
            return false;
        });
    });

</script>



</body>

</html>
