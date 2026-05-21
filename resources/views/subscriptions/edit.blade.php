@extends('layouts.app')

@section('title', 'Edit Subscription')
@section('subtitle', $subscription->client->name ?? '')

@section('content')
    <form method="POST" action="{{ route('subscriptions.update', $subscription) }}">
        @csrf @method('PUT')
        @include('subscriptions._form', ['submit' => 'Save Changes'])
    </form>
@endsection
