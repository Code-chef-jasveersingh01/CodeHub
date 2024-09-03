@extends('errors.layout')

@section('title', __('main.unauthorized'))
@section('code', '401')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-401.svg') }}" alt="" class="error-basic-img move-animation">
    <h6>{{ __('message.User not authorized') }}</h6>
</div>
@endsection
