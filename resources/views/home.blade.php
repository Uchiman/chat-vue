@extends('layouts.app')

@section('content')
<chatroom-component :user="{{ Auth::user() }}"></chatroom-component>
@endsection
