@extends('layouts.app')

@section('title', 'New Subscription')
@section('subtitle', 'Assign a product to a client')

@section('content')
    <form method="POST" action="{{ route('subscriptions.store') }}">
        @csrf
        @include('subscriptions._form', ['submit' => 'Create Subscription'])
    </form>
@endsection
