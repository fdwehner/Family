@extends('layouts.app')

@section('title', __('grocery.create.title'))

@section('content')
    <livewire:forms.grocery-item-form />
@endsection
