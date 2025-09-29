<?php $__env->startSection('title'); ?>
    Package List
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
                    <th>Max DownLines</th>
                    <th>Total Members</th>
                    <th>Reward Amount</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                    
                    <?php $i = 0; ?>
                  <?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php $i++; ?>
                  <tr>
                    <td><?php echo e($i); ?></td>
                    <td><?php echo e($package->name); ?></td>
                    <td><?php echo e($package->price); ?></td>
                    <td><?php echo e($package->max_downlines); ?></td>
                    <td><?php echo e($package->total_members); ?></td>
                    <td><?php echo e($package->reward_amount); ?></td>
                    <td>
                      <a href="<?php echo e(route('package.show',$package->id)); ?>" target="_blank"  class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="<?php echo e($package->id); ?>" data-name="<?php echo e($package->name); ?>" data-price="<?php echo e($package->price); ?>" 
                      data-max_downlines="<?php echo e($package->max_downlines); ?>" data-total_members="<?php echo e($package->total_members); ?>" data-reward_amount="<?php echo e($package->reward_amount); ?>"><i class="fa fa-edit"></i></button>
                      <!--<?php if($package->deleted_at): ?>-->
                      <!--<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo e($package->id); ?>" data-action="restore"><i class="fa fa-undo"></i></button>-->
                      <!--<?php else: ?>-->
                      <!--<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo e($package->id); ?>" data-action="delete"><i class="fa fa-trash"></i></button>-->
                      <!--<?php endif; ?>-->
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
        <h5 class="modal-title" id="exampleModalLabel">Add Package</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(route('package.store')); ?>" method="post" class="add-form">
        <?php echo csrf_field(); ?>
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
            <label for="max_downlines" class="col-form-label">Max Downlines:</label>
            <select name="max_downlines" class="form-control" id="max_downlines" placeholder="Max Downlines" required>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
            <input type="hidden" name="max_downlines_2" class="form-control" id="max_downlines_2" placeholder="Max downlines" required>
          </div>
          <div class="form-group">
            <label for="total_members" class="col-form-label">Total Members:</label>
            <input type="hidden" name="total_members" class="form-control" id="total_members" placeholder="Total Members" required>
            <input type="text" name="total_members_2" class="form-control" id="total_members_2" placeholder="Total Members" required disabled>
          </div>
          <div class="form-group">
            <label for="reward" class="col-form-label">Reward Amount:</label>
            <input type="text" name="reward_amount" class="form-control" id="reward_amount" placeholder="Reward Amount" minlength="1" maxlength="10"  
            onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
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
      var url = "<?php echo e(route('package.destroy','')); ?>";
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
      
      $('#edit-id').val(button.data('id'));
      $('#name').val(button.data('name'));
      $('#price').val(button.data('price'));
      $('#max_downlines').val(button.data('max_downlines'));
      $('#max_downlines_2').val(button.data('max_downlines'));
      $('#total_members').val(button.data('total_members'));
      $('#total_members_2').val(button.data('total_members'));
      $('#reward_amount').val(button.data('reward_amount'));
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
                url : "<?php echo e(url('update-user-status')); ?>",
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
        }
        if(max_downline == 3){
            $('#total_members').val(13);
            $('#total_members_2').val(13);
            $('#max_downlines_2').val(3);
        }
        if(max_downline == 4){
            $('#total_members').val(21);
            $('#total_members_2').val(21);
            $('#max_downlines_2').val(4);
        }
    });

  });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/package/index.blade.php ENDPATH**/ ?>