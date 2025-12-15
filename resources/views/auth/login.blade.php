<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CPD Login</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <strong>CPD Login</strong>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email used for CPD registration</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   class="form-control"
                                   required
                                   autofocus>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Continue
                        </button>
                    </form>
                </div>
                <div class="card-footer text-muted small text-center">
                    Temporary login for testing Moodle SSO
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
