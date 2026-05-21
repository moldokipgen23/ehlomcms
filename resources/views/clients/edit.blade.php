@extends('layouts.app')

@section('title', 'Edit Client')
@section('subtitle', $client->name)

@section('content')
    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf @method('PUT')
        @include('clients._form', ['submit' => 'Save Changes', 'showStatus' => true])
    </form>
@endsection
