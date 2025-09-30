<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('public/admin/plugins/summernote/summernote-bs4.min.css')}}">
    
    <?php $setting = \App\Models\Setting::first(); ?>
    <title>Privacy Policy | {{$setting->bussiness_name}}</title>

</head>
<body>
    <div class="container">
        <section class="row justify-content-center mt-5">
            <div class="col-md-8">
                <center>
                    <img src="{{$setting->logo}}" style="height:100px" alt="Logo">
                </center>
                <div class="card p-4 mt-3">
                    {!! $privacy_policy !!}
                </div>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS Bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{asset('public/admin/plugins/summernote/summernote-bs4.min.js')}}"></script>
    <script>
      $(function () {
        $('#summernote').summernote();
      });
    </script>
</body>
</html>
