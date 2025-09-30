<?php $__env->startSection('title'); ?>
    Trip List
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
                    <!-- <th>Action</th> -->
                  </tr>
                  </thead>
                  <tbody>
                            <?php $i = 0; ?>
                            <?php $__empty_1 = true; $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $i++; ?>
                            <tr>
                                <td><?php echo e($i); ?></td>
                                <td><a href="<?php echo e(url('show-customer',$trip->user_id)); ?>"><?php echo e($trip->user->username); ?></a></td>
                                <td><?php echo e($trip->tour ? $trip->tour->name : 'N/A'); ?></td>
                                <td><?php echo e($trip->members); ?></td>
                                <td><?php echo e($trip->from_date); ?></td>
                                <td><?php echo e($trip->to_date); ?></td>
                                <td><?php echo e($trip->from_place); ?></td>
                                <td><?php echo e($trip->status); ?></td>
                                <td>
                                    <a href="<?php echo e(route('trip.show',$trip->id)); ?>" target="_blank"  class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="<?php echo e($trip->id); ?>" data-tour="<?php echo e($trip->tour_id); ?>" data-members="<?php echo e($trip->members); ?>"
                                    data-from_date="<?php echo e($trip->from_date); ?>" data-to_date="<?php echo e($trip->to_date); ?>" data-from_place="<?php echo e($trip->from_place); ?>" data-status="<?php echo e($trip->status); ?>"><i class="fa fa-edit"></i></button>
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
        <h5 class="modal-title" id="exampleModalLabel">Update Trip</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(route('trip.store')); ?>" method="post" class="add-form">
        <?php echo csrf_field(); ?>
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="members" class="col-form-label">Select Tour:</label>
            <select name="tour" id="tour" class="form-control" required>
                <?php $__empty_1 = true; $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <option value="<?php echo e($tour->id); ?>"><?php echo e($tour->name); ?> - <?php echo e($tour->place); ?> at Rs <?php echo e($tour->price); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
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
      var url = "<?php echo e(route('country.destroy','')); ?>";
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
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/trip/index.blade.php ENDPATH**/ ?>