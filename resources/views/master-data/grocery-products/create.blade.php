@extends('layouts.app')

@section('title', __('grocery.master_data.create_title'))

@section('content')
    <livewire:forms.grocery-product-form />
@endsection
