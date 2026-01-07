@extends('layouts.admin')


@section('title')
    Withdraw List
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
            <h1>Withdraws</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Withdraws</li>
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
                    <th>Code</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Transfer Details</th>
                    <th>Status</th>
                    <th>Rejection Reason</th>
                    <th>Requested at</th>
                    <th>Updated at</th>
                    <!-- <th>Action</th> -->
                  </tr>
                  </thead>
                  <tbody>
                    
                    <?php $i = 0; ?>
                  @forelse($withdraws as $withdraw)
                  <?php $i++; ?>
                  <tr>
                    <td>{{$i}}</td>
                    <td>{{$withdraw->request_code}}</td>
                    <td><a href="{{url('/show-customer',$withdraw->user_id)}}" target="_blank">{{$withdraw->user->name}}</a></td>
                    <td>{{$withdraw->amount}}</td>
                    <td>{{$withdraw->transfer_details}}</td>
                    <td>
                      @if(strtolower($withdraw->status) == 'rejected')
                        <span class="badge badge-danger">{{$withdraw->status}}</span>
                      @elseif(strtolower($withdraw->status) == 'approved')
                        <span class="badge badge-success">{{$withdraw->status}}</span>
                      @else
                        <span class="badge badge-warning">{{$withdraw->status}}</span>
                      @endif
                    </td>
                    <td>
                      @if($withdraw->rejection_reason)
                        <small>{{$withdraw->rejection_reason}}</small>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>{{$withdraw->created_at}}</td>
                    <td>{{$withdraw->updated_at}}</td>
                    <td>
                      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="{{$withdraw->id}}" data-name="{{$withdraw->user->name}}" data-user_id="{{$withdraw->user_id}}"
                      data-amount="{{$withdraw->amount}}" data-transfer_details="{{$withdraw->transfer_details}}" data-status="{{$withdraw->status}}" data-rejection_reason="{{$withdraw->rejection_reason}}"><i class="fa fa-edit"></i></button>
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
        <h5 class="modal-title" id="exampleModalLabel">Update Withdraw Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{url('/update-withdraw')}}" method="post" class="add-form">
        @csrf
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="name" class="col-form-label">Transfer To:</label>
            <input type="text" name="name" id="name" class="form-control" min="3" max="30" placeholder="Name" required disabled>
          </div>
          <div class="form-group">
            <label for="name" class="col-form-label">Amount:</label>
            <input type="text" name="amount" id="amount" class="form-control" placeholder="Amount" required disabled>
          </div>
          <div class="form-group">
            <label for="code" class="col-form-label">Transfer Details:</label>
            <textarea name="transfer_details" class="form-control" id="transfer_details" placeholder="Transfer details" required></textarea>
          </div>
          <div class="form-group">
            <label for="code" class="col-form-label">Status:</label>
            <select name="status" class="form-control" id="status" required disabled>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <small id="status-help" class="form-text text-muted" style="display: none; color: red !important;">Status cannot be changed once approved or rejected.</small>
          </div>
          <div class="form-group" id="rejection-reason-group" style="display: none;">
            <label for="rejection_reason" class="col-form-label">Rejection Reason/Remarks: <span class="text-danger">*</span></label>
            <textarea name="rejection_reason" class="form-control" id="rejection_reason" placeholder="Please provide a reason for rejection (minimum 5 characters)" rows="3"></textarea>
            <small class="form-text text-muted">This field is required when rejecting a withdrawal request.</small>
          </div>
          <input type="hidden" name="id" id="edit-id">
          <input type="hidden" name="user_id" id="user-id">
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
      var currentStatus = button.data('status');
      var statusLower = currentStatus ? currentStatus.toLowerCase() : '';
      
      $('#edit-id').val(button.data('id'));
      $('#name').val(button.data('name'));
      $('#user-id').val(button.data('user_id'));
      $('#amount').val(button.data('amount'));
      $('#transfer_details').val(button.data('transfer_details'));
      $('#status').val(currentStatus);
      $('#rejection_reason').val(button.data('rejection_reason') || '');
      
      // Disable status dropdown if already approved or rejected
      if(statusLower == 'approved' || statusLower == 'rejected') {
        $('#status').prop('disabled', true);
        $('#status-help').show();
        $('#save-button').prop('disabled', true).text('Cannot Edit Status');
      } else {
        $('#status').prop('disabled', false);
        $('#status-help').hide();
        $('#save-button').prop('disabled', false).text('Save');
      }
      
      // Show/hide rejection reason field based on status
      toggleRejectionReason();
    });
    
    // Toggle rejection reason field based on status selection
    $('#status').on('change', function() {
      toggleRejectionReason();
    });
    
    function toggleRejectionReason() {
      var status = $('#status').val();
      if(status == 'Rejected') {
        $('#rejection-reason-group').show();
        $('#rejection_reason').prop('required', true);
      } else {
        $('#rejection-reason-group').hide();
        $('#rejection_reason').prop('required', false);
        $('#rejection_reason').val('');
      }
    }
    
    // Form validation before submit
    $('.add-form').on('submit', function(e) {
      // Prevent submission if status is disabled (already approved/rejected)
      if($('#status').prop('disabled')) {
        e.preventDefault();
        alert('Cannot update withdrawal request that is already approved or rejected');
        return false;
      }
      
      var status = $('#status').val();
      var rejectionReason = $('#rejection_reason').val().trim();
      
      if(status == 'Rejected' && rejectionReason.length < 5) {
        e.preventDefault();
        alert('Please provide a rejection reason (minimum 5 characters)');
        $('#rejection_reason').focus();
        return false;
      }
      
      return true;
    });
    
    $('.status').bootstrapSwitch('state');
        $('.status').on('switchChange.bootstrapSwitch',function () {
            var id = $(this).data('id');
            $.ajax({
                url : "{{url('update-user-status')}}",
                type: "post",
                data : {'_token':token,'id':id,},
                success: function(data)
                {
                  //
                }
            });
        });

  });
</script>
@endsection

@endsection


