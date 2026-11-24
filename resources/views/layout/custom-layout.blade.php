<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice & Expense Tracker system</title>
    <meta name="description"
        content="Generate business invoices and track expenses with ease. Our system simplifies financial management for small businesses and freelancers.">
    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Fontawesome -->
</head>

<body>

  @yield('content')

    @stack('scripts')
    
     <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
</body>

</html>
