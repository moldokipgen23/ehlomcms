@extends('layouts.app')

@section('title', 'New Product')
@section('subtitle', 'Add a product or service')

@section('content')
    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form', ['submit' => 'Create Product'])
    </form>
@endsection
