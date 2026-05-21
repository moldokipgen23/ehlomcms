@extends('layouts.app')

@section('title', 'Edit Domain')
@section('subtitle', $domain->domain_name)

@section('content')
    <form method="POST" action="{{ route('domains.update', $domain) }}">
        @csrf @method('PUT')
        @include('domains._form', ['submit' => 'Save Changes'])
    </form>
@endsection
