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
    <div>
        <form method="GET" action="{{ route('pets.index') }}">
            <label for="limit">Show:</label>
            <select name="limit" onchange="this.form.submit()">
                <option value="5" {{ request('limit') == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
            </select> per page
    
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="filter" value="{{ request('filter', 'my') }}">
            <input type="hidden" name="search" value="{{ request('search', '') }}">
        </form>
    </div>
    
<hr/>
    <!-- Wyszukiwarka -->
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
                    <th>Category</th>
                    <th>Photo</th>
                    <th>Tags</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pets as $pet)
                    <tr>
                        <td>{{ $pet['id'] ?? '-' }}</td>
                        <td><a href="{{ route('pets.show', $pet['id']) }}">{{ $pet['name'] ?? 'Unknown' }}</a></td>
                        <td>{{ $pet['category']['name'] ?? 'No Category' }}</td>
                        <td>
                            @if(isset($pet['photoUrls']) && count($pet['photoUrls']) > 0)
                                <img src="{{ $pet['photoUrls'][0] }}" alt="Pet Image" width="50" height="50">
                            @else
                                No Image
                            @endif
                        </td>
                        <td>
                            @if(isset($pet['tags']) && count($pet['tags']) > 0)
                                {{ implode(', ', array_column($pet['tags'], 'name')) }}
                            @else
                                No Tags
                            @endif
                        </td>
                        <td>{{ $pet['status'] ?? 'Unknown' }}</td>
                        <td>
                            <a href="{{ route('pets.edit', $pet['id']) }}">Edit</a>
                            <form action="{{ route('pets.destroy', $pet['id']) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
<hr/>
<div>
    @if ($lastPage > 1)
        <a href="{{ route('pets.index', ['page' => 1, 'limit' => request('limit', 10), 'filter' => request('filter', 'my'), 'search' => request('search', '')]) }}">First</a>

        @if($currentPage > 1)
            <a href="{{ route('pets.index', ['page' => $currentPage - 1, 'limit' => request('limit', 10), 'filter' => request('filter', 'my'), 'search' => request('search', '')]) }}">Previous</a>
        @endif

        <span>Page {{ $currentPage }} of {{ $lastPage }}</span>

        @if($currentPage < $lastPage)
            <a href="{{ route('pets.index', ['page' => $currentPage + 1, 'limit' => request('limit', 10), 'filter' => request('filter', 'my'), 'search' => request('search', '')]) }}">Next</a>
        @endif

        <a href="{{ route('pets.index', ['page' => $lastPage, 'limit' => request('limit', 10), 'filter' => request('filter', 'my'), 'search' => request('search', '')]) }}">Last</a>
    @endif
</div>

        

    @else
        <p>No pets found.</p>
    @endif
@endsection
