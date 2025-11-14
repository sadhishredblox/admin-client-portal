@extends('layouts.app')

@section('content')
<div class="container">
    <client-management 
        :initial-clients="{{ json_encode($clients) }}"
        :interests="{{ json_encode($interests) }}">
    </client-management>
</div>
@endsection