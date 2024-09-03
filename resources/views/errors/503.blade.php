@extends('errors.layout')

@section('title', __('main.service_unavailable'))
@section('code', '503')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-503.svg') }}" alt="" class="error-basic-img move-animation">
</div>
@endsection
