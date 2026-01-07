@extends('layouts.admin')

@section('title')
    Customer Details
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Customer Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Customer Details</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{$customer->photo}}"
                       alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{$customer->name}}</h3>

                <p class="text-muted text-center">{{$customer->role}}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Username</b> <a class="float-right">{{$customer->username}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Email</b> <a class="float-right">{{$customer->email}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Phone</b> <a class="float-right">{{$customer->phone}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Gender</b> <a class="float-right">{{$customer->gender}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Withdrawable Wallet</b> <a class="float-right">₹{{number_format($customer->wallet ?? 0, 2)}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Non-Withdrawable Amount</b> <a class="float-right">₹{{number_format($customer->not_withdraw_amount ?? 0, 2)}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Total Wallet</b> <a class="float-right"><strong>₹{{number_format(($customer->wallet ?? 0) + ($customer->not_withdraw_amount ?? 0), 2)}}</strong></a>
                  </li>
                  <li class="list-group-item">
                    <b>Referal</b> <a class="float-right">{{$customer->referal_code}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Status</b> <a class="float-right">
                        <input type="checkbox" name="my-checkbox" class="status" data-id="{{$customer->id}}" data-bootstrap-switch data-on-text="Active" 
                        data-off-text="Inactive" {{$customer->status == 'Active' ? 'checked' : ''}}>
                    </a>
                  </li>
                  <li class="list-group-item">
                    <b>Document Status</b> <a class="float-right">
                        <input type="checkbox" name="document-checkbox" class="document-status" data-id="{{$customer->id}}" data-bootstrap-switch data-on-text="Verified" 
                        data-off-text="Pending" {{$customer->email_sent_document_status == 'Verified' ? 'checked' : ''}}>
                    </a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#basic" data-toggle="tab">Basic Info</a></li>
                  <li class="nav-item"><a class="nav-link" href="#referals" data-toggle="tab">Referals</a></li>
                  <li class="nav-item"><a class="nav-link" href="#subscriptions" data-toggle="tab">Subscriptions</a></li>
                  <li class="nav-item"><a class="nav-link" href="#circles" data-toggle="tab">Circles</a></li>
                  <li class="nav-item"><a class="nav-link" href="#trips" data-toggle="tab">Trips</a></li>
                  <li class="nav-item"><a class="nav-link" href="#transactions" data-toggle="tab">Transactions</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="basic">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                          <tr style="display:none;">
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>

                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              
                          </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Name</th>
                          <td>{{$customer->name}}</td>
                          <td>Role</th>
                          <td>{{$customer->role}}</td>
                        </tr>

                        <tr>
                          <td>Email</th>
                          <td>{{$customer->email}}</td>
                          <td>Phone</th>
                          <td>{{$customer->phone}}</td>
                        </tr>
                        <tr>
                          <td>Gender</th>
                          <td>{{$customer->gender}}</td>
                          <td>Created at</th>
                          <td>{{$customer->created_at}}</td>
                        </tr>
                        <tr>
                          <td>Referal Code</th>
                          <td>{{$customer->referal_code}}</td>
                          <td>Refered By</th>
                          <td><a href="{{url('show-customer',$customer->referal_id)}}" target="_blank">{{$customer->referal ? $customer->referal->username : 'N/A'}}</a></td>
                        </tr>

                        <tr>
                          <td>Adhar Status</td>
                          <td>{{$customer->aadhar_status}}</td>
                          <td>Pan Status</td>
                          <td>{{$customer->pan_status}}</td>
                        </tr>
                        <tr>
                          <td>Bank Status</td>
                          <td>{{$customer->bank_status}}</td>
                          <td>Document Status</td>
                          <td>{{$customer->document_status}}</td>
                        </tr>
                        <tr>
                          <td>Adhar Details</td>
                          <td colspan="3">
                          @php
                            $aadharDetails = !empty($customer->aadhar_details) ? json_decode($customer->aadhar_details, true) : [];

                            if (is_array($aadharDetails) && count($aadharDetails) > 0) {
                                $firstKey = array_key_first($aadharDetails);
                                $s3_file_url = $aadharDetails[$firstKey]['msg'][0]['s3_file_url'] ?? '#';
                                $aadhar_name = $aadharDetails[$firstKey]['msg'][0]['data']['name'] ?? 'null';
                                $father_name = $aadharDetails[$firstKey]['msg'][0]['data']['Father Name'] ?? 'null';
                                $dob = $aadharDetails[$firstKey]['msg'][0]['data']['dob'] ?? 'null';
                                $aadhar_no = $aadharDetails[$firstKey]['msg'][0]['data']['aadhar_number'] ?? 'null';
                                $gender = $aadharDetails[$firstKey]['msg'][0]['data']['gender'] ?? 'null';
                                $address = $aadharDetails[$firstKey]['msg'][0]['data']['address'] ?? 'null';
                                $co = $aadharDetails[$firstKey]['msg'][0]['data']['co'] ?? 'null';
                                $photo = $aadharDetails[$firstKey]['msg'][0]['data']['photo'] ?? 'null';
                            } else {
                                $s3_file_url = '#';
                                $aadhar_name = 'null';
                                $father_name = 'null';
                                $dob = 'null';
                                $aadhar_no = 'null';
                                $gender = 'null';
                                $address = 'null';
                                $co = 'null';
                                $photo = 'null';
                            }
                        @endphp
                          Adhar no:{{$aadhar_no}}, Name:{{$aadhar_name}}, Father:{{$father_name}}, DOB:{{$dob}}, Gender:{{$gender}}, 
                          Address:{{$father_name}} CO:{{$co}},
                          <a href="{{$s3_file_url}}" target="_blank">Photo Link,</a>
                          <a href="{{$photo}}" target="_blank">File Link</a>
                          </td>
                        </tr>
                        <tr>
                          <td>Pan Details</td>
                          <td colspan="3">{{$customer->pan_details}}</td>
                        </tr>
                        <tr>
                          <td>Bank Details</td>
                          <td colspan="3">{{$customer->bank_details}}</td>
                        </tr>
                      </tbody>
                    </table>
                    </div>
                  </div>
                  <!-- /.tab-pane -->
                  
                  <div class="tab-pane" id="referals">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>Name</th>
                           <th>Email</th>
                           <th>Phone</th>
                           <th>Username</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($customer->downlines as $downline)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$downline->name}}</td>
                                <td>{{$downline->email}}</td>
                                <td>{{$downline->phone}}</td>
                                <td>{{$downline->username}}</td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  </div>

                  <div class="tab-pane" id="subscriptions">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>Package</th>
                           <th>Price</th>
                           <th>Max Downlines</th>
                           <th>Total Members</th>
                           <th>Circles</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($customer->subscriptions as $subscription)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$subscription->name}}</td>
                                <td>{{$subscription->price}}</td>
                                <td>{{$subscription->max_downlines}}</td>
                                <td>{{$subscription->total_members}}</td>
                                <td>{{$subscription->circles->count()}}</td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  </div>
                  
                  <div class="tab-pane" id="circles">
                    
                    @forelse($customer->active_circles() as $circle)
                        <!--<div class="card">-->
                        <!--    <div class="card-body">-->
                        
                                <center>
                                    
                                    <?php 
                                        // Check if this is a 5-member circle (simple circle)
                                        $is_5_member = $circle->package->total_members == 5;
                                        
                                        if($is_5_member){
                                            $circle_container = 'five-circle-container';
                                        } elseif($circle->package->max_downlines == 2){
                                            $circle_container = 'two-circle-container';
                                        } elseif($circle->package->max_downlines == 3){
                                            $circle_container = 'three-circle-container';
                                        } elseif($circle->package->max_downlines == 4){
                                            $circle_container = 'four-circle-container';
                                        } else {
                                            $circle_container = 'two-circle-container'; // Default
                                        }
                                    
                                    ?>
                                    
                                    @if($is_5_member)
                                        <!-- 5-Member Circle Display (Simple Circle) -->
                                        <div class="alert alert-info">
                                            <strong>5-Member Simple Circle</strong> - User is in Position 5, Direct Referrals fill Positions 1-4
                                        </div>
                                        @if($circle->status == 'Completed')
                                            <div class="alert alert-warning">
                                                <strong>Circle Completed!</strong> Please update the package.
                                            </div>
                                        @endif
                                    @endif
                                    
                                    <div class="{{$circle_container}} circle">
                                    @if($is_5_member)
                                        <!-- 5-Member Circle Layout (Circular Style) -->
                                        <div class="five-circle1">
                                            <div class="base">
                                                <a href="{{$circle->circle_member(5)->user ? url('show-customer',$circle->circle_member(5)->user->id) : '#'}}" target="_blank">
                                                    {{$circle->circle_member(5)->user ? $circle->circle_member(5)->user->username : 'Empty-5'}}
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="five-circle2">
                                            <div><a href="{{$circle->circle_member(1)->user ? url('show-customer',$circle->circle_member(1)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(1)->user ? $circle->circle_member(1)->user->username : 'Empty-1'}}</a></div>
                                            <div><a href="{{$circle->circle_member(2)->user ? url('show-customer',$circle->circle_member(2)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(2)->user ? $circle->circle_member(2)->user->username : 'Empty-2'}}</a></div>
                                            <div><a href="{{$circle->circle_member(3)->user ? url('show-customer',$circle->circle_member(3)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(3)->user ? $circle->circle_member(3)->user->username : 'Empty-3'}}</a></div>
                                            <div><a href="{{$circle->circle_member(4)->user ? url('show-customer',$circle->circle_member(4)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(4)->user ? $circle->circle_member(4)->user->username : 'Empty-4'}}</a></div>
                                        </div>
                                    @else
                                    @forelse($circle->members as $member)
                                    
                                        @if($circle->package->max_downlines == 2)
                                            @if($member->position == 1)
                                                <div class="two-circle1">
                                                    <div class="base"><a href="{{$circle->circle_member(7)->user ? url('show-customer',$circle->circle_member(7)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(7)->user ? $circle->circle_member(7)->user->username : 'Empty-7'}}</a></div>
                                                </div>
                                            @endif
                                                
                                            @if($member->position == 2)
                                                <div class="two-circle2">
                                            @endif
                                            
                                            @if($member->position == 2)
                                                <div><a href="{{$circle->circle_member(3)->user ? url('show-customer',$circle->circle_member(3)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(3)->user ? $circle->circle_member(3)->user->username : 'Empty-3'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 3)
                                                <div><a href="{{$circle->circle_member(6)->user ? url('show-customer',$circle->circle_member(6)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(6)->user ? $circle->circle_member(6)->user->username : 'Empty-6'}}</a></div>
                                            @endif
    
                                            @if($member->position == 4)
                                                </div>
                                                <div class="two-circle3">
    
                                            @endif
                                            
                                            @if($member->position == 6)
                                                <div><a href="{{$circle->circle_member(1)->user ? url('show-customer',$circle->circle_member(1)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(1)->user ? $circle->circle_member(1)->user->username : 'Empty-1'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 4)
                                                <div><a href="{{$circle->circle_member(2)->user ? url('show-customer',$circle->circle_member(2)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(2)->user ? $circle->circle_member(2)->user->username : 'Empty-2'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 5)
                                                <div><a href="{{$circle->circle_member(4)->user ? url('show-customer',$circle->circle_member(4)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(4)->user ? $circle->circle_member(4)->user->username : 'Empty-4'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 7)
                                                <div><a href="{{$circle->circle_member(5)->user ? url('show-customer',$circle->circle_member(5)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(5)->user ? $circle->circle_member(5)->user->username : 'Empty-5'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 7)
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @if($circle->package->max_downlines == 3)
                                            @if($member->position == 1)
                                                <div class="three-circle1">
                                                    <div class="base"><a href="{{$circle->circle_member(13)->user ? url('show-customer',$circle->circle_member(13)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(13)->user ? $circle->circle_member(13)->user->username : 'Empty-13'}}</a></div>
                                                </div>
                                            @endif
                                                
                                            @if($member->position == 2)
                                                <div class="three-circle2">
                                            @endif
                                            
                                            @if($member->position == 2)
                                                <div><a href="{{$circle->circle_member(8)->user ? url('show-customer',$circle->circle_member(8)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(8)->user ? $circle->circle_member(8)->user->username : 'Empty-8'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 3)
                                                <div><a href="{{$circle->circle_member(12)->user ? url('show-customer',$circle->circle_member(12)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(12)->user ? $circle->circle_member(12)->user->username : 'Empty-12'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 4)
                                                <div><a href="{{$circle->circle_member(4)->user ? url('show-customer',$circle->circle_member(4)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(4)->user ? $circle->circle_member(4)->user->username : 'Empty-4'}}</a></div>
                                            @endif
    
                                            @if($member->position == 4)
                                                </div>
                                                <div class="three-circle3">
    
                                            @endif
                                            
                                            @if($member->position ==5)
                                                <div><a href="{{$circle->circle_member(5)->user ? url('show-customer',$circle->circle_member(5)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(5)->user ? $circle->circle_member(5)->user->username : 'Empty-5'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==6)
                                                <div><a href="{{$circle->circle_member(6)->user ? url('show-customer',$circle->circle_member(6)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(6)->user ? $circle->circle_member(6)->user->username : 'Empty-6'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==7)
                                                <div><a href="{{$circle->circle_member(7)->user ? url('show-customer',$circle->circle_member(7)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(7)->user ? $circle->circle_member(7)->user->username : 'Empty-7'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==8)
                                                <div><a href="{{$circle->circle_member(9)->user ? url('show-customer',$circle->circle_member(9)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(9)->user ? $circle->circle_member(9)->user->username : 'Empty-9'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==9)
                                                <div><a href="{{$circle->circle_member(10)->user ? url('show-customer',$circle->circle_member(10)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(10)->user ? $circle->circle_member(10)->user->username : 'Empty-10'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==10)
                                                <div><a href="{{$circle->circle_member(11)->user ? url('show-customer',$circle->circle_member(11)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(11)->user ? $circle->circle_member(11)->user->username : 'Empty-11'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==11)
                                                <div><a href="{{$circle->circle_member(1)->user ? url('show-customer',$circle->circle_member(1)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(1)->user ? $circle->circle_member(1)->user->username : 'Empty-1'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==12)
                                                <div><a href="{{$circle->circle_member(2)->user ? url('show-customer',$circle->circle_member(2)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(2)->user ? $circle->circle_member(2)->user->username : 'Empty-2'}}</a></div>
                                            @endif
                                            
                                            @if($member->position ==13)
                                                <div><a href="{{$circle->circle_member(3)->user ? url('show-customer',$circle->circle_member(3)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(3)->user ? $circle->circle_member(3)->user->username : 'Empty-3'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 13)
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @if($circle->package->max_downlines == 4)
                                            @if($member->position == 1)
                                                <div class="four-circle1">
                                                    <div class="base"><a href="{{$circle->circle_member(21)->user ? url('show-customer',$circle->circle_member(21)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(21)->user ? $circle->circle_member(21)->user->username : 'Empty-21'}}</a></div>
                                                </div>
                                            @endif
                                                
                                            @if($member->position == 2)
                                                <div class="four-circle2">
                                            @endif
                                            
                                            @if($member->position == 2)
                                                <div><a href="{{$circle->circle_member(10)->user ? url('show-customer',$circle->circle_member(10)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(10)->user ? $circle->circle_member(10)->user->username : 'Empty-10'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 3)
                                                <div><a href="{{$circle->circle_member(15)->user ? url('show-customer',$circle->circle_member(15)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(15)->user ? $circle->circle_member(15)->user->username : 'Empty-15'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 4)
                                                <div><a href="{{$circle->circle_member(20)->user ? url('show-customer',$circle->circle_member(20)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(20)->user ? $circle->circle_member(20)->user->username : 'Empty-20'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 5)
                                                <div><a href="{{$circle->circle_member(5)->user ? url('show-customer',$circle->circle_member(5)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(5)->user ? $circle->circle_member(5)->user->username : 'Empty-5'}}</a></div>
                                            @endif
    
                                            @if($member->position == 5)
                                                </div>
                                                <div class="four-circle3">
    
                                            @endif
                                            
                                            @if($member->position == 6)
                                                <div><a href="{{$circle->circle_member(7)->user ? url('show-customer',$circle->circle_member(7)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(7)->user ? $circle->circle_member(7)->user->username : 'Empty-7'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 7)
                                                <div><a href="{{$circle->circle_member(8)->user ? url('show-customer',$circle->circle_member(8)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(8)->user ? $circle->circle_member(8)->user->username : 'Empty-8'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 8)
                                                <div><a href="{{$circle->circle_member(9)->user ? url('show-customer',$circle->circle_member(9)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(9)->user ? $circle->circle_member(9)->user->username : 'Empty-9'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 9)
                                                <div><a href="{{$circle->circle_member(11)->user ? url('show-customer',$circle->circle_member(11)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(11)->user ? $circle->circle_member(11)->user->username : 'Empty-11'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 10)
                                                <div><a href="{{$circle->circle_member(12)->user ? url('show-customer',$circle->circle_member(12)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(12)->user ? $circle->circle_member(12)->user->username : 'Empty-12'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 11)
                                                <div><a href="{{$circle->circle_member(13)->user ? url('show-customer',$circle->circle_member(13)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(13)->user ? $circle->circle_member(13)->user->username : 'Empty-13'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 12)
                                                <div><a href="{{$circle->circle_member(14)->user ? url('show-customer',$circle->circle_member(14)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(14)->user ? $circle->circle_member(14)->user->username : 'Empty-14'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 13)
                                                <div><a href="{{$circle->circle_member(16)->user ? url('show-customer',$circle->circle_member(16)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(16)->user ? $circle->circle_member(16)->user->username : 'Empty-16'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 14)
                                                <div><a href="{{$circle->circle_member(17)->user ? url('show-customer',$circle->circle_member(17)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(17)->user ? $circle->circle_member(17)->user->username : 'Empty-17'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 15)
                                                <div><a href="{{$circle->circle_member(18)->user ? url('show-customer',$circle->circle_member(18)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(18)->user ? $circle->circle_member(18)->user->username : 'Empty-18'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 16)
                                                <div><a href="{{$circle->circle_member(19)->user ? url('show-customer',$circle->circle_member(19)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(19)->user ? $circle->circle_member(19)->user->username : 'Empty-19'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 17)
                                                <div><a href="{{$circle->circle_member(1)->user ? url('show-customer',$circle->circle_member(1)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(1)->user ? $circle->circle_member(1)->user->username : 'Empty-1'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 18)
                                                <div><a href="{{$circle->circle_member(2)->user ? url('show-customer',$circle->circle_member(2)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(2)->user ? $circle->circle_member(2)->user->username : 'Empty-2'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 19)
                                                <div><a href="{{$circle->circle_member(3)->user ? url('show-customer',$circle->circle_member(3)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(3)->user ? $circle->circle_member(3)->user->username : 'Empty-3'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 20)
                                                <div><a href="{{$circle->circle_member(4)->user ? url('show-customer',$circle->circle_member(4)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(4)->user ? $circle->circle_member(4)->user->username : 'Empty-4'}}</a></div>
                                            @endif
                                            
                                            @if($member->position == 21)
                                                <div><a href="{{$circle->circle_member(6)->user ? url('show-customer',$circle->circle_member(6)->user->id) : '#'}}" target="_blank">{{$circle->circle_member(6)->user ? $circle->circle_member(6)->user->username : 'Empty-6'}}</a></div>
                                            @endif
                                            
                                            
                                            
                                            @if($member->position == 21)
                                                </div>
                                            @endif
                                        @endif
                                        
                                    @empty
                                    @endforelse
                                    @endif
                                    
                                    </div>
                                    <p><strong>Code:</strong> #{{$circle->name}}</p>
                                    <h3>{{$circle->package->name}}</h3>
                                    <p><strong>Status:</strong> {{$circle->status}}</p>
                                    @if(!$is_5_member)
                                    <p>Time Left: 
                                    @php
                                        $timer = App\Models\Timer::where('user_id',$customer->id)->where('package_id',$circle->package_id)->first();

                                        $purchased_at = $timer->started_at;

                                        $expiresAt = $purchased_at->copy()->addDays(120);
                                        $now = now();

                                        if ($now->gt($expiresAt)) {
                                            echo "Expired";
                                        } else {
                                            $diffInSeconds = $now->diffInSeconds($expiresAt);
                                            $days = floor($diffInSeconds / 86400);
                                            $hours = floor(($diffInSeconds % 86400) / 3600);
                                            $minutes = floor(($diffInSeconds % 3600) / 60);

                                            echo "{$days} days {$hours} hours {$minutes} min";
                                        }
                                    @endphp
                                    </p>
                                    @else
                                    
                                    @php
                                        $filled_positions = $circle->members->where('status', 'Occupied')->count();
                                        $empty_positions = 5 - $filled_positions;
                                    @endphp
                                    <p><strong>Progress:</strong> {{$filled_positions}}/5 positions filled ({{$empty_positions}} remaining)</p>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                               <th>S No</th>
                                               <th>Circle</th>
                                               <th>Section</th>
                                               <th>Reward Amount</th>
                                               <th>Desc</th>
                                               <th>Status</th>
                                               <th>Time</th>
                                            </thead>
                                            <tbody>
                                                <?php $i = 0; ?>
                                                @forelse($customer->circle_rewards($circle->package_id) as $reward)
                                                @if($reward->user_id == $customer->id)
                                                <?php 
                                                    $i++; 
                                                    $base_reward = $reward->amount;
                                                    $bonus = ($base_reward * 10) / 100;
                                                    $total_reward = $base_reward + $bonus;
                                                ?>
                                                <tr>
                                                    <td>{{$i}}</td>
                                                    <th>#{{$reward->circle->name}}</th>
                                                    <td>{{$reward->section}}</td>
                                                    <td>
                                                        <strong>Total: ₹{{number_format($total_reward, 2)}}</strong><br>
                                                        <small style="color: #666;">
                                                            Base: ₹{{number_format($base_reward, 2)}} + 
                                                            10% Bonus: ₹{{number_format($bonus, 2)}}
                                                        </small>
                                                    </td>
                                                    <td>{{$reward->desc}}</td>
                                                    <td>{{$reward->status}}</td>
                                                    <td>{{$reward->created_at}}</td>
                                                </tr>
                                                @endif
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @forelse($circle->rewards as $reward)
                                    
                                    @empty
                                    @endforelse
                                    <hr>
                                </center>
                                
                        <!--    </div>-->
                        <!--</div>-->
                    @empty
                  
                    @endforelse
                  </div>
                  
                  <div class="tab-pane" id="trips">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal">Add New Trip</button>
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>Tour</th>
                           <th>Members</th>
                           <th>From Date</th>
                           <th>To Date</th>
                           <th>From Place</th>
                           <th>Status</th>
                           <!-- <th>Action</th> -->
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($customer->trips as $trip)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>
                                    <a href="{{ $trip->tour ? route('tour.show', $trip->tour_id) : '#' }}" target="_blank">{{ $trip->tour ? $trip->tour->name : 'N/A' }}</a>
                                </td>
                                <td>{{$trip->members}}</td>
                                <td>{{$trip->from_date}}</td>
                                <td>{{$trip->to_date}}</td>
                                <td>{{$trip->from_place}}</td>
                                <td>{{$trip->status}}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" data-id="{{$trip->id}}" data-tour="{{$trip->tour_id}}" data-members="{{$trip->members}}"
                                    data-from_date="{{$trip->from_date}}" data-to_date="{{$trip->to_date}}" data-from_place="{{$trip->from_place}}" data-status="{{$trip->status}}"><i class="fa fa-edit"></i></button>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  </div>
                  
                  <div class="tab-pane" id="transactions">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                           <th>S No</th>
                           <th>Type</th>
                           <th>Reason</th>
                           <th>Amount</th>
                           <th>Balance</th>
                           <th>Date</th>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @forelse($customer->transactions as $transaction)
                            <?php $i++; ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$transaction->type}}</td>
                                <td>{{$transaction->reason}}</td>
                                <td>{{$transaction->amount}}</td>
                                <td>{{$transaction->balance}}</td>
                                <td>{{$transaction->created_at}}</td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  </div>
                  
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
    
    
    <!-- Add Modal -->

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Trip</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('trip.store')}}" method="post" class="add-form">
        @csrf
        <div class="modal-body">
          <div class="error"></div>
          <div class="form-group">
            <label for="members" class="col-form-label">Select Tour:</label>
            <select name="tour" id="tour" class="form-control" required>
                @forelse($tours as $tour)
                <option value="{{$tour->id}}">{{$tour->name}} - {{$tour->place}} at Rs {{$tour->price}}</option>
                @empty
                @endforelse
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
          <input type="hidden" name="user_id" id="user-id" value="{{$customer->id}}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="save-button">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
    
    <style>
    .two-circle-container {
      position: relative;
      width: 340px;
      height: 340px;
    }
    .two-circle1 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100px;
      height: 100px;
      background-color: brown;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      z-index: 3; /* Ensures circle1 is on top */
      border: 5px solid white;
    }

    .two-circle2 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: conic-gradient(
        purple 0% 25%,
        skyblue 25% 75%,
        purple 75% 100%
      );
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      z-index: 2; /* Ensures circle2 is below circle1 */
      border: 5px solid white;
    }

    .two-circle2::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }

    .two-circle2::before {
      transform: translate(-50%, -50%) rotate(0deg);
    }

    .two-circle2 > div {
      position: absolute;
      width: 50%;
      text-align: center;
    }

    .two-circle2 > div:nth-child(1) {
      top: 5%;
      right: 25%;
      transform: rotate(0deg);
    }

    .two-circle2 > div:nth-child(2) {
      right: 23%;
      transform: rotate(0deg);
      bottom: 10%;
    }

    .two-circle3 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 300px;
      height: 300PX;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: conic-gradient(
        purple 0% 25%,
        skyblue 25% 75%,
        purple 75% 100%,
        skyblue 75% 100%
      );
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      z-index: 1; /* Ensures circle3 is below circle2 */
    }

    .two-circle3 > div {
      position: absolute;
      width: 50%;
    }

    .two-circle3 > div:nth-child(1) {
      top: 18%;
      right: -5%;
      transform: rotate(47deg);
    }

    .two-circle3 > div:nth-child(2) {
      bottom: 18%;
      transform: rotate(-47deg);
      right: -5%;
    }

    .two-circle3 > div:nth-child(3) {
      top: 18%;
      transform: rotate(-47deg);
      left: -5%;
    }

    .two-circle3 > div:nth-child(4) {
      bottom: 18%;
      transform: rotate(47deg);
      left: -5%;
    }

    .two-circle3::before, .two-circle3::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }

    .two-circle3::before {
      transform: translate(-50%, -50%) rotate(0deg);
    }

    .two-circle3::after {
      transform: translate(-50%, -50%) rotate(90deg);
    }
    
    
    
    .three-circle-container {
      position: relative;
      width: 470px;
      height: 470px;
      margin-top: 50px;
    }
    .three-circle1 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 120px;
      height: 120px;
      background-color: brown;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      z-index: 3; /* Ensures circle1 is on top */
      border: 6px solid white;
    }

    .three-circle1::before {
      content: '';
      position: absolute;
      bottom: 10%;
      left: -142%;
      width: 143%;
      height: 6px;
      background-color: white;
      transform: rotate(163deg);
    }

    .three-circle2 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: conic-gradient(
        purple 0% 33%,
        red 33% 70%,
        skyblue 70% 100%
      );
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      z-index: 2; /* Ensures circle2 is below circle1 */
      border: 5px solid white;
    }

    .three-circle2::before {
      content: '';
      position: absolute;
      top: -6%;
      left: 19%;
      width: 63%;
      height: 6px;
      background-color: white;
      transform: rotate(90deg);
    }
    .three-circle2::after {
      content: '';
      position: absolute;
      bottom: 23%;
      left: 67%;
      width: 62%;
      height: 6px;
      background-color: white;
      transform: rotate(-152deg);
    }

    .three-circle2 > div:nth-child(1) {
      top: 24%;
      right: 12%;
      transform: rotate(45deg);
      position: absolute;
    }
    .three-circle2 > div:nth-child(2) {
      bottom: 10%;
      right: 42%;
      transform: rotate(0deg);
      position: absolute;
    }
    .three-circle2 > div:nth-child(3) {
      top: 24%;
      left: 12%;
      transform: rotate(-52deg);
      position: absolute;
    }

    .three-circle3 {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 440px;
    height: 440px;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    background: conic-gradient(
      purple 0% 33%,
      red 33% 70%,
      skyblue 70% 100%
    );
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: white;
    z-index: 1; /* Ensures circle3 is below circle2 */
    border: 6px solid white;
  }

  .three-circle3 > div {
    position: absolute;
    transform: translate(-50%, -50%);
    color: white;
    min-width:55px;
  }

  /* Adjust the positioning and rotation for each div */
  .three-circle3 > div:nth-child(1) {
    top: 8%;
    right: 20%;
    transform: translate(-50%, -50%) rotate(25deg);
  }

  .three-circle3 > div:nth-child(2) {
    top: 27%;
    right: -1%;
    transform: translate(-50%, -50%) rotate(56deg);
  }

  .three-circle3 > div:nth-child(3) {
    top: 56%;
    right: -7%;
    transform: translate(-50%, -50%) rotate(98deg);
  }

  .three-circle3 > div:nth-child(4) {
    bottom: 10%;
    right: 10%;
    transform: translate(-50%, -50%) rotate(-36deg);
  }

  .three-circle3 > div:nth-child(5) {
    bottom: 2%;
    right: 38%;
    transform: translate(-50%, -50%) rotate(4deg);
  }

  .three-circle3 > div:nth-child(6) {
    bottom: 18%;
    right: 69%;
    transform: translate(-50%, -50%) rotate(52deg);
  }
  .three-circle3 > div:nth-child(7) {
    bottom: 44%;
    left: 8%;
    transform: translate(-50%, -50%) rotate(-86deg);
  }
  .three-circle3 > div:nth-child(8) {
    top: 29%;
    right: 73%;
    transform: translate(-50%, -50%) rotate(-62deg);
  }
  .three-circle3 > div:nth-child(9) {
    top: 7%;
    right: 51%;
    transform: translate(-50%, -50%) rotate(-18deg);
  }
  .three-circle3 > div:nth-child(10) {
    bottom: 4%;
    right: 46%;
    transform: translate(-50%, -50%) rotate(4deg);
  }
  .three-circle3 > div:nth-child(11) {
    bottom: 4%;
    right: 46%;
    transform: translate(-50%, -50%) rotate(4deg);
  }
  .three-circle3 > div:nth-child(12) {
    bottom: 4%;
    right: 46%;
    transform: translate(-50%, -50%) rotate(4deg);
  }

  /* Example ::after for div:nth-child(1) */
  .three-circle3 > div:nth-child(1)::after {
    content: '';
    position: absolute;
    top: 151%;
    left: 58%;
    transform: rotate(101deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }

  /* Example ::after for div:nth-child(4) */
  .three-circle3 > div:nth-child(2)::after {
    content: '';
    position: absolute;
    top: 154%;
    left: 66%;
    transform: rotate(109deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }

  .three-circle3 > div:nth-child(4)::after {
    content: '';
    position: absolute;
    bottom: 130%;
    left: -129%;
    transform: rotate(102deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }

  .three-circle3 > div:nth-child(5)::after {
    content: '';
    position: absolute;
    bottom: 156%;
    left: -136%;
    transform: rotate(112deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }

  .three-circle3 > div:nth-child(7)::after {
    content: '';
    position: absolute;
    bottom: -10%;
    left: 76%;
    transform: rotate(108deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }

  .three-circle3 > div:nth-child(8)::after {
    content: '';
    position: absolute;
    bottom: -46%;
    left: 85%;
    transform: rotate(108deg);
    width: 86px;
    height: 4px;
    background-color: white;
  }


  
  .four-circle-container {
      position: relative;
      width: 560px;
      height: 560px;
      margin-top: 50px;
      
    }
    
  .four-circle1 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 130px;
      height: 130px;
      background-color: brown;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      z-index: 3; /* Ensures circle1 is on top */
      border: 6px solid white;
    }

    .four-circle2 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 320px;
      height: 320px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: conic-gradient(
        purple 0% 25%,
        red 25% 50%,
        green 50% 75%,
        skyblue 75% 100%
      );
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      z-index: 2; /* Ensures circle2 is below circle1 */
      border: 6px solid white;
    }

    .four-circle2::before, .four-circle2::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 6px;
      background-color: white;
      transform-origin: center;
    }

    .four-circle2::before {
      transform: translate(-50%, -50%) rotate(0deg);
    }

    .four-circle2::after {
      transform: translate(-50%, -50%) rotate(90deg);
    }

    .four-circle2 > div:nth-child(1) {
      right: 12%;
      transform: rotate(48deg);
      top: 17%;
      position: absolute;
    }

    .four-circle2 > div:nth-child(2) {
      right: 12%;
      transform: rotate(-40deg);
      bottom: 19%;
      position: absolute;
    }

    .four-circle2 > div:nth-child(3) {
      top: 69%;
      transform: rotate(45deg);
      left: 14%;
      position: absolute;
    }

    .four-circle2 > div:nth-child(4) {
      left: 10%;
      transform: rotate(-45deg);
      top: 20%;
      position: absolute;
    }

    .four-circle3 {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 520px;
      height: 520px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: conic-gradient(
        purple 0% 25%,
        red 25% 50%,
        green 50% 75%,
        skyblue 75% 100%
      );
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      z-index: 1; /* Ensures circle3 is below circle2 */
    }

    .four-circle3 > div {
      position: absolute;
      width: 50%;
    }

    .four-circle3 > div:nth-child(1) {
      top: 8%;
      right: 0%;
      transform: rotate(35deg);
    }

    .four-circle3 > div:nth-child(2) {
      right: -13%;
      transform: rotate(57deg);
      top: 20%;
    }

    .four-circle3 > div:nth-child(3) {
      top: 38%;
      transform: rotate(76deg);
      right: -20%;
    }

    .four-circle3 > div:nth-child(4) {
      top: 57%;
      transform: rotate(-76deg);
      right: -20%;
    }

    .four-circle3 > div:nth-child(5) {
      top: 71%;
      right: -14%;
      transform: rotate(-60deg);
    }

    .four-circle3 > div:nth-child(6) {
      right: -2%;
      top: 84%;
      transform: rotate(-40deg);
    }

    .four-circle3 > div:nth-child(7) {
      bottom: 3%;
      right: 15%;
      transform: rotate(-12deg);
    }

    .four-circle3 > div:nth-child(8) {
     bottom: 2%;
      right: 34%;
      transform: rotate(15deg);
    }

    .four-circle3 > div:nth-child(9) {
        bottom: 9%;
        right: 50%;
        transform: rotate(35deg);
    }

    .four-circle3 > div:nth-child(10) {
      bottom: 22%;
      left: -14%;
      transform: rotate(56deg);
    }

    .four-circle3 > div:nth-child(11) {
      bottom: 39%;
      left: -20%;
      transform: rotate(78deg);
    }

    .four-circle3 > div:nth-child(12) {
      bottom: 57%;
      left: -21%;
      transform: rotate(-83deg);
    }
    .four-circle3 > div:nth-child(13) {
      top: 22%;
      left: -14%;
      transform: rotate(-60deg);
    }

    .four-circle3 > div:nth-child(14) {
      top: 10%;
      left: -3%;
      transform: rotate(-40deg);
    }

    .four-circle3 > div:nth-child(15) {
      top: 2%;
      left: 13%;
      transform: rotate(-16deg);
    }

    .four-circle3 > div:nth-child(16) {
      top: 2%;
      left: 35%;
      transform: rotate(14deg);
    }


    .four-circle3::before, .four-circle3::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 6px;
      background-color: white;
      transform-origin: center;
    }

    .four-circle3::before {
      transform: translate(-50%, -50%) rotate(0deg);
    }

    .four-circle3::after {
      transform: translate(-50%, -50%) rotate(90deg);
    }
    .four-circle3 > div:nth-child(1)::after {
      top: 204%;
      right: 42%;
      transform: rotate(79deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }

    .four-circle3 > div:nth-child(2)::after {
      top: 211%;
      right: 40%;
      transform: rotate(79deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(3)::after {
      top: 178%;
      right: 45%;
      transform: rotate(80deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(4)::after {
      top: -88%;
      right: 43%;
      transform: rotate(-82deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(5)::after {
      bottom: 176%;
      right: 47%;
      transform: rotate(102deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(6)::after {
      bottom: 179%;
      right: 47%;
      transform: rotate(104deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    /*.four-circle3 > div:nth-child(7)::after {*/
    /*  bottom: 373%;*/
    /*  right: 131%;*/
    /*  transform: rotate(141deg);*/
    /*  content: '';*/
    /*  position: absolute;*/
    /*  width: 52%;*/
    /*  height: 4px;*/
    /*  background-color: white;*/
    /*  transform-origin: center;*/
    /*}*/
    .four-circle3 > div:nth-child(8)::after {
      bottom: 188%;
      right: 47%;
      transform: rotate(102deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(9)::after {
      bottom: 178%;
      right: 48%;
      transform: rotate(100deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(10)::after {
      bottom: 200%;
      right: 46%;
      transform: rotate(106deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    .four-circle3 > div:nth-child(12)::after {
      bottom: -126%;
      right: 18%;
      transform: rotate(104deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }

    .four-circle3 > div:nth-child(13)::after {
      bottom: -120%;
      right: 17%;
      transform: rotate(106deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    
    .four-circle3 > div:nth-child(14)::after {
      bottom: -135%;
      right: 12%;
      transform: rotate(103deg);
      content: '';
      position: absolute;
      width: 40%;
      height: 4px;
      background-color: white;
      transform-origin: center;
    }
    
    .circle a{
        color:white;
        
    }

    /* 5-Member Circle Styles (Simple Circle) - Circular Layout like other circles */
    .five-circle-container {
        position: relative;
        width: 340px;
        height: 340px;
    }
    
    .five-circle1 {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 120px;
        height: 120px;
        background-color: #f5576c;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        z-index: 3;
        border: 6px solid #ffd700;
        box-shadow: 0 4px 20px rgba(255,215,0,0.4);
    }
    
    .five-circle2 {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        background: conic-gradient(
          purple 0% 25%,
        red 25% 50%,
        green 50% 75%,
        skyblue 75% 100%
        );
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        color: white;
        z-index: 2;
        border: 5px solid white;
    }
    
    .five-circle2::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 4px;
        background-color: white;
        transform-origin: center;
        transform: translate(-50%, -50%) rotate(0deg);
    }
    
    .five-circle2::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 4px;
        background-color: white;
        transform-origin: center;
        transform: translate(-50%, -50%) rotate(90deg);
    }
    
    .five-circle2 > div {
        position: absolute;
        width: 50%;
        text-align: center;
        color: white;
    }
    
    .five-circle2 > div:nth-child(1) {
        top: 5%;
        right: 25%;
        transform: rotate(-49deg) translate(-80px, -23px);
    }
    
    .five-circle2 > div:nth-child(2) {
        right: 5%;
        bottom: 25%;
        transform: rotate(52deg) translate(-88px, -82px);
    }
    
    .five-circle2 > div:nth-child(3) {
        bottom: 5%;
        left: 25%;
        transform: rotate(-41deg) translate(78px, 8.8px);
    }
    
    .five-circle2 > div:nth-child(4) {
        left: 5%;
        top: 25%;
        transform: rotate(44deg) translate(71px, 94px);
    }

    
  </style>

@section('script')
<script>
    $(document).ready(function(){
        var id = '';
        var action = '';
        var token = "{{csrf_token()}}";
        
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
        
        $('.document-status').bootstrapSwitch('state');
        $('.document-status').on('switchChange.bootstrapSwitch',function () {
            var id = $(this).data('id');
            $.ajax({
                url : "{{url('update-document-status')}}",
                type: "post",
                data : {'_token':token,'id':id,},
                success: function(data)
                {
                  //
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
        });
    });
</script>
@endsection

@endsection
