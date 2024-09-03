@extends('errors.layout')

@section('title', __('main.page_expired'))
@section('code', '419')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error.svg') }}" alt="" class="error-basic-img move-animation">
    <h1 class="display-1 fw-semibold">419</h1>
    <h6>{{ __('main.page_expired') }}</h6>
</div>
@endsection
