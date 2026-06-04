@extends('layouts.admin')

@section('title')
    KYC Submission #{{ $submission->id }}
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>KYC Submission #{{ $submission->id }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('admin/kyc') }}">KYC Submissions</a></li>
                        <li class="breadcrumb-item active">#{{ $submission->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            @if($submission->user)
                                <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle"
                                         src="{{ $submission->user->photo }}"
                                         alt="User profile picture">
                                </div>
                                <h3 class="profile-username text-center">{{ $submission->user->name }}</h3>
                                <p class="text-muted text-center">{{ $submission->user->role }}</p>
                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                        <b>User ID</b> <a class="float-right">{{ $submission->user->id }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Username</b> <a class="float-right">{{ $submission->user->username }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Email</b> <a class="float-right">{{ $submission->user->email }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Phone</b> <a class="float-right">{{ $submission->user->phone }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Referral</b> <a class="float-right">{{ $submission->user->referal_code }}</a>
                                    </li>
                                </ul>
                                <a href="{{ url('show-customer/'.$submission->user->id) }}" target="_blank" class="btn btn-primary btn-block"><b>View Customer</b></a>
                            @else
                                <p class="text-center text-muted">User not found</p>
                            @endif
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Submission</h3>
                        </div>
                        <div class="card-body">
                            <p><b>Email Status:</b>
                                @if($submission->email_status === 'sent')
                                    <span class="badge badge-success">Sent</span>
                                @elseif($submission->email_status === 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </p>
                            <p><b>Email Retry Count:</b> {{ $submission->email_retry_count }}</p>
                            @if($submission->email_error)
                                <p><b>Last Error:</b> <span class="text-danger small">{{ $submission->email_error }}</span></p>
                            @endif
                            <p><b>Submitted At:</b> {{ optional($submission->submitted_at ?? $submission->created_at)->format('d M Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documents</h3>
                        </div>
                        <div class="card-body">
                            @if($files->isEmpty())
                                <p class="text-muted">No files for this submission.</p>
                            @endif
                            <div class="row">
                                @foreach($files as $file)
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-outline card-secondary h-100">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ str_replace('_', ' ', ucfirst($file['field'])) }}</h3>
                                            </div>
                                            <div class="card-body text-center">
                                                @if($file['exists'])
                                                    @if(in_array($file['mime'], ['image/jpeg','image/png']))
                                                        <a href="{{ $file['view_url'] }}" target="_blank">
                                                            <img src="{{ $file['view_url'] }}" alt="{{ $file['original_name'] }}" class="img-fluid img-thumbnail" style="max-height: 280px;">
                                                        </a>
                                                    @elseif($file['mime'] === 'application/pdf')
                                                        <i class="fa fa-file-pdf fa-4x text-danger"></i>
                                                        <p class="mt-2"><a href="{{ $file['view_url'] }}" target="_blank">Open PDF</a></p>
                                                    @else
                                                        <i class="fa fa-file fa-4x text-secondary"></i>
                                                    @endif
                                                    <p class="mt-2 small text-muted">{{ $file['original_name'] }}</p>
                                                    <p class="small text-muted">{{ number_format($file['size'] / 1024, 1) }} KB</p>
                                                @else
                                                    <p class="text-danger">File missing on server</p>
                                                @endif
                                            </div>
                                            @if($file['exists'])
                                                <div class="card-footer text-center">
                                                    <a href="{{ $file['view_url'] }}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> View</a>
                                                    <a href="{{ $file['download_url'] }}" class="btn btn-sm btn-success"><i class="fa fa-download"></i> Download</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
