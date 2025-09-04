@extends('layout')
@section('title', 'HomePage')
@section('content')
    @auth
        {{ auth()->user()->name }}
    @endauth
@endsection