@extends('layouts.app')

@section('title', 'Edit Agreement')
@section('subtitle', $agreement->title)

@section('content')
    <form method="POST" action="{{ route('agreements.update', $agreement) }}">
        @csrf @method('PUT')
        @include('agreements._form', ['submit' => 'Save Changes'])
    </form>
@endsection
