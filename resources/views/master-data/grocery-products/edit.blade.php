@extends('layouts.app')

@section('title', __('grocery.master_data.edit_title'))

@section('content')
    <livewire:forms.grocery-product-form :grocery-product="$groceryProduct" />
@endsection
