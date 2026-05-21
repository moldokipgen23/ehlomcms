@extends('layouts.app')

@section('title', 'Edit Product')
@section('subtitle', $product->name)

@section('content')
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products._form', ['submit' => 'Save Changes'])
    </form>
@endsection
