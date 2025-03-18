<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet App</title>
</head>
<body>
    <nav>
        <a href="{{ route('pets.index') }}">Home</a>
    </nav>

    @if (session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="background: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px;">
            {{ session('warning') }}
        </div>
    @endif

    <div>
        @yield('content')
    </div>
</body>
</html>
