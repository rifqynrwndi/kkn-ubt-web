@extends('layouts.app')

@section('title', 'Table Example')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Table Example</h1>
        </div>

        <div class="section-body">
            <h2 class="section-title">Table Example</h2>
            <p class="section-lead">
                This page is an example of using a table.
            </p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Rifqy Nirwandi</td>
                            <td>rifqy@example.com</td>
                            <td>Admin</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>John Doe</td>
                            <td>johndoe@example.com</td>
                            <td>User</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
@endpush
