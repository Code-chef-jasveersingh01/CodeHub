@extends('errors.layout')

@section('title', __('main.payment_required'))
@section('code', '402')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-402.svg') }}" alt="" class="error-basic-img move-animation">
    <h1 class="display-1 fw-semibold">402</h1>
    <h6>{{ __('main.payment_required') }}</h6>
</div>
@endsection
