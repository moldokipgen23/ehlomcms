@extends('layouts.app')

@section('title', 'Edit Invoice')
@section('subtitle', $invoice->invoice_number)

@section('content')
    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        @include('invoices._form', ['submit' => 'Save Changes'])
    </form>
@endsection
