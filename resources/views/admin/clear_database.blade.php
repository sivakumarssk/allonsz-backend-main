@extends('layouts.admin')

@section('title')
    Clear Database
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Clear Database</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <center>
                                <h3 class="text-danger mt-5">Danger Area</h3>
                                <p>Make sure you are going to permanently delete all the data</p>
                                <p>Once this is deleted, you cant restore this</p>
                                <button class="btn btn-lg btn-danger mt-3 mb-3" data-toggle="modal" data-target="#deleteModal">Clear Database</button>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
        <p class="text">You are going truncate all the databases</p>
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
        var token = "{{csrf_token()}}";

        $(document).on('click','#delete-button',function(){
            var url = "{{url('post-clear-database')}}";
            $.ajax({
                url : url,
                type: "POST",
                data : {'_token':token},
                success: function(data)
                {
                    window.location.reload();
                }
            });
        });
    });
</script>
@endsection

@endsection