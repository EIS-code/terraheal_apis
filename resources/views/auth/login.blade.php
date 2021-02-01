@extends('layouts.admin-login')

@section('content')
<div class="h-100 bg-plum-plate bg-animation">
    <div class="d-flex h-100 justify-content-center align-items-center">
        <div class="mx-auto app-login-box col-md-8">
            <div class="app-logo-inverse mx-auto mb-3"></div>
            <div class="modal-dialog w-100 mx-auto">
                <form method="POST" action="{{ route('login') }}">
                @csrf
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="h5 modal-title text-center">
                                <h4 class="mt-2">
                                    <div>Welcome,</div>
                                    <span>Please sign in to your account below.</span>
                                </h4>
                            </div>
                            
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <div class="position-relative form-group">
                                            <input id="email" type="email" placeholder="Email here..." class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                            @error('email')
                                                <em class="error invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </em>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="position-relative form-group">
                                            <input id="password" type="password" placeholder="Password here..." class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                            @error('password')
                                                <em class="error invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </em>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="position-relative form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="exampleCheck" class="form-check-label">Keep me logged in</label>
                                </div>
                            <!-- <div class="divider"></div> -->
                            <!-- <h6 class="mb-0">No account? <a href="javascript:void(0);" class="text-primary">Sign up now</a></h6> -->
                        </div>
                        <div class="modal-footer clearfix">
                            <!-- <div class="float-left">
                                <a href="javascript:void(0);" class="btn-lg btn btn-link">Recover Password</a>
                            </div> -->
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Login to Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="text-center text-white opacity-8 mt-3">Copyright © UONDA 2020</div>
        </div>
    </div>
</div>
@endsection
