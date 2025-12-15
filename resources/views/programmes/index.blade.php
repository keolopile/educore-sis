@extends('layouts.app')

@section('title', 'Programmes – Pedagre SIS')

@section('content')
    <h1>Programmes</h1>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Institution</th>
            <th>Code</th>
            <th>Name</th>
            <th>Level</th>
            <th>Duration (years)</th>
        </tr>
        </thead>
        <tbody>
        @forelse($programmes as $prog)
            <tr>
                <td>{{ $prog->id }}</td>
                <td>{{ $prog->institution->short_code ?? '' }}</td>
                <td>{{ $prog->code }}</td>
                <td>{{ $prog->name }}</td>
                <td>{{ $prog->level }}</td>
                <td>{{ $prog->duration_years }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">No programmes yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
