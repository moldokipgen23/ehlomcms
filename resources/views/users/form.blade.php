@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')
@section('subtitle', isset($user) ? 'Update account details' : 'Add a new admin or staff account')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;max-width:480px;">
        <div class="eos-card-header">
            <div class="eos-card-title">{{ isset($user) ? 'Edit' : 'New' }} User</div>
        </div>
        <div style="padding:16px;">
            <form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if (isset($user)) @method('PUT') @endif

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="eos-input">
                    @error('name')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="eos-input">
                    @error('email')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Password {{ isset($user) ? '(leave blank to keep current)' : '' }}</label>
                    <input type="password" name="password" class="eos-input" {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <div class="eos-field" style="margin-bottom:14px;">
                    <label class="eos-label">Role</label>
                    <select name="role_id" required class="eos-input">
                        <option value="">Select role...</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>{{ $role->label }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="eos-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="eos-btn eos-btn-primary">{{ isset($user) ? 'Update' : 'Create' }} User</button>
                <a href="{{ route('users.index') }}" class="eos-btn" style="border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text-secondary);">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
