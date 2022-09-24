<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>

    {{-- ICON --}}
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/icon.png') }}"/>

    <!-- Font Awesome UI KIT-->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://kit.fontawesome.com/f75ab26951.js" crossorigin="anonymous"></script>
    <script src="{{asset('jqueryui-1.13/jquery-ui.min.js')}}" type="text/javascript" defer></script>
    <script src="{{asset('sweetalert2/sweetalert2.min.js')}}" defer></script>
    <script src="{{asset('DataTables/datatables.min.js')}}" defer></script>

    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{asset('css/app.css')}}" rel="stylesheet">
    <link href="{{asset('admin/css/sb-admin-2.min.css')}}" rel="stylesheet">
    <link href="{{asset('jqueryui-1.13/jquery-ui.css')}}" rel="stylesheet">
    <link href="{{asset('sweetalert2/sweetalert2.css') }}" rel="stylesheet" type="text/css">
    <link href="{{asset('DataTables/datatables.min.css')}}" rel="stylesheet" type="text/css">

</head>