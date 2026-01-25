<!DOCTYPE html>
<html lang="{{ str_replace('_','-',strtolower(app()->getLocale())) }}">
@include('edaoren.layouts._header')
<body>
@include('edaoren.layouts._nav')
@yield('content')
@include('edaoren.layouts._footer')
</body>
@include('edaoren.layouts._script')
@section('js')
@show
</html>

