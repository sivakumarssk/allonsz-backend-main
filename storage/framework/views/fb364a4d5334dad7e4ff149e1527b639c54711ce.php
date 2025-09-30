<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/css/login.css')); ?>"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <?php $setting = \App\Models\Setting::first(); ?>
    <title>Forgot Password | <?php echo e($setting->bussiness_name); ?></title>
    <style>
        @import  url('https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        body{font-family:Jost;}
        .password-icon{
            float: right;
            margin-top: -22px;
        }
        .alert-success{
            background-color:green;
            color:white;
        }
    </style>
</head>
<body>
    <div class="main">

        <!-- Sign up form -->
        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="<?php echo e($setting->logo); ?>" alt="sing up image"></figure>
                        <a href="<?php echo e(url('/')); ?>" class="signup-image-link">Login Here</a>
                    </div>

                    <div class="signin-form">
                        <h2 class="form-title">Forgot Password</h2>
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
                        <form method="post" action="<?php echo e(url('forget-password')); ?>" class="register-form" id="login-form">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label for="your_pass"><i class="fa fa-envelope"></i></label>
                                <input type="email" name="email" placeholder="Email" required>
                            </div>
                                <?php if($errors->has('email')): ?>
                                    <p style="color:red"><?php echo e($errors->first('email')); ?></p>
                                <?php endif; ?>
                            
                            <div class="form-group form-button">
                            	<input type="submit" id="signin" class="form-submit" value="Send Reset Password Link"/>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/forgot_password.blade.php ENDPATH**/ ?>