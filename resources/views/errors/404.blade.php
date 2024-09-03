@extends('errors.layout')

@section('title', __('main.not_found'))
@section('code', '404')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-404.svg') }}" alt="" class="error-basic-img move-animation">
    <h6>{{ __('message.The page you are looking for might have been removed, had its name changed,') }}<br />{{ __('message.or is temporarily unavailable.') }}</h6>
</div>
@endsection
