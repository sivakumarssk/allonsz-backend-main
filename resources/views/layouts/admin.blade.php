<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $setting = \App\Models\Setting::first(); ?>
  <title>@yield('title') | {{$setting->bussiness_name}}</title>
  <link rel="shortcut icon" href="{{$setting->favicon}}">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('admin/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Select2 -->
  <!--<link rel="stylesheet" href="{{asset('admin/plugins/select2/css/select2.min.css')}}">-->
  <!--<link rel="stylesheet" href="{{asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">-->

  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('admin/dist/css/adminlte.min.css')}}">
 
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    tfoot{display:none;}
    .btn-primary{background-color:black;}
    .btn-primary:hover{background-color:white;color:black;}
    .page-item.active .page-link {
      z-index: 3;
      color: #fff;
      background-color: black;
      border-color: #007bff;
    }
    .page-item .page-link {
      color: black;
    }
    .active-tab{
        background-color: black;
        color: white;
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
      color: #fff;
      background-color: black;
      border-radius:0px;
    }
    .nav-pills .nav-link:hover, .nav-pills .show > .nav-link:hover {
      color: #fff !important;
    }
    .custom-map-control-button{padding:14px;}
    .breadcrumb{display:none;}
    .right{float: right !important}
    .sidebar-dark-primary{background-color:white;}
    [class*="sidebar-dark-"] .sidebar a {
        color: black;
    }
    .nav-item:hover{background-color:black;color:white;border-bottom:2px solid white;}
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active, .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active {
        background-color: black;
        color: white !important;
        border-radius:0px;
    }
    [class*="sidebar-dark-"] .nav-sidebar > .nav-item.menu-open > .nav-link, [class*="sidebar-dark-"] .nav-sidebar > .nav-item:hover > .nav-link, [class*="sidebar-dark-"] .nav-sidebar > .nav-item > .nav-link:focus {
        background-color: black;
        color: #fff;
    }
    .sidebar{padding-top:2px;padding-bottom:150px;}
    .card-header {
        background-color: #ebf6f8;
    }
    .brand-image{border-bottom:1px solid black;}
    .buttons-html5{padding:5px 8px !important;}
    .btn{margin-bottom:3px;}
    
    .bg-info{background-color:black !important; color:white !important;}
    .bg-info:hover{background-color:white !important; color:black !important;}
    .btn-success{background-color:black;color:white;}
    .btn-success:hover{background-color:white;color:black;border-color:black;}
    .humburger-menu:hover{color:white !important;}
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link humburger-menu" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!--<li class="nav-item d-none d-sm-inline-block">-->
      <!--  <a href="#" class="nav-link">Home</a>-->
      <!--</li>-->
      <!--<li class="nav-item d-none d-sm-inline-block">-->
      <!--  <a href="#" class="nav-link">Contact</a>-->
      <!--</li>-->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">


      <!-- Notifications Dropdown Menu -->
      <!--<li class="nav-item dropdown">-->
      <!--  <a class="nav-link" data-toggle="dropdown" href="#">-->
      <!--    <i class="far fa-bell"></i>-->
      <!--    <?php $notifications = \App\Models\Notification::where('admin_read',0)->orderBy('id','desc')->get(); ?>-->
      <!--    <span class="badge badge-warning navbar-badge">{{$notifications->count()}}</span>-->
      <!--  </a>-->
      <!--  <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">-->
      <!--    <span class="dropdown-item dropdown-header">{{$notifications->count()}} Notifications</span>-->
      <!--    <div class="dropdown-divider"></div>-->
      <!--    @forelse($notifications as $notification)-->
      <!--    <a href="{{route('notification.show',$notification->id)}}" class="dropdown-item">-->
      <!--      <i class="fas fa-envelope mr-2"></i> {{$notification->title}}-->
      <!--      <span class="float-right text-muted text-sm">{{$notification->created_at->diffForHumans()}}</span>-->
      <!--    </a>-->
      <!--    <div class="dropdown-divider"></div>-->
      <!--    @empty-->
      <!--    @endforelse-->
      <!--    <a href="{{route('notification.index')}}" class="dropdown-item dropdown-footer">See All Notifications</a>-->
      <!--  </div>-->
      <!--</li>-->
      <!--<li class="nav-item">-->
      <!--  <a class="nav-link humburger-menu" data-widget="fullscreen" href="#" role="button">-->
      <!--    <i class="fas fa-expand-arrows-alt"></i>-->
      <!--  </a>-->
      <!--</li>-->
      <!--<li class="nav-item">-->
      <!--  <a class="nav-link humburger-menu" data-widget="control-sidebar" data-slide="true" href="#" role="button">-->
      <!--    <i class="fas fa-th-large"></i>-->
      <!--  </a>-->
      <!--</li>-->
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    
    <a href="{{url('/')}}">
        <center>
            <img src="{{$setting->logo}}" alt="AdminLTE Logo" class="brand-image" style="width:200px;height:200px;">
        </center>
    </a>
    <?php $user = Auth::User(); ?>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <!--<div class="user-panel mt-3 pb-3 mb-3 d-flex">-->
      <!--  <div class="image">-->
      <!--    <img src="{{asset('images/profiles/'.$user->photo)}}" class="img-circle elevation-2" alt="User Image">-->
      <!--  </div>-->
      <!--  <div class="info">-->
      <!--    <a href="{{url('profile')}}" class="d-block">{{$user->name}}</a>-->
      <!--  </div>-->
      <!--</div>-->

      <!-- SidebarSearch Form -->
      <!--<div class="form-inline">-->
      <!--  <div class="input-group" data-widget="sidebar-search">-->
      <!--    <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">-->
      <!--    <div class="input-group-append">-->
      <!--      <button class="btn btn-sidebar">-->
      <!--        <i class="fas fa-search fa-fw"></i>-->
      <!--      </button>-->
      <!--    </div>-->
      <!--  </div>-->
      <!--</div>-->

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <!-- <li class="nav-item">
          <!--  <a href="#" class="nav-link">-->
          <!--    <i class="nav-icon fas fa-tachometer-alt"></i>-->
          <!--    <p>-->
          <!--      Dashboard-->
          <!--      <i class="right fas fa-angle-left"></i>-->
          <!--    </p>-->
          <!--  </a>-->
          <!--  <ul class="nav nav-treeview">-->
          <!--    <li class="nav-item">-->
          <!--      <a href="{{url('admin/index.html')}}" class="nav-link">-->
          <!--        <i class="far fa-circle nav-icon"></i>-->
          <!--        <p>Dashboard v1</p>-->
          <!--      </a>-->
          <!--    </li>-->
          <!--  </ul>-->
          <!--</li> -->
          
          <li class="nav-item">
            <a href="{{url('/')}}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
              <i class="nav-icon fa fa-bar-chart"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('/profile')}}" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Profile</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('customers')}}" class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Customers</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('expiring-timers')}}" class="nav-link {{ request()->is('expiring-timers*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Expiring Timers</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('customer-timers')}}" class="nav-link {{ request()->is('customer-timers*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Timers</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('withdraws')}}" class="nav-link {{ request()->is('withdraws*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Withdraw Request</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('trip.index')}}" class="nav-link {{ request()->is('trip*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Trips</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('country.index')}}" class="nav-link {{ request()->is('country*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Country</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('state.index')}}" class="nav-link {{ request()->is('state*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>State</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('district.index')}}" class="nav-link {{ request()->is('district*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>District</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('mandal.index')}}" class="nav-link {{ request()->is('mandal*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Mandal</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('tour.index')}}" class="nav-link {{ request()->is('tour*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Tour</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('package.index')}}" class="nav-link {{ request()->is('package*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-user"></i>
              <p>Package</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('add')}}" class="nav-link {{ request()->is('add*') ? 'active' : '' }}">
              <i class="nav-icon fas far fa-bell"></i>
              <p>Add</p>
            </a>
          </li>
          <!--<li class="nav-item">-->
          <!--  <a href="{{route('notification.index')}}" class="nav-link {{ request()->is('notification*') ? 'active' : '' }}">-->
          <!--    <i class="nav-icon fas far fa-bell"></i>-->
          <!--    <p>Notifications <i class="badge badge-danger right">{{$notifications->count()}}</i></p>-->
          <!--  </a>-->
          <!--</li>-->
          <li class="nav-item">
            <a href="{{route('order.index')}}" class="nav-link {{ request()->is('order*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-wrench"></i>
              <p>Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('subscription.index')}}" class="nav-link {{ request()->is('subscription*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-wrench"></i>
              <p>Subscriptions</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('transaction.index')}}" class="nav-link {{ request()->is('transaction*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-wrench"></i>
              <p>Transactions</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('setting.index')}}" class="nav-link {{ request()->is('setting*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-wrench"></i>
              <p>Settings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('taxes')}}" class="nav-link {{ request()->is('taxes*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-leaf"></i>
              <p>Taxes</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('privacy-policy')}}" class="nav-link {{ request()->is('privacy-policy*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-anchor"></i>
              <p>Privacy Policy</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('terms-conditions')}}" class="nav-link {{ request()->is('terms-conditions*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-splotch"></i>
              <p>Terms & Conditions</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('about-us')}}" class="nav-link {{ request()->is('about-us*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-solid fa-eye-dropper"></i>
              <p>About us</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('how-it-works')}}" class="nav-link {{ request()->is('how-it-works*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-swatchbook"></i>
              <p>How it works</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('return-and-refund-policy')}}" class="nav-link {{ request()->is('return-and-refund-policy*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-duotone fa-ruler-combined"></i>
              <p>Return and Refund Policy</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('cancellation-policy')}}" class="nav-link {{ request()->is('cancellation-policy') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sharp fa-solid fa-fill"></i>
              <p>Cancellation Policy</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('faqs')}}" class="nav-link {{ request()->is('faqs*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sharp fa-solid fa-fill-drip"></i>
              <p>FAQ's</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{url('logout')}}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>

            <form id="logout-form" action="{{url('logout')}}" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
            </form>

          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    @yield('content')
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{asset('admin/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- DataTables  & Plugins -->
<script src="{{asset('admin/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('admin/plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('admin/plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('admin/plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
<script src="{{asset('admin/plugins/inputmask/jquery.inputmask.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('admin/dist/js/adminlte.min.js')}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset('admin/dist/js/demo.js')}}"></script>

<!-- Select2 -->
<!--<script src="{{asset('admin/plugins/select2/js/select2.full.min.js')}}"></script>-->

<!-- Bootstrap Switch -->
<script src="{{asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>

<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase.js"></script>

<script>
    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
</script>
<script type="module">

  var firebaseConfig = {
        apiKey: "AIzaSyCMIXqJyXbmxmFMdywPuFbYd6cRUx-l6nc",
        authDomain: "school-979f6.firebaseapp.com",
        databaseURL: "https://school-979f6.firebaseio.com",
        projectId: "school-979f6",
        storageBucket: "school-979f6.appspot.com",
        messagingSenderId: "308636612449",
        appId: "1:308636612449:web:603eb003f33921ad9db720",
        measurementId: "G-45CQ9YMRNN"
    };
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();
    
    function startFCM() {
        messaging
            .requestPermission()
            .then(function () {
                return messaging.getToken()
            })
            .then(function (response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '{{ route("save.token") }}',
                    type: 'POST',
                    data: {
                        device_token: response,
                        _token:"{{csrf_token()}}"
                    },
                    dataType: 'JSON',
                    success: function (response) {
                        alert('Token stored.');
                    },
                    error: function (error) {
                        alert(error);
                    },
                });
            }).catch(function (error) {
                alert(error);
            });
    }
    messaging.onMessage(function (payload) {
        const title = payload.notification.title;
        const options = {
            body: payload.notification.body,
            icon: payload.notification.icon,
        };
        new Notification(title, options);
    });
    
</script>

<script>
$(document).ready(function(){
    
    $(function () {
        $(".table").DataTable({
          "responsive": true, "ordering": true, "lengthChange": false, "autoWidth": false,"bPaginate": true,"bInfo": false,"searching": true,"pageLength": parseInt("{{$setting->pagination}}"),
          order: [[0, 'asc']],
          buttons: [
            {
                extend: 'csvHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            'colvis'
        ]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": true,
          "info": true,
          "autoWidth": false,
          "responsive": true,
          "bPaginate": false,
          "bInfo": false,
        });
    });
});
</script>

@yield('script')
</body>
</html>


