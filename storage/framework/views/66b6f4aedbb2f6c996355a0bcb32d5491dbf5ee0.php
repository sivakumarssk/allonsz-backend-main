<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="Ml63mUEFyaGvIM0h52l8aZ6cEGkZ61t2Jw0t9jhv" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="Ml63mUEFyaGvIM0h52l8aZ6cEGkZ61t2Jw0t9jhv" />
    <title>Admin Panel | <?php echo e($setting->bussiness_name); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('admin/css/bootstrap.min.css')); ?>">
    <script src="<?php echo e(asset('admin/js/jquery.min.js')); ?>"></script>
    <link rel="shortcut icon" href="<?php echo e(asset('images/bussiness/'.$setting->favicon)); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <style>
        @import    url('https://fonts.googleapis.com/css2?family=Questrial&display=swap');
        *{font-family:Jost;}
        .btn-custom{background-color:black;color:white;}
        .btn-custom:hover{background-color:black;color:white;}
        .btn-custom:after{background-color:black;color:white;}
        a{color:black;}
        a:hover{text-decoration:none;}
        .card{box-shadow: 0px 2px red;}
        
        @media  screen and (max-width: 468px) {
            video{width:100% !important;}
        }
    </style>


</head>

<body style="background-color:black;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="card mt-5">
                    <div class="card-header" style="background-color:white">
                        <center>
                            <img src="<?php echo e($setting->logo); ?>" style="width:60%">
                        </center>
                    </div>
                    <div class="card-body">
                        <h3><center>Admin Login</center></h3>
                        <hr>
                        <form action="<?php echo e(url('login')); ?>" method="post">
                            <?php echo csrf_field(); ?>
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

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control password" minlength="8" maxlength="14" id="re_pass" required>
                                    <div class="input-group-append">
                                          <div class="input-group-text">
                                              <span class="show-password password-icon"><i class="fa fa-eye-slash"></i></span>
                                              <span class="hide-password password-icon" style="display:none;"><i class="fa fa-eye"></i></span>
                                          </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-4">
                                <input type="submit" class="btn btn-custom btn-block" value="Login">
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="https://allons-z.com/" class="text-danger" target="_blank">Go to User Panel</a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="<?php echo e(url('forget-password')); ?>" class="text-danger" style="float:right" target="_blank">Forgot Password ?</a>
                                    </div>
                                </div>
                                <p class="mt-5"><center><small>© Copyright <a href="https://allons-z.com/" target="_blank">Allons-z</a></small></center></p>
                                <p>
                                    <center>
                                        <small>Designed by <a href="https://analogueitsolutions.com/" target="_blank">Analogue IT Solutions</a></small> &nbsp;Pvt Ltd
                                    </center>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo e(asset('admin/plugins/jquery/jquery.min.js')); ?>"></script>
    <script>
        $(document).ready(function(){
            $('.hide-password').hide();
            
            $(document).on('click','.show-password',function(){
                $('.password').attr('type','text');
                $('.show-password').hide();
                $('.hide-password').show();
            });
            $(document).on('click','.hide-password',function(){
                $('.password').attr('type','password');
                $('.hide-password').hide();
                $('.show-password').show();
            });
        });
        
    </script>
    <script>
        $(document).ready(function(){
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function () {
                window.history.pushState(null, "", window.location.href);
            };

        });
    </script>
    
</body>

<script type="module">

  import { initializeApp } from "https://www.gstatic.com/firebasejs/9.9.3/firebase-app.js";

  import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.9.3/firebase-analytics.js";

  const firebaseConfig = {

    apiKey: "AIzaSyCMIXqJyXbmxmFMdywPuFbYd6cRUx-l6nc",

    authDomain: "school-979f6.firebaseapp.com",

    databaseURL: "https://school-979f6.firebaseio.com",

    projectId: "school-979f6",

    storageBucket: "school-979f6.appspot.com",

    messagingSenderId: "308636612449",

    appId: "1:308636612449:web:603eb003f33921ad9db720",

    measurementId: "G-45CQ9YMRNN"

  };


  // Initialize Firebase

  const app = initializeApp(firebaseConfig);

  const analytics = getAnalytics(app);
  
  
    import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/9.9.3/firebase-messaging.js";

    const messaging = getMessaging();
    getToken(messaging, { vapidKey: 'BALAriAKrgC8UL3txmvobWMeu2wRZ4g-7wX4TxOI6-JzE9b5oZWUMwUANBJ2w0V3glmlsBGIV0SNlofoApPf9e0' }).then((currentToken) => {
      if (currentToken) {
        document.getElementById("device_token").value = currentToken;
      } else {
        // Show permission request UI
        console.log('No registration token available. Request permission to generate one.');
        // ...
      }
    }).catch((err) => {
      console.log('An error occurred while retrieving token. ', err);
      // ...
    });
    

</script>

</html><?php /**PATH C:\xampp\htdocs\allonsz-backend-main\resources\views/admin/login.blade.php ENDPATH**/ ?>