<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'fa' ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- load logo -->
  <link rel="icon" href="logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">


  <title>Ayhoot</title>
  <meta name="Ayhoot" content="Play, Learn and Grow." />
  @vite('resources/css/app.css')

</head>

<body>
  <div id="app"></div>
  <script>
    window.__APP_CONFIG__ = {
      VITE_API_BASE_URL: "{{ config('app.url', env('VITE_API_BASE_URL', '')) }}",
      PUSHER_APP_KEY: "{{ config('broadcasting.connections.pusher.key', env('PUSHER_APP_KEY', '5c8a81ec227bf87005fe')) }}",
      PUSHER_APP_CLUSTER: "{{ config('broadcasting.connections.pusher.options.cluster', env('PUSHER_APP_CLUSTER', 'mt1')) }}",
      @if(env('PUSHER_CLIENT_HOST') || env('VITE_PUSHER_HOST'))
      PUSHER_CLIENT_HOST: "{{ env('PUSHER_CLIENT_HOST', env('VITE_PUSHER_HOST')) }}",
      @endif
    };
  </script>
  @vite('resources/js/app.js')
</body>

</html>