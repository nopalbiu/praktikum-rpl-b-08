@extends('layouts.base')

@section('body')
    <x-splash />
    <x-navbar />

    <div id="konten" class="flex flex-1 flex-col">
        @yield('content')
    </div>

    <x-footer />
@endsection
