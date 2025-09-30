            <select name="country" class="form-control" id="country" required>
                <option value="">--Select Country--</option>
                <?php $__empty_1 = true; $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <option value="<?php echo e($country->id); ?>" <?php echo e($country->id == $customer->country_id ? 'selected' : ''); ?>><?php echo e($country->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
            </select><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/partials/get_countries.blade.php ENDPATH**/ ?>