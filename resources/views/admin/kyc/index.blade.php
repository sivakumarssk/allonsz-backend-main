@extends('layouts.admin')

@section('title')
    KYC Submissions
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>KYC Submissions</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">KYC Submissions</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>S No</th>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Referral</th>
                                            <th>Files</th>
                                            <th>Email Status</th>
                                            <th>Submitted</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 0; @endphp
                                        @forelse($submissions as $submission)
                                            @php $i++; @endphp
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>{{ $submission->id }}</td>
                                                <td>{{ $submission->user ? $submission->user->name : 'N/A' }}</td>
                                                <td>{{ $submission->user ? $submission->user->email : '' }}</td>
                                                <td>{{ $submission->user ? $submission->user->phone : '' }}</td>
                                                <td>{{ $submission->user ? $submission->user->referal_code : '' }}</td>
                                                <td>{{ is_array($submission->file_paths) ? count($submission->file_paths) : 0 }}</td>
                                                <td>
                                                    @if($submission->email_status === 'sent')
                                                        <span class="badge badge-success">Sent</span>
                                                    @elseif($submission->email_status === 'failed')
                                                        <span class="badge badge-danger">Failed</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ optional($submission->submitted_at ?? $submission->created_at)->format('d M Y h:i A') }}</td>
                                                <td>
                                                    <a href="{{ url('admin/kyc/'.$submission->id) }}" target="_blank" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No KYC submissions yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
