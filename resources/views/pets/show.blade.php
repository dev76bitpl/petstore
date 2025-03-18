@extends('layouts.app')

@section('content')
    <h1>Pet Details</h1>

    <p><strong>Name:</strong> {{ $pet->name }}</p>
    <p><strong>Status:</strong> {{ $pet->status }}</p>
    <p><strong>Category:</strong> {{ $pet->category_name ?? 'No category' }}</p>

    <a href="{{ route('pets.edit', $pet->id) }}">Edit</a>

    <form action="{{ route('pets.destroy', $pet->id) }}" method="POST" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit">Delete</button>
    </form>

    <a href="{{ route('pets.index') }}">Back to list</a>
@endsection
