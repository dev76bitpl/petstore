@extends('layouts.app')

@section('content')
    <h1>List of Pets</h1>

    <a href="{{ route('pets.create') }}">Add New Pet</a>

    <!-- Przyciski do przełączania między pełną listą a zwierzętami użytkownika -->
    <div>
        <a href="{{ route('pets.index', ['filter' => 'all']) }}">
            <button {{ request('filter', 'my') == 'all' ? 'disabled' : '' }}>Show All Pets</button>
        </a>
        <a href="{{ route('pets.index', ['filter' => 'my']) }}">
            <button {{ request('filter', 'my') == 'my' ? 'disabled' : '' }}>Show My Pets</button>
        </a>
    </div>
    <hr/>

    <form method="GET" action="{{ route('pets.index') }}">
        <label for="limit">Show:</label>
        <select name="limit" onchange="this.form.submit()">
            @foreach([5,10,20,50] as $value)
                <option value="{{ $value }}" {{ request('limit') == $value ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
        </select> per page

        <input type="hidden" name="page" value="1">
        <input type="hidden" name="filter" value="{{ request('filter', 'my') }}">
        <input type="hidden" name="search" value="{{ request('search', '') }}">
    </form>

    <hr/>

    <form method="GET" action="{{ route('pets.index') }}">
        <input type="hidden" name="filter" value="{{ request('filter', 'my') }}">
        <label for="search">Search by name:</label>
        <input type="text" name="search" value="{{ request('search', '') }}">
        <button type="submit">Search</button>
        <a href="{{ route('pets.index', ['filter' => request('filter', 'my')]) }}">Clear</a>
    </form>

    <hr/>

    @if ($pets && count($pets) > 0)
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th>Tags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pets as $pet)
                <tr>
                    <td>{{ $pet->id }}</td>
                    <td>{{ $pet->name }}</td>
                    <td>{{ $pet->status }}</td>
                    <td>{{ $pet->category_name ?? 'No category' }}</td>
                    <td>
                        @foreach($pet->tags as $tag)
                            <span>{{ $tag['name'] }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('pets.edit', $pet->id) }}">Edit</a>
                        <form action="{{ route('pets.destroy', $pet->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div>
            @if ($lastPage > 1)
                @if($currentPage > 1)
                    <a href="{{ route('pets.index', array_merge(request()->except('page'), ['page' => $currentPage - 1])) }}">Previous</a>
                @endif

                <span>Page {{ $currentPage }} of {{ $lastPage }}</span>

                @if($currentPage < $lastPage)
                    <a href="{{ route('pets.index', array_merge(request()->except('page'), ['page' => $currentPage + 1])) }}">Next</a>
                @endif
            @endif
        </div>
        
        @else
        <p>No pets found.</p>
    @endif
@endsection
