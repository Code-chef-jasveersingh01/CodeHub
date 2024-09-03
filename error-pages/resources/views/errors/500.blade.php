@extends('errors.layout')

@section('title', __('main.server_error'))
@section('code', '500')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-500.svg') }}" alt="" class="error-basic-img move-animation">
</div>
@endsection
