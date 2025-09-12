<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>{{ app()->getLocale() === 'de' ? 'Premium WordPress Vorlagen Galerie | Entdecken & Wählen Sie Ihr Website Design' : 'Premium WordPress Templates Gallery | Explore & Choose Your Future Website Design' }}</title>
    <meta name="description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere komplette Sammlung professionell gestalteter WordPress-Vorlagen, automatisch aktualisiert von unserem Sandbox-Server. Durchsuchen, Vorschau und Auswahl des perfekten Designs für Ihre Business-Website — schnell, modern und startbereit.' : 'Discover our complete collection of professionally crafted WordPress templates, automatically updated from our sandbox server. Browse, preview, and select the perfect design for your business website — fast, modern, and ready to launch.' }}">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ app()->getLocale() === 'de' ? 'Premium WordPress Vorlagen Galerie' : 'Premium WordPress Templates Gallery' }}">
    <meta property="og:description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere komplette Sammlung professionell gestalteter WordPress-Vorlagen, automatisch aktualisiert von unserem Sandbox-Server.' : 'Discover our complete collection of professionally crafted WordPress templates, automatically updated from our sandbox server.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ app()->getLocale() === 'de' ? 'Premium WordPress Vorlagen Galerie' : 'Premium WordPress Templates Gallery' }}">
    <meta name="twitter:description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie professionell gestaltete WordPress-Vorlagen für Ihre Website.' : 'Discover professionally crafted WordPress templates for your website.' }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Language Alternates -->
    <link rel="alternate" hreflang="en" href="{{ route('templates.index') }}">
    <link rel="alternate" hreflang="de" href="{{ route('templates.index.de') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/watermark.png">
    
    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <livewire:templates-index />
    @livewireScripts
</body>
</html>
