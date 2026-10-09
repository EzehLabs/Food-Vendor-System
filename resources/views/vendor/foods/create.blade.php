@extends('layouts.app')
@section('content')

    <form action="{{ url('/vendor/foods/store') }}"
      method="POST"
      enctype="multipart/form-data">

    @include('vendor.foods._form')

</form>

@endsection