@extends('errors.layout')

@section('title', __('main.forbidden'))
@section('code', '403')
@section('error')
<div class="">
    <img src="{{ asset('assets/images/error-403.svg') }}" alt="" class="error-basic-img move-animation">
</div>
@endsection
