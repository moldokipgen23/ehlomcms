@extends('layouts.app')

@section('title', 'New Invoice')
@section('subtitle', 'Next number: ' . $nextNumber)

@section('content')
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        @include('invoices._form', ['submit' => 'Create Invoice', 'emailOption' => true])
    </form>
@endsection
