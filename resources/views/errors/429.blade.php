@extends('errors.layout')

@section('title', __('main.too_many_requests'))
@section('code', '429')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error.svg') }}" alt="" class="error-basic-img move-animation">
    <h1 class="display-1 fw-semibold">429</h1>
    <h6>{{ __('main.too_many_requests') }}</h6>
</div>
@endsection
