@extends('layouts.admin')

@section('title')
    Dashboard
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard</h1>
          </div>
          <div class="col-sm-6">
          <!-- <button class="btn btn-sm btn-success right" data-toggle="modal" data-target="#deleteModal">Clear Database</button> -->
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <a href="{{url('/customers')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\User::where('role','customer')->count()}}</h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('country.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Country::count()}}</h3>
                            <p>Country</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('state.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\State::count()}}</h3>
                            <p>State</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('district.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\District::count()}}</h3>
                            <p>District</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('mandal.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Mandal::count()}}</h3>
                            <p>Mandal</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('tour.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Tour::count()}}</h3>
                            <p>Tours</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('package.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Package::count()}}</h3>
                            <p>Package</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('trip.index')}}" target="_blank">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Trip::count()}}</h3>
                            <p>Trips</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <!-- <div class="col-lg-3 col-6">
                    <a href="#">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Circle::count()}}</h3>
                            <p>Circles</p>
                        </div>
                    </div>
                    </a>
                </div> -->
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('order.index')}}">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Order::count()}}</h3>
                            <p>Orders</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('subscription.index')}}">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Subscription::count()}}</h3>
                            <p>Total Subscriptions</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <div class="col-lg-3 col-6">
                    <a href="{{route('transaction.index')}}">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Transaction::count()}}</h3>
                            <p>Total Transactions</p>
                        </div>
                    </div>
                    </a>
                </div>
                
                <!-- <div class="col-lg-3 col-6">
                    <a href="#">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{\App\Models\Photo::count()}}</h3>
                            <p>Total Photos</p>
                        </div>
                    </div>
                    </a>
                </div> -->
                
                
            </div>
        </div>
    </section>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Are you sure ?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text">You are going truncate all the databases</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" id="delete-button">Confirm Delete</button>
      </div>
    </div>
  </div>
</div>

@section('script')

<script>
    
    $(document).ready(function(){
        var id = '';
        var token = "{{csrf_token()}}";

        $(document).on('click','#delete-button',function(){
            var url = "{{url('clear-database')}}";
            $.ajax({
                url : url,
                type: "POST",
                data : {'_token':token},
                success: function(data)
                {
                    window.location.reload();
                }
            });
        });
    });
</script>
@endsection

@endsection