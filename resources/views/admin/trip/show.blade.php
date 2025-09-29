@extends('layouts.admin')

@section('title')
    Trip Details
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Trip Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Trip Details</li>
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
                </div>
                <h3 class="profile-username text-center">{{$trip->name}}</h3>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Tour</b> <a class="float-right">{{$trip->tour ? $trip->tour->name : 'N/A'}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>User</b> <a class="float-right">{{$trip->user->name}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Members</b> <a class="float-right">{{$trip->members}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>From Date</b> <a class="float-right">{{$trip->from_date}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>To Date</b> <a class="float-right">{{$trip->to_date}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>From Place</b> <a class="float-right">{{$trip->from_place}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>To Place</b> <a class="float-right">{{$trip->tour ? $trip->tour->plcae : 'N/A'}}</a>
                  </li>
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
                  <li class="nav-item"><a class="nav-link active" href="#photos" data-toggle="tab">Photos</a></li>
                  <li class="nav-item"><a class="nav-link" href="#rewards" data-toggle="tab">Rewards</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="photos">
                    <div class="row">
                        @forelse($trip->photos as $photo)
                            <div class="col-md-4">
                                <img src="{{$photo->photo}}" class="img-thumbnail" alt="N/A">
                            </div>
                        @empty
                        
                        @endforelse
                    </div>
                  </div>
                  <!-- /.tab-pane -->
                  
                
                <div class="tab-pane" id="rewards">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>Circle Code</th>
                           <th>Position</th>
                           <th>Amount</th>
                           <th>Status</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($trip->rewards as $reward)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>#{{$reward->circle->name}}</td>
                                <td>{{$reward->position}}</td>
                                <td>{{$reward->amount}}</td>
                                <td>{{$reward->status}}</td>
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
