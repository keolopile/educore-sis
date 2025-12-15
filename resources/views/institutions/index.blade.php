@extends('layouts.app')

@section('title', 'Institutions – Pedagre SIS')

@section('content')
    <h1>Institutions</h1>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Short Code</th>
            <th>Name</th>
            <th>Created</th>
        </tr>
        </thead>
        <tbody>
        @forelse($institutions as $inst)
            <tr>
                <td>{{ $inst->id }}</td>
                <td>{{ $inst->short_code }}</td>
                <td>{{ $inst->name }}</td>
                <td>{{ $inst->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No institutions yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
