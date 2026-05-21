@extends('layouts.app')

@section('title', 'Edit Project')
@section('subtitle', $project->title)

@section('content')
    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf @method('PUT')
        @include('projects._form', ['submit' => 'Save Changes'])
    </form>
@endsection
