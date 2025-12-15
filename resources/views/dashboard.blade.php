@extends('layouts.app')

@section('title', 'Dashboard – Pedagre SIS')

@section('content')
    <h1>Dashboard</h1>
    <p>High-level overview of your student data and DTEF integration.</p>

    <table>
        <thead>
        <tr>
            <th>Metric</th>
            <th>Count</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Institutions</td>
            <td>{{ $stats['institutions'] }}</td>
        </tr>
        <tr>
            <td>Programmes</td>
            <td>{{ $stats['programmes'] }}</td>
        </tr>
        <tr>
            <td>Students</td>
            <td>{{ $stats['students'] }}</td>
        </tr>
        <tr>
            <td>Admissions</td>
            <td>{{ $stats['admissions'] }}</td>
        </tr>
        <tr>
            <td>Registrations</td>
            <td>{{ $stats['registrations'] }}</td>
        </tr>
        <tr>
            <td>Results</td>
            <td>{{ $stats['results'] }}</td>
        </tr>
        </tbody>
    </table>

    <br>

    <a href="{{ route('admissions.index') }}" class="btn btn-primary">View Admissions</a>
    <a href="{{ route('registrations.index') }}" class="btn btn-primary">View Registrations</a>
    <a href="{{ route('results.index') }}" class="btn btn-primary">View Results</a>
@endsection
