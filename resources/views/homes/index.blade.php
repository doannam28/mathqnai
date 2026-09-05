@extends('layouts.app')
@section('meta')
    <?php use App\Helpers\Utility;$setting = Utility::setting();
    $content = isset($setting->content) ? json_decode($setting->content) : '';
    ?>
    <title>{{$setting->site_title}}</title>
    <meta name="description" content="{{$setting->meta_description}}">
    <meta property="og:title" content="{{$setting->site_title}}">
    <meta name="keywords" content="{{$setting->site_title}}">
    <meta property="og:description" content="{{$setting->meta_description}}">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{Storage::disk('admin')->url($setting->image_og)}}"/>
    <meta name="twitter:card" content="{{$setting->meta_description}}"/>
    <meta name="twitter:site" content="https://tajsc.vn"/>
    <meta name="twitter:title" content="{{$setting->site_title}}"/>
    <meta name="twitter:description" content="{{$setting->meta_description}}"/>
@endsection
@section('content')
    <section class="banner" id="gt">
        <div class="banner_item">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="banner_info">
                            <h2>Giới thiệu về <span>{{$setting->name}}</span></h2>
                            <p class="banner_text">{!! $content->soicau !!}
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <p class="banner_item_img"><img src="{{Storage::disk('admin')->url($setting->img_soicau)}}" alt="" /></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sp" id="sp">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <h2>Sản phẩm và Dịch vụ</h2>
                    <p class="sp_text">
                        Đội ngũ chuyên gia kỹ thuật sẵn sàng hỗ trợ 24/7, chúng tôi luôn nỗ lực mang đến các sản phẩm và
                        dịch vụ chất lượng nhất cho quý khách hàng
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="sp_item">
                        <h3>{{$content->title1}}</h3>
                       {!! $setting->text1 !!}
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="sp_item sp_item2">
                        <h3>{{$content->title2}}</h3>
                        {!! $setting->text2 !!}
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="sp_item sp_item3">
                        <h3>{{$content->title3}}</h3>
                        {!! $setting->texttag !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tscct" id="tscct">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <h2>TẠI SAO CHỌN CHÚNG TÔI?</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-8">
                    {!! $setting->copyright !!}
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <p class="tscct_img"><img src="{{Storage::disk('admin')->url($setting->img_why)}}" alt="" /></p>
                </div>
            </div>
        </div>
    </section>

    <section class="lhht" id="lh">
        <div class="container">
            <h2>Liên hệ hỗ trợ</h2>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="lhht_box">
                        <p class="lhht_box_txt">Chúng tôi mong muốn lắng nghe, tiếp nhận mọi ý kiến phản hồi của quý
                            khách hàng để không ngừng hoàn thiện sản phẩm, nâng cao chất lượng dịch vụ thông qua các
                            kênh sau</p>
                        <div class="lhht_item">
                            <p class="lhht_item_img"><img src="/assets/images/lhht_phone.svg" alt="" /></p>
                            <div class="lhht_item_info">
                                <h3>Hotline</h3>
                                <p><a href="#">{{$setting->phone}}</a></p>
                            </div>
                        </div>
                        <div class="lhht_item">
                            <p class="lhht_item_img"><img src="/assets/images/lhht_mail.svg" alt="" /></p>
                            <div class="lhht_item_info">
                                <h3>Email</h3>
                                <p><a href="mailto:{{$setting->email}}?subject=Liên hệ công việc">{{$setting->email}}</a></p>
                            </div>
                        </div>
                        <div class="lhht_item">
                            <p class="lhht_item_img"><img src="/assets/images/lhht_zalo.svg" alt="" /></p>
                            <div class="lhht_item_info">
                                <h3>Zalo</h3>
                                <p>{{$content->zalo}}</p>
                            </div>
                        </div>
                        <div class="lhht_item">
                            <p class="lhht_item_img"><img src="/assets/images/lhht_add.svg" alt="" /></p>
                            <div class="lhht_item_info">
                                <h3>Địa chỉ</h3>
                                <p>{{$setting->address}}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="lhht_form">
                        <form id="contactForm" enctype="multipart/form-data" method="post" class="form-contact needs-validation" novalidate>
                            @csrf
                            <div class="lhht_2col">
                                <div class="lhht_form_item">
                                    <input type="text" placeholder="Email người gửi" name="email" class="form-control" required id="fromemail"/>
                                    <div class="invalid-feedback">
                                        Xin vui lòng nhập email.
                                    </div>
                                </div>
                                <div class="lhht_form_item">
                                    <input type="text"  placeholder="Số điện thoại" name="phone" class="form-control" id="sodienthoai" required/>
                                    <div class="invalid-feedback">
                                        Xin vui lòng nhập số điện thoại.
                                    </div>
                                </div>
                            </div>
                            <div class="lhht_form_item">
                                <input type="text" placeholder="Tiêu đề" class="form-control" name="title" id="subjectemail" required/>
                                <div class="invalid-feedback">
                                    Xin vui lòng nhập tiêu đề.
                                </div>
                            </div>
                            <div class="lhht_form_item">
                                <textarea placeholder="Nội dung liên hệ" class="form-control" name="content" id="bodyemail" required></textarea>
                                <div class="invalid-feedback">
                                    Xin vui lòng nhập nội dung.
                                </div>
                            </div>
                            <div class="lhht_form_item">
                                <button type="submit" id="submitBtn">Gửi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var form = document.getElementById('contactForm');

                form.addEventListener('submit', function(event) {
                    event.preventDefault(); // Ngăn submit mặc định
                    event.stopPropagation();

                    if (form.checkValidity() === false) {
                        form.classList.add('was-validated');
                        return;
                    }

                    form.classList.add('was-validated');

                    let formData = new FormData(form);

                    fetch("{{ url('/send') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success){
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Thành công!',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                });
                                form.reset();
                                form.classList.remove('was-validated');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi!',
                                    text: 'Gửi mail thất bại: ' + (data.message || 'Lỗi server'),
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Đã xảy ra lỗi khi gửi mail.');
                        });
                });
            }, false);
        })();
    </script>
@stop

