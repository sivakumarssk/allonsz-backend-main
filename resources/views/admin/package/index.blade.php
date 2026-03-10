@extends('layouts.admin')


@section('title')
    Package List
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
            <h1>Packages</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Packages</li>
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
                <button class="btn btn-sm btn-success right" data-toggle="modal" data-target="#addModal">Add New Package</button>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>S No</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Circle Type</th>
                    <th>Total Members</th>
                    <th>Reward Amount</th>
                    <!-- <th>Action</th> -->
                  </tr>
                  </thead>
                  <tbody>
                    
                    <?php $i = 0; ?>
                  @forelse($packages as $package)
                  <?php $i++; ?>
                  <tr>
                    <td>{{$i}}</td>
                    <td>{{$package->name}}</td>
                    <td>{{$package->price}}</td>
                    <td>
                        @if($package->is_combo)
                            <span class="badge badge-primary">Combo (3x5 + 21)</span>
                        @elseif($package->total_members == 5)
                            <span class="badge badge-info">5-Member Simple Circle</span>
                        @else
                            {{$package->max_downlines}} Downlines
                        @endif
                    </td>
                    <td>{{$package->total_members}}</td>
                    <td>{{$package->reward_amount}}</td>
                    <td>
                      <a href="{{route('package.show',$package->id)}}" target="_blank"  class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="{{$package->id}}" data-name="{{$package->name}}" data-price="{{$package->price}}" 
                      data-max_downlines="{{$package->max_downlines ?? ''}}" data-total_members="{{$package->total_members}}" data-reward_amount="{{$package->reward_amount}}"
                      data-is_combo="{{$package->is_combo ? 1 : 0}}"
                      data-combo_five_a_reward_direct="{{$package->combo_five_a_reward_direct ?? ''}}"
                      data-combo_five_a_reward_autofill="{{$package->combo_five_a_reward_autofill ?? ''}}"
                      data-combo_five_a_autorenew_amount="{{$package->combo_five_a_autorenew_amount ?? ''}}"
                      data-combo_five_b_reward_direct="{{$package->combo_five_b_reward_direct ?? ''}}"
                      data-combo_five_b_reward_autofill="{{$package->combo_five_b_reward_autofill ?? ''}}"
                      data-combo_five_b_autorenew_amount="{{$package->combo_five_b_autorenew_amount ?? ''}}"
                      data-combo_five_c_reward_direct="{{$package->combo_five_c_reward_direct ?? ''}}"
                      data-combo_five_c_reward_autofill="{{$package->combo_five_c_reward_autofill ?? ''}}"
                      data-combo_five_c_autorenew_amount="{{$package->combo_five_c_autorenew_amount ?? ''}}"
                      data-combo_twentyone_reward_amount="{{$package->combo_twentyone_reward_amount}}"
                      data-combo_twentyone_autorenew_amount="{{$package->combo_twentyone_autorenew_amount}}"
                      data-combo_five_a_name="{{$package->combo_five_a_name ?? ''}}"
                      data-combo_five_b_name="{{$package->combo_five_b_name ?? ''}}"
                      data-combo_five_c_name="{{$package->combo_five_c_name ?? ''}}"
                      data-combo_twentyone_name="{{$package->combo_twentyone_name ?? ''}}"><i class="fa fa-edit"></i></button>
                      <!--@if($package->deleted_at)-->
                      <!--<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#deleteModal" data-id="{{$package->id}}" data-action="restore"><i class="fa fa-undo"></i></button>-->
                      <!--@else-->
                      <!--<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="{{$package->id}}" data-action="delete"><i class="fa fa-trash"></i></button>-->
                      <!--@endif-->
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
        <h5 class="modal-title" id="exampleModalLabel">Add Package</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('package.store')}}" method="post" class="add-form">
        @csrf
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="name" class="col-form-label">Name:</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Name" minlength="2" maxlength="20" required>
          </div>
          <div class="form-group">
            <label for="price" class="col-form-label">Price:</label>
            <input type="text" name="price" class="form-control" id="price" placeholder="Price" minlength="1" maxlength="10"  
            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
          </div>
          <div class="form-group">
            <label for="max_downlines" class="col-form-label">Circle Type:</label>
            <select name="max_downlines" class="form-control" id="max_downlines" placeholder="Circle Type" required>
                <option value="2">2 Downlines (7 Members)</option>
                <option value="3">3 Downlines (13 Members)</option>
                <option value="4">4 Downlines (21 Members)</option>
                <option value="5">5-Member Simple Circle</option>
                <option value="combo">Combo Package (3x5 + 21)</option>
            </select>
            <input type="hidden" name="max_downlines_2" class="form-control" id="max_downlines_2" placeholder="Max downlines">
          </div>
          <div class="form-group">
            <label for="total_members" class="col-form-label">Total Members:</label>
            <input type="hidden" name="total_members" class="form-control" id="total_members" placeholder="Total Members" required>
            <input type="text" name="total_members_2" class="form-control" id="total_members_2" placeholder="Total Members" required disabled>
          </div>
          <div class="form-group reward-amount-field">
            <label for="reward" class="col-form-label">Reward Amount:</label>
            <input type="text" name="reward_amount" class="form-control" id="reward_amount" placeholder="Reward Amount" minlength="1" maxlength="10"  
            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
          </div>
          <input type="hidden" name="is_combo" id="is_combo" value="0">
          <div class="combo-fields" style="display:none;">
            <hr>
            <h6>Combo Package Settings</h6>
            <div class="form-group">
              <label for="combo_five_a_name" class="col-form-label">5-Member Circle 1 Name:</label>
              <input type="text" name="combo_five_a_name" class="form-control" id="combo_five_a_name" placeholder="Enter name for 5-Member Circle 1" maxlength="100">
            </div>
            <div class="form-group">
              <label for="combo_five_b_name" class="col-form-label">5-Member Circle 2 Name:</label>
              <input type="text" name="combo_five_b_name" class="form-control" id="combo_five_b_name" placeholder="Enter name for 5-Member Circle 2" maxlength="100">
            </div>
            <div class="form-group">
              <label for="combo_five_c_name" class="col-form-label">5-Member Circle 3 Name:</label>
              <input type="text" name="combo_five_c_name" class="form-control" id="combo_five_c_name" placeholder="Enter name for 5-Member Circle 3" maxlength="100">
            </div>
            <div class="form-group">
              <label for="combo_twentyone_name" class="col-form-label">21-Member Circle Name:</label>
              <input type="text" name="combo_twentyone_name" class="form-control" id="combo_twentyone_name" placeholder="Enter name for 21-Member Circle" maxlength="100">
            </div>
            <hr>
            <h6>5-Member Circle 1 (five_a) Rewards</h6>
            <div class="form-group">
              <label for="combo_five_a_reward_direct" class="col-form-label">5-Member Circle 1 Direct Reward:</label>
              <input type="text" name="combo_five_a_reward_direct" class="form-control" id="combo_five_a_reward_direct" placeholder="Direct reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_a_reward_autofill" class="col-form-label">5-Member Circle 1 Autofill Reward:</label>
              <input type="text" name="combo_five_a_reward_autofill" class="form-control" id="combo_five_a_reward_autofill" placeholder="Autofill reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_a_autorenew_amount" class="col-form-label">5-Member Circle 1 Auto-Renew Amount:</label>
              <input type="text" name="combo_five_a_autorenew_amount" class="form-control" id="combo_five_a_autorenew_amount" placeholder="Auto-renew amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <hr>
            <h6>5-Member Circle 2 (five_b) Rewards</h6>
            <div class="form-group">
              <label for="combo_five_b_reward_direct" class="col-form-label">5-Member Circle 2 Direct Reward:</label>
              <input type="text" name="combo_five_b_reward_direct" class="form-control" id="combo_five_b_reward_direct" placeholder="Direct reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_b_reward_autofill" class="col-form-label">5-Member Circle 2 Autofill Reward:</label>
              <input type="text" name="combo_five_b_reward_autofill" class="form-control" id="combo_five_b_reward_autofill" placeholder="Autofill reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_b_autorenew_amount" class="col-form-label">5-Member Circle 2 Auto-Renew Amount:</label>
              <input type="text" name="combo_five_b_autorenew_amount" class="form-control" id="combo_five_b_autorenew_amount" placeholder="Auto-renew amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <hr>
            <h6>5-Member Circle 3 (five_c) Rewards</h6>
            <div class="form-group">
              <label for="combo_five_c_reward_direct" class="col-form-label">5-Member Circle 3 Direct Reward:</label>
              <input type="text" name="combo_five_c_reward_direct" class="form-control" id="combo_five_c_reward_direct" placeholder="Direct reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_c_reward_autofill" class="col-form-label">5-Member Circle 3 Autofill Reward:</label>
              <input type="text" name="combo_five_c_reward_autofill" class="form-control" id="combo_five_c_reward_autofill" placeholder="Autofill reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_five_c_autorenew_amount" class="col-form-label">5-Member Circle 3 Auto-Renew Amount:</label>
              <input type="text" name="combo_five_c_autorenew_amount" class="form-control" id="combo_five_c_autorenew_amount" placeholder="Auto-renew amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_twentyone_reward_amount" class="col-form-label">21-Member Reward Amount:</label>
              <input type="text" name="combo_twentyone_reward_amount" class="form-control" id="combo_twentyone_reward_amount" placeholder="21-member reward amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <div class="form-group">
              <label for="combo_twentyone_autorenew_amount" class="col-form-label">21-Member Auto-Renew Amount:</label>
              <input type="text" name="combo_twentyone_autorenew_amount" class="form-control" id="combo_twentyone_autorenew_amount" placeholder="21-member auto-renew amount" minlength="1" maxlength="10"
              onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
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
      var url = "{{route('package.destroy','')}}";
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
      $('#total_members').val('');
      $('#total_members_2').val('');
      $('#is_combo').val(0);
      $('.combo-fields').hide();
      
      $('#edit-id').val(button.data('id'));
      $('#name').val(button.data('name'));
      $('#price').val(button.data('price'));
      
      // Handle 5-member circles (max_downlines might be null)
      var totalMembers = button.data('total_members');
      var maxDownlines = button.data('max_downlines');
      
      if(button.data('is_combo') == 1) {
        $('#max_downlines').val('combo');
        $('#max_downlines_2').val(4);
        $('#total_members').val(36); // 5+5+5+21 = 36 for combo packages
        $('#total_members_2').val('36 (5+5+5+21)');
        $('#is_combo').val(1);
        $('.combo-fields').show();
        $('.reward-amount-field').hide(); // Hide reward_amount for combo packages
        $('#reward_amount').removeAttr('required');
      } else if(totalMembers == 5) {
        // 5-Member Simple Circle
        $('#max_downlines').val(5);
        $('#max_downlines_2').val('');
      } else {
        $('#max_downlines').val(maxDownlines);
        $('#max_downlines_2').val(maxDownlines);
      }

      if(button.data('is_combo') != 1){
        $('#total_members').val(totalMembers);
        $('#total_members_2').val(totalMembers);
        $('.reward-amount-field').show(); // Show reward_amount for non-combo packages
        $('#reward_amount').attr('required', 'required');
      }
      $('#reward_amount').val(button.data('reward_amount'));
      // 5-Member Circle 1 (five_a) rewards
      $('#combo_five_a_reward_direct').val(button.data('combo_five_a_reward_direct') || '');
      $('#combo_five_a_reward_autofill').val(button.data('combo_five_a_reward_autofill') || '');
      $('#combo_five_a_autorenew_amount').val(button.data('combo_five_a_autorenew_amount') || '');
      // 5-Member Circle 2 (five_b) rewards
      $('#combo_five_b_reward_direct').val(button.data('combo_five_b_reward_direct') || '');
      $('#combo_five_b_reward_autofill').val(button.data('combo_five_b_reward_autofill') || '');
      $('#combo_five_b_autorenew_amount').val(button.data('combo_five_b_autorenew_amount') || '');
      // 5-Member Circle 3 (five_c) rewards
      $('#combo_five_c_reward_direct').val(button.data('combo_five_c_reward_direct') || '');
      $('#combo_five_c_reward_autofill').val(button.data('combo_five_c_reward_autofill') || '');
      $('#combo_five_c_autorenew_amount').val(button.data('combo_five_c_autorenew_amount') || '');
      // 21-Member circle rewards
      $('#combo_twentyone_reward_amount').val(button.data('combo_twentyone_reward_amount'));
      $('#combo_twentyone_autorenew_amount').val(button.data('combo_twentyone_autorenew_amount'));
      // Section names
      $('#combo_five_a_name').val(button.data('combo_five_a_name') || '');
      $('#combo_five_b_name').val(button.data('combo_five_b_name') || '');
      $('#combo_five_c_name').val(button.data('combo_five_c_name') || '');
      $('#combo_twentyone_name').val(button.data('combo_twentyone_name') || '');
      
      if(button.data('id') > 0){
          $('#max_downlines').attr('disabled',true);
          $(".modal-title").text('Update Package');
      }else{
          $('#max_downlines').attr('disabled',false);
          $(".modal-title").text('Add New Package');
      }
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
        
    $(document).on('change','#max_downlines',function(){
        var max_downline = $(this).val();
        if(max_downline == 2){
            $('#total_members').val(7);
            $('#total_members_2').val(7);
            $('#max_downlines_2').val(2);
            $('#is_combo').val(0);
            $('.combo-fields').hide();
        }
        if(max_downline == 3){
            $('#total_members').val(13);
            $('#total_members_2').val(13);
            $('#max_downlines_2').val(3);
            $('#is_combo').val(0);
            $('.combo-fields').hide();
        }
        if(max_downline == 4){
            $('#total_members').val(21);
            $('#total_members_2').val(21);
            $('#max_downlines_2').val(4);
            $('#is_combo').val(0);
            $('.combo-fields').hide();
        }
        if(max_downline == 5){
            // 5-Member Simple Circle
            $('#total_members').val(5);
            $('#total_members_2').val(5);
            $('#max_downlines_2').val(''); // Set to empty/null for 5-member circles
            $('#is_combo').val(0);
            $('.combo-fields').hide();
        }
        if(max_downline == 'combo'){
            $('#total_members').val(36); // 5+5+5+21 = 36 for combo packages
            $('#total_members_2').val('36 (5+5+5+21)');
            $('#max_downlines_2').val(4);
            $('#is_combo').val(1);
            $('.combo-fields').show();
            $('.reward-amount-field').hide(); // Hide reward_amount for combo packages
            $('#reward_amount').removeAttr('required');
        } else {
            $('.reward-amount-field').show(); // Show reward_amount for non-combo packages
            $('#reward_amount').attr('required', 'required');
        }
    });

  });
</script>
@endsection

@endsection


