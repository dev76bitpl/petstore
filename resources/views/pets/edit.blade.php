@extends('layouts.app')

@section('content')
    <h1>Edit Pet: {{ $pet->name }}</h1>

    @if ($errors->any())
        <div>
            <strong>Whoops! Something went wrong.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pets.update', $pet->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="name">Pet Name:</label>
        <input type="text" name="name" value="{{ old('name', $pet->name) }}" required>

        <label for="category_id">Category ID:</label>
        <input type="number" name="category_id" value="{{ old('category_id', $pet->category_id ?? '') }}">

        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" value="{{ old('category_name', $pet->category_name ?? '') }}">

        <label for="photoUrls">Photo URLs:</label>
        @foreach ($pet->photoUrls as $index => $photo)
            <input type="text" name="photoUrls[{{ $index }}]" value="{{ $photo }}">
        @endforeach
        <button type="button" onclick="addPhotoUrl()">+ Add another photo</button>
        <div id="photoUrlsContainer"></div>

        <label for="tags">Tags:</label>
        @foreach ($pet->tags as $index => $tag)
            <input type="text" name="tags[{{ $index }}][id]" value="{{ $tag['id'] }}" hidden>
            <input type="text" name="tags[{{ $index }}][name]" value="{{ $tag['name'] }}">
        @endforeach
        <button type="button" onclick="addTag()">+ Add another tag</button>
        <div id="tagsContainer"></div>

        <label for="status">Status:</label>
        <select name="status">
            <option value="available" {{ $pet->status == 'available' ? 'selected' : '' }}>Available</option>
            <option value="pending" {{ $pet->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="sold" {{ $pet->status == 'sold' ? 'selected' : '' }}>Sold</option>
        </select>

        <button type="submit">Update Pet</button>
    </form>

    <a href="{{ route('pets.index') }}">Back to list</a>

    <script>
        function addPhotoUrl() {
            let container = document.getElementById('photoUrlsContainer');
            let index = container.getElementsByTagName('input').length;
            let input = document.createElement('input');
            input.type = 'text';
            input.name = 'photoUrls[' + index + ']';
            input.placeholder = 'Enter another photo URL';
            container.appendChild(document.createElement('br'));
            container.appendChild(input);
        }

        function addTag() {
            let container = document.getElementById('tagsContainer');
            let index = container.getElementsByTagName('input').length / 2;

            let tagId = document.createElement('input');
            tagId.type = 'text';
            tagId.name = 'tags[' + index + '][id]';
            tagId.value = Math.floor(Math.random() * 100) + 1;
            tagId.hidden = true;

            let tagName = document.createElement('input');
            tagName.type = 'text';
            tagName.name = 'tags[' + index + '][name]';
            tagName.placeholder = 'Enter tag name';

            container.appendChild(document.createElement('br'));
            container.appendChild(tagId);
            container.appendChild(tagName);
        }
    </script>
@endsection
