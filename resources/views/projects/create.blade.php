@extends('layouts.app')

@section('title', 'New Project')
@section('subtitle', 'Create a project')

@section('content')
    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        @include('projects._form', ['submit' => 'Create Project'])
    </form>
@endsection
