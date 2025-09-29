@extends('layouts.admin')


@section('title')
    Trip List
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-12">
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
                @endif
            </div>
          <div class="col-sm-6">
            <h1>Trips</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Trips</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
                <!--<button class="btn btn-sm btn-success right" data-toggle="modal" data-target="#addModal">Add New Country</button>-->
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>S No</th>
                    <th>Username</th>
                    <th>Tour</th>
                    <th>Members</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Place</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                            <?php $i = 0; ?>
                            @forelse($trips as $trip)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td><a href="{{url('show-customer',$trip->user_id)}}">{{$trip->user->username}}</a></td>
                                <td>{{$trip->tour ? $trip->tour->name : 'N/A'}}</td>
                                <td>{{$trip->members}}</td>
                                <td>{{$trip->from_date}}</td>
                                <td>{{$trip->to_date}}</td>
                                <td>{{$trip->from_place}}</td>
                                <td>{{$trip->status}}</td>
                                <td>
                                    <a href="{{route('trip.show',$trip->id)}}" target="_blank"  class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="{{$trip->id}}" data-tour="{{$trip->tour_id}}" data-members="{{$trip->members}}"
                                    data-from_date="{{$trip->from_date}}" data-to_date="{{$trip->to_date}}" data-from_place="{{$trip->from_place}}" data-status="{{$trip->status}}"><i class="fa fa-edit"></i></button>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                </table>
                </div>
                
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<!-- Add Modal -->

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Update Trip</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('trip.store')}}" method="post" class="add-form">
        @csrf
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="members" class="col-form-label">Select Tour:</label>
            <select name="tour" id="tour" class="form-control" required>
                @forelse($tours as $tour)
                <option value="{{$tour->id}}">{{$tour->name}} - {{$tour->place}} at Rs {{$tour->price}}</option>
                @empty
                @endforelse
            </select>
          </div>
          <div class="form-group">
            <label for="members" class="col-form-label">Total Members:</label>
            <input type="number" name="members" id="members" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="from_date" class="col-form-label">From Date:</label>
            <input type="date" name="from_date" class="form-control" id="from_date" placeholder="From Date" required>
          </div>
          <div class="form-group">
            <label for="to_date" class="col-form-label">To Date:</label>
            <input type="date" name="to_date" class="form-control" id="to_date" placeholder="To Date" required>
          </div>
          <div class="form-group">
            <label for="to_date" class="col-form-label">From Place:</label>
            <input type="text" name="from_place" class="form-control" id="from_place" placeholder="From Place" required>
          </div>
          <div class="form-group">
            <label for="to_date" class="col-form-label">Status:</label>
            <select name="status" class="form-control" id="status" placeholder="Status">
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
            </select>
          </div>
          
          <input type="hidden" name="id" id="edit-id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="save-button">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>


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
        <p class="text"></p>
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
    var action = '';
    var token = "{{csrf_token()}}";
    
    $('#deleteModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      id = button.data('id');
      $('#delete-id').val(id);
      action= button.data('action');
      $('#delete-button').removeClass('btn-success');
      $('#delete-button').removeClass('btn-danger');
      if(action == 'delete'){
          $('#delete-button').addClass('btn-danger');
          $('#delete-button').text('Confirm Delete');
          $('.text').text('You are going to permanently delete this item..');
      }else{
          $('#delete-button').addClass('btn-success');
          $('#delete-button').text('Confirm Restore');
          $('.text').text('You are going to restore this item..');
      }
    });

    $(document).on('click','#delete-button',function(){
      var url = "{{route('country.destroy','')}}";
      $.ajax({
        url : url + '/' + id,
        type: "DELETE",
        data : {'_token':token,'action':action},
        success: function(data)
        {
          window.location.reload();
        }
      });
    });

    $('#addModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget);
          $('#edit-id').val(button.data('id'));
          $('#tour').val(button.data('tour'));
          $('#members').val(button.data('members'));
          $('#from_date').val(button.data('from_date'));
          $('#to_date').val(button.data('to_date'));
          $('#from_place').val(button.data('from_place'));
          $('#status').val(button.data('status'));
          if(button.data('id') > 0){
              $(".modal-title").text('Update Trip');
          }else{
              $(".modal-title").text('Add New Trip');
          }
        });

  });
</script>
@endsection

@endsection


