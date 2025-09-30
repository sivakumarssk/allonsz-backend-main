@extends('layouts.admin')

@section('title')
    Setting
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Settings</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Setting</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                Business Setting
              </div>
              <div class="card-body">
                @if(session()->has('success'))
                    <div class="alert alert-success">
                        {{ session()->get('success') }}
                    </div>
                @endif
                @if(session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <form action="{{route('setting.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bussiness Name</label>
                                <input type="text" class="form-control" name="bussiness_name" value="{{$setting->bussiness_name}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Logo <img src="{{$setting->logo}}" width="30px;"></label>
                                <input type="file" class="form-control" name="logo" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Favicon <img src="{{$setting->favicon}}" width="30px;"></label>
                                <input type="file" class="form-control" name="favicon" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Msg91 Key</label>
                                <input type="text" class="form-control" name="msg91_key" value="{{$setting->msg91_key}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Msg91 Sender</label>
                                <input type="text" class="form-control" name="msg91_sender" value="{{$setting->msg91_sender}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Msg91 Flow Id</label>
                                <input type="text" class="form-control" name="msg91_flow_id" value="{{$setting->msg91_flow_id}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Razorpay Key</label>
                                <input type="text" class="form-control" name="razorpay_key" value="{{$setting->razorpay_key}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Razorpay Secret</label>
                                <input type="text" class="form-control" name="razorpay_secret" value="{{$setting->razorpay_secret}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>FCM Key</label>
                                <input type="text" class="form-control" name="fcm_key" value="{{$setting->fcm_key}}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Call Support Number</label>
                                <input type="text" class="form-control" name="call_support_number" value="{{$setting->call_support_number}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Whatsapp Support Number</label>
                                <input type="text" class="form-control" name="whatsapp_support_number" value="{{$setting->whatsapp_support_number}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email Support</label>
                                <input type="email" class="form-control" name="email_support" value="{{$setting->email_support}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Google map api key</label>
                                <input type="text" class="form-control" name="google_map_api_key" value="{{$setting->google_map_api_key}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pagination</label>
                                <input type="text" class="form-control" name="pagination" value="{{$setting->pagination}}" 
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="submit" class="btn btn-block bg-gradient-primary btn-flat" value="Save">
                        </div>
                    </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    

@section('script')

<script>
$(document).ready(function(){

});
</script>

@endsection

@endsection




