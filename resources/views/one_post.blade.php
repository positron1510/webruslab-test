@extends('layout')

@section('title')
    Пост {{$post->id}}
@endsection

@section('content')
   @if($post)
       <p> Пост {{$post->id}} &nbsp;&nbsp; <b>{{$post->views}}</b> &nbsp;&nbsp; просмотров</p>
   @else
       Поста с таким идентификатором нет
   @endif
@endsection