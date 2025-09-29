@extends('layouts.admin')

@section('title')
    Package Details
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Package Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Package Details</li>
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
                <h3 class="profile-username text-center">{{$package->name}}</h3>

                <p class="text-muted text-center">{{$package->price}}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Price</b> <a class="float-right">{{$package->price}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Max Downlines</b> <a class="float-right">{{$package->max_downlines}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Total Members</b> <a class="float-right">{{$package->total_members}}</a>
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
                  <li class="nav-item"><a class="nav-link active" href="#members" data-toggle="tab">Members</a></li>
                  <!--<li class="nav-item"><a class="nav-link" href="#rewards" data-toggle="tab">Rewards</a></li>-->
                  <li class="nav-item"><a class="nav-link" href="#colors" data-toggle="tab">Position Color</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="members">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                          <tr>
                              <td>Name</td>
                              <td>Email</td>
                              <td>Phone</td>
                              <td>Gender</td>
                          </tr>
                      </thead>
                      <tbody>
                        @forelse($package->orders as $order)
                        <tr>
                            <td>{{$order->user ? $order->user->name : 'N/A'}}</td>
                            <td>{{$order->user ? $order->user->email : 'N/A'}}</td>
                            <td>{{$order->user ? $order->user->phone : 'N/A'}}</td>
                            <td>{{$order->user ? $order->user->gender : 'N/A'}}</td>
                        </tr>
                        @empty
                        @endforelse
                      </tbody>
                    </table>
                    </div>
                  </div>
                  <!-- /.tab-pane -->
                  
                  <div class="tab-pane" id="rewards">
                      <button class="btn btn-sm btn-success right mb-3" data-toggle="modal" data-target="#addModal"> &nbsp; Add New Reward</button>
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                          <tr>
                              <td>Title</td>
                              <td>Amount</td>
                              <td>Position</td>
                              <td>Action</td>
                          </tr>
                      </thead>
                      <tbody>
                        @forelse($package->rewards as $reward)
                        <tr>
                            <td>{{$reward->title}}</td>
                            <td>{{$reward->amount}}</td>
                            <td>{{$reward->position}}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="{{$reward->id}}" data-title="{{$reward->title}}" data-amount="{{$reward->amount}}" data-position="{{$reward->position}}"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="{{$reward->id}}" data-action="delete"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                      </tbody>
                    </table>
                    </div>
                  </div>
                  
                  <div class="tab-pane" id="colors">
                      <form action="{{url('/update-color')}}" method="post">
                          @csrf
                          <div class="form-group row">
                            <?php $i = 0; ?>
                            @forelse($package->colors as $color)
                            <?php $i++ ?>
                            <label for="color" class="col-sm-2 col-form-label">Position {{$i}}</label>
                            <div class="col-sm-10 mb-3">
                              <input type="color" class="form-control" name="colors[]" id="color{{$i}}" placeholder="Color" value="{{$color->color}}">
                            </div>
                            @empty
                            @endforelse
                          </div>
                          <input type="hidden" name="package_id" value="{{$package->id}}">
                          <input type="submit" class="btn btn-lg btn-block btn-primary" value="Save Changes">
                      </form>
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
    
    
<!-- Add Modal -->

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Reward</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('reward.store')}}" method="post" class="add-form">
        @csrf
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="name" class="col-form-label">Title:</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Name" min="4" max="90" required>
          </div>
          <div class="form-group">
            <label for="code" class="col-form-label">Amount:</label>
            <input type="text" name="amount" class="form-control" id="amount" placeholder="Amount" required>
          </div>
          <div class="form-group">
            <label for="code" class="col-form-label">Position:</label>
            <input type="text" name="position" class="form-control" id="position" placeholder="Position" required>
          </div>
          <input type="hidden" name="id" id="edit-id">
          <input type="hidden" name="package_id" value="{{$package->id}}">
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
      var url = "{{route('reward.destroy','')}}";
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
      $('#title').val(button.data('title'));
      $('#amount').val(button.data('amount'));
      $('#position').val(button.data('position'));
      if(button.data('id') > 0){
          $(".modal-title").text('Update Reward');
      }else{
          $(".modal-title").text('Add New Reward');
      }
    });

  });
</script>
@endsection

@endsection
