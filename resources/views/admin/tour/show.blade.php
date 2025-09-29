@extends('layouts.admin')

@section('title')
    Tour Details
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Tour Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Tour Details</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{$tour->photo}}"
                       alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{$tour->name}}</h3>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Place</b> <a class="float-right">{{$tour->place}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Area</b> <a class="float-right">{{$tour->area}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Price</b> <a class="float-right">{{$tour->price}}</a>
                  </li>
                  <!-- <li class="list-group-item">
                    <b>Desc</b> <a class="float-right">{{$tour->desc}}</a>
                  </li> -->
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link active" href="#desc" data-toggle="tab">Desc</a></li>
                  <li class="nav-item"><a class="nav-link" href="#photos" data-toggle="tab">Photos</a></li>
                  <li class="nav-item"><a class="nav-link" href="#trips" data-toggle="tab">Trips</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                <div class="active tab-pane" id="photos">
                  <div class="row">
                    <div class="col-md-12">
                    {!! $tour->desc !!}
                    </div>
                  </div>
                </div>

                  <div class="active tab-pane" id="photos">
                    <div class="row">
                        @forelse($tour->photos as $photo)
                            <div class="col-md-4">
                                <img src="{{$photo->photo}}" class="img-thumbnail" alt="N/A">
                            </div>
                        @empty
                        
                        @endforelse
                    </div>
                  </div>
                  <!-- /.tab-pane -->
                  
                
                <div class="tab-pane" id="trips">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>User</th>
                           <th>From Date</th>
                           <th>To Date</th>
                           <th>Members</th>
                           <th>Payment Tye</th>
                           <th>Amount</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($tour->trips as $trip)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td><a href="{{url('show-customer',$trip->user_id)}}" target="_blank">{{$trip->user->name}}</a></td>
                                <td>{{$trip->from_date}}</td>
                                <td>{{$trip->to_date}}</td>
                                <td>{{$trip->members}}</td>
                                <td>{{$trip->payment_type}}</td>
                                <td>{{$trip->amount}}</td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  </div>
                  
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

@section('script')
<script>
    $(document).ready(function(){
        var token = "{{csrf_token()}}";

    });
</script>
@endsection

@endsection
