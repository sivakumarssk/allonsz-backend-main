@extends('layouts.admin')


@section('title')
    Users with Expiring Timers
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-12">
                @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
                @endif
            </div>
          <div class="col-sm-6">
            <h1>Users with Expiring Timers</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Expiring Timers</li>
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
                <h3 class="card-title">Users with timers expiring in 30 days or less</h3>
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
                    <th>Package</th>
                    <th>Started At</th>
                    <th>Expires At</th>
                    <th>Days Remaining</th>
                    <th>Status</th>
                  </tr>
                  </thead>
                  <tbody>

                    <?php $i = 0; ?>
                  @forelse($users_with_expiring_timers as $item)
                  <?php $i++; ?>
                  <tr class="{{ $item['is_expired'] ? 'table-danger' : ($item['days_remaining'] <= 7 ? 'table-warning' : '') }}">
                    <td>{{$i}}</td>
                    <td>{{$item['user']->name}}</td>
                    <td>{{$item['user']->username}}</td>
                    <td>{{$item['user']->email}}</td>
                    <td>{{$item['user']->phone}}</td>
                    <td>{{$item['user']->referal_code}}</td>
                    <td>{{$item['package'] ? $item['package']->name : 'N/A'}}</td>
                    <td>{{$item['started_at']->format('d M Y, h:i A')}}</td>
                    <td>{{$item['expires_at']->format('d M Y, h:i A')}}</td>
                    <td>
                        @if($item['is_expired'])
                            <span class="badge badge-danger">EXPIRED</span>
                        @elseif($item['days_remaining'] <= 7)
                            <span class="badge badge-warning">{{$item['days_remaining']}} days</span>
                        @else
                            <span class="badge badge-info">{{$item['days_remaining']}} days</span>
                        @endif
                    </td>
                    <td>
                        @if($item['is_expired'])
                            <span class="badge badge-danger">Expired</span>
                        @elseif($item['days_remaining'] <= 7)
                            <span class="badge badge-warning">Critical</span>
                        @else
                            <span class="badge badge-info">Expiring Soon</span>
                        @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="11" class="text-center">No users with expiring timers found</td>
                  </tr>
                  @endforelse
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


@section('script')
{{-- DataTable is already initialized in admin layout (layouts/admin.blade.php line 489) for all .table elements --}}
{{-- No additional initialization needed here to avoid "Cannot reinitialise DataTable" error --}}
@endsection

@endsection
