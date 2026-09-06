@extends('layouts.app')

@section('title', __('grocery.edit.title'))

@section('content')
    <livewire:forms.grocery-item-form :grocery-item="$groceryItem" />
@endsection
