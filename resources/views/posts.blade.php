@extends('layout')

@section('title')
    Все посты
@endsection

@section('content')
    <p><a href="{{route('add_post')}}">Добавить пост</a></p>
    
    @if($posts)
        <h2>Список постов</h2>
        <table style="width: 20%;">
            <tr style="font-weight: bold;">
                <td>Пост</td>
                <td>Кол-во просмотров</td>
            </tr>

            @foreach($posts as $post)
                <tr>
                    <td><a href="{{route('one_post', ['post_id' => $post->id])}}" target="_blank">Пост {{$post->id}}</a></td>
                    <td>{{$post->views}}</td>
                </tr>
            @endforeach
        </table>
    @endif
@endsection