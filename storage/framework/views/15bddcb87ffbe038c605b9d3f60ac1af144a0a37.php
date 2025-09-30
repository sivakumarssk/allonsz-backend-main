<?php $__env->startSection('title'); ?>
    Customer List
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-12">
                <?php if(session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session()->get('error')); ?>

                </div>
                <?php endif; ?>
                <?php if(session()->has('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session()->get('success')); ?>

                </div>
                <?php endif; ?>
            </div>
          <div class="col-sm-6">
            <h1>Customers</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Customers</li>
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
                <button class="btn btn-sm btn-success right" data-toggle="modal" data-target="#addModal">Add New Customer</button>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>S No</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Referal Code</th>
                    <th>Status</th>
                    <!-- <th>Action</th> -->
                  </tr>
                  </thead>
                  <tbody>
                    
                    <?php $i = 0; ?>
                  <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php $i++; ?>
                  <tr>
                    <td><?php echo e($i); ?></td>
                    <td><?php echo e($user->name); ?></td>
                    <td><?php echo e($user->username); ?></td>
                    <td><?php echo e($user->email); ?></td>
                    <td><?php echo e($user->phone); ?></td>
                    <td><?php echo e($user->referal_code); ?></td>
                    <td>
                        <!--<input type="checkbox" name="my-checkbox" class="status" data-id="<?php echo e($user->id); ?>" data-bootstrap-switch data-on-text="Active" -->
                        <!--data-off-text="Inactive" <?php echo e($user->status == 'Active' ? 'checked' : ''); ?>>-->
                        <?php echo e($user->status); ?>

                    </td>
                    <td>
                      <a href="<?php echo e(url('show-customer',$user->id)); ?>" target="_blank"  class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                      <button class="btn btn-sm btn-primary"  data-toggle="modal" data-target="#addModal" data-id="<?php echo e($user->id); ?>" data-first_name="<?php echo e($user->first_name); ?>" data-last_name="<?php echo e($user->last_name); ?>" data-email="<?php echo e($user->email); ?>" data-phone="<?php echo e($user->phone); ?>" data-gender="<?php echo e($user->gender); ?>" 
                      data-country="<?php echo e($user->country_id); ?>" data-state="<?php echo e($user->state_id); ?>" data-district="<?php echo e($user->district_id); ?>" data-mandal="<?php echo e($user->mandal_id); ?>"><i class="fa fa-edit"></i></button>
                      <?php if($user->status == 'Pending'): ?>
                      <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo e($user->id); ?>" data-action="permanent"><i class="fa fa-trash"></i></button>
                      <?php endif; ?>
                    </td>

                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <?php endif; ?>
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
        <h5 class="modal-title" id="exampleModalLabel">Add Customer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(url('store-user')); ?>" method="post" class="add-form">
        <?php echo csrf_field(); ?>
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="name" class="col-form-label">First Name:</label>
            <input type="text" name="first_name" id="first_name" class="form-control" min="3" max="30" placeholder="First Name" minlength="4" maxlength="20"
                          onkeypress="return event.charCode >= 65 && event.charCode <= 90 || event.charCode >= 97 && event.charCode <= 122 || event.charCode == 32" required>
          </div>
          <div class="form-group">
            <label for="name" class="col-form-label">Last Name:</label>
            <input type="text" name="last_name" id="last_name" class="form-control" min="3" max="30" placeholder="Last Name" minlength="4" maxlength="20"
                          onkeypress="return event.charCode >= 65 && event.charCode <= 90 || event.charCode >= 97 && event.charCode <= 122 || event.charCode == 32" required>
          </div>
          <div class="form-group">
            <label for="email" class="col-form-label">Email:</label>
            <input type="email" name="email" class="form-control" id="email" maxlength="40" placeholder="Email" required>
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">Phone:</label>
            <input type="text" name="phone" class="form-control" id="phone" value="<?php echo e(old('phone')); ?>" placeholder="Phone" minlength="10" maxlength="10" 
                                      onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">Gender:</label>
            <select name="gender" class="form-control" id="gender" required>
                <option value="">--Select Gender--</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">Country:</label>
            <select name="country" class="form-control" id="country" required>
                <option value="">--Select Country--</option>
            </select>
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">State:</label>
            <select name="state" class="form-control" id="state" required>
                <option value="">--Select State--</option>
            </select>
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">District:</label>
            <select name="district" class="form-control" id="district" required>
                <option value="">--Select District--</option>
            </select>
          </div>
          <div class="form-group">
            <label for="phone" class="col-form-label">Mandal:</label>
            <select name="mandal" class="form-control" id="mandal" required>
                <option value="">--Select Mandal--</option>
            </select>
          </div>
          <div class="form-group">
            <label for="address" class="col-form-label">Address:</label>
            <textarea name="address" class="form-control" id="address"></textarea>
          </div>
          <div class="form-group">
            <label for="address" class="col-form-label">Pin:</label>
            <input type="text" name="pin" class="form-control" id="pin">
          </div>
          <!--<div class="form-group">-->
          <!--  <label for="phone" class="col-form-label">Password:</label>-->
          <!--  <input type="password" name="password" class="form-control" id="password">-->
          <!--</div>-->
          <div class="form-group">
            <label for="role" class="col-form-label">Role:</label>
            <select name="role" class="form-control">
              <option value="customer">Customer</option>
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
        <!-- <button type="button" class="btn btn-danger" data-dismiss="modal" id="delete-button">Soft Delete</button> -->
        <button type="button" class="btn btn-danger" data-dismiss="modal" id="permanent-delete-button">Permanent Delete</button>
      </div>
    </div>
  </div>
</div>


<?php $__env->startSection('script'); ?>


<script>
  $(document).ready(function(){
    var id = '';
    var action = '';
    var token = "<?php echo e(csrf_token()); ?>";
    
    $('#deleteModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      id = button.data('id');
      $('#delete-id').val(id);
      $('.text').text('You are going to delete this item..');
      // action= button.data('action');
      // $('#delete-button').removeClass('btn-success');
      // $('#delete-button').removeClass('btn-danger');
      // if(action == 'delete'){
      //     $('#delete-button').addClass('btn-danger');
      //     $('#delete-button').text('Soft Delete');
      //     $('.text').text('You are going to delete this item..');
      //     $('#permanent-delete-button').show();
      // }else{
      //     $('#delete-button').addClass('btn-success');
      //     $('#delete-button').text('Confirm Restore');
      //     $('.text').text('You are going to restore this item..');
      //     $('#permanent-delete-button').hide();
      // }
    });

    $(document).on('click','#delete-button',function(){
      var url = "<?php echo e(url('delete-user')); ?>";
      $.ajax({
        url : url,
        type: "POST",
        data : {'_token':token,'id':id,'action':action},
        success: function(data)
        {
          window.location.reload();
        }
      });
    });

    $(document).on('click','#permanent-delete-button',function(){
      var url = "<?php echo e(url('delete-user')); ?>";
      $.ajax({
        url : url,
        type: "POST",
        data : {'_token':token,'id':id,'action':'permanent'},
        success: function(data)
        {
          window.location.reload();
        }
      });
    });

    $('#addModal').on('show.bs.modal', function (event) {
      get_countries();
      get_states();
      get_districts();
      get_mandals();

      console.log('Modal opened', event.relatedTarget);
      
      var button = $(event.relatedTarget);
      $('#edit-id').val(button.data('id'));
      $('#first_name').val(button.data('first_name'));
      $('#last_name').val(button.data('last_name'));
      $('#email').val(button.data('email'));
      $('#phone').val(button.data('phone'));
      $('#gender').val(button.data('gender'));
      $('#customer_id').val(button.data('customer_id'));
      $('#country').val(button.data('country'));
      $('#state').val(button.data('state'));
      $('#district').val(button.data('district'));
      $('#mandal').val(button.data('mandal'));
      $('#address').val(button.data('address'));
      $('#pin').val(button.data('pin'));
      if(button.data('id') > 0){
          $(".modal-title").text('Update Customer');
      }else{
          $(".modal-title").text('Add New Customer');
      }
    });
    
    $('.status').bootstrapSwitch('state');
    $('.status').on('switchChange.bootstrapSwitch',function () {
            var id = $(this).data('id');
            $.ajax({
                url : "<?php echo e(url('update-user-status')); ?>",
                type: "post",
                data : {'_token':token,'id':id,},
                success: function(data)
                {
                  //
                }
            });
    });

    $(document).on('change','#country',function(){
      get_states();
    });

    $(document).on('change','#state',function(){
      get_districts();
    });

    $(document).on('change','#district',function(){
      get_mandals();
    });

    function get_countries()
    {
      var customer_id = $('#edit-id').val();
      $.ajax({
        url : "<?php echo e(url('/get-countries')); ?>",
        type: "post",
        data : {'_token':token,'customer_id':customer_id},
          success: function(data)
        {
        $('#country').html(data);
        }
      });
    }

    function get_states()
    {
      var country_id = $('#country').val();
      var customer_id = $('#edit-id').val();
      $.ajax({
        url : "<?php echo e(url('/get-states')); ?>",
        type: "post",
        data : {'_token':token,'country_id':country_id,'customer_id':customer_id},
          success: function(data)
        {
        $('#state').html(data);
        }
      });
    }

    function get_districts()
    {
      var state_id = $('#state').val();
      var customer_id = $('#edit-id').val();            
      $.ajax({
        url : "<?php echo e(url('/get-districts')); ?>",
        type: "post",
        data : {'_token':token,'state_id':state_id,'customer_id':customer_id},
          success: function(data)
        {
        $('#district').html(data);
        }
      });
    }

    function get_mandals()
    {
      var district_id = $('#district').val();
      var customer_id = $('#edit-id').val();
            
      $.ajax({
        url : "<?php echo e(url('/get-mandals')); ?>",
        type: "post",
        data : {'_token':token,'district_id':district_id,'customer_id':customer_id},
          success: function(data)
        {
        $('#mandal').html(data);
        }
      });
    }

  });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/customers/index.blade.php ENDPATH**/ ?>