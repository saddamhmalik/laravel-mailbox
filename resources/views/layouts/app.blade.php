<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('mailbox.ui.title', 'Mailbox') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (\LaravelMailbox\Support\MailboxAssets::usesVite())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {!! \LaravelMailbox\Support\MailboxAssets::render() !!}
    @endif
    @livewireStyles
</head>
<body class="h-full">
    {{ $slot }}
    @livewireScripts
</body>
</html>
