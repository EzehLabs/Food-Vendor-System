
@extends('layouts.app')
@section('content')

    <form action="{{ route('vendor.foods.update', $food->id) }}"
        method="POST"
        enctype="multipart/form-data">

        @include('vendor.foods._form')

    </form>

@endsection