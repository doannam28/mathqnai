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

@stop

