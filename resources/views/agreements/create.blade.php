@extends('layouts.app')

@section('title', 'New Agreement')
@section('subtitle', 'Create a service agreement')

@section('content')
    <form method="POST" action="{{ route('agreements.store') }}">
        @csrf
        @include('agreements._form', ['submit' => 'Create Agreement'])
    </form>
@endsection
