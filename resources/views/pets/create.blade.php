@extends('layouts.app')

@section('content')
    <h1>Add a New Pet</h1>

    @if ($errors->any())
        <div style="color: red;">
            <strong>Whoops! Something went wrong.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pets.store') }}" method="POST">
        @csrf

        <label for="name">Pet Name:</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
        <br><br>

        <label for="category_id">Category ID:</label>
        <input type="number" name="category_id" value="{{ old('category_id', rand(1, 10)) }}" required>
        <br><br>

        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" value="{{ old('category_name', 'General') }}" required>
        <br><br>

        <label for="photoUrls">Photo URL:</label>
        <input type="text" name="photoUrls[]" value="{{ old('photoUrls.0', 'https://picsum.photos/200/300') }}" required>
        <button type="button" onclick="addPhotoUrl()">+ Add another photo</button>
        <div id="photoUrlsContainer"></div>
        <br><br>

        <label for="tags">Tags:</label>
        <input type="text" name="tags[0][id]" value="{{ old('tags.0.id', rand(1, 100)) }}" hidden>
        <input type="text" name="tags[0][name]" value="{{ old('tags.0.name', 'friendly') }}" required>
        <button type="button" onclick="addTag()">+ Add another tag</button>
        <div id="tagsContainer"></div>
        <br><br>

        <label for="status">Status:</label>
        <select name="status">
            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
        </select>
        <br><br>

        <button type="submit">Save Pet</button>
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
