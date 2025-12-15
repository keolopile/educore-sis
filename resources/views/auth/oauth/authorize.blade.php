@extends('layouts.app') {{-- or use a minimal HTML layout if you prefer --}}

@section('title', 'Authorize application')

@section('content')
    <div class="container mt-5">
        <h1 class="h4 mb-3">Authorize application</h1>

        <p class="mb-3">
            The application <strong>{{ $client->name ?? 'Educore CPD' }}</strong>
            is requesting access to your account.
        </p>

        @if (! empty($scopes))
            <p>This will allow the application to:</p>
            <ul>
                @foreach ($scopes as $scope)
                    <li>{{ $scope->description }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="d-inline">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="btn btn-success">Authorize</button>
        </form>

        <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="d-inline ms-2">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="btn btn-outline-secondary">Cancel</button>
        </form>
    </div>
@endsection
