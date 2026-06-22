@extends('layouts.app')

@section('title', 'Edit Lead')
@section('subtitle', $lead->name)

@section('content')
    <form method="POST" action="{{ route('leads.update', $lead) }}">
        @csrf @method('PUT')
        @include('leads._form', ['submit' => 'Save Changes'])
    </form>
@endsection
