@extends('layouts.app')

@section('title', 'Add Domain')
@section('subtitle', 'Track a domain & hosting setup')

@section('content')
    <form method="POST" action="{{ route('domains.store') }}">
        @csrf
        @include('domains._form', ['submit' => 'Add Domain'])
    </form>
@endsection
