@extends('layouts.app')

@section('title', 'New Client')
@section('subtitle', 'Add a new client')

@section('content')
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('clients._form', ['submit' => 'Create Client'])
    </form>
@endsection
