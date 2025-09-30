@extends('layout')

@section('title', "Cooperative - {$coop->name}")

@section('content')
    <div class="container mt-4">
        <h2>{{ $coop->name }} ({{ ucfirst($coop->type) }})</h2>

        <a href="{{ route('cooperative.show', $coop->id) }}" class="btn btn-primary">
                                View
                            </a>

        <table class="table table-bordered mt-3">
            <tr>
                <th>ID</th>
                <td>{{ $coop->id }}</td>
            </tr>
            <tr>
                <th>Holder</th>
                <td>{{ optional($coop->holderRelation)->name ?? 'None' }}</td>
            </tr>

            {{-- CoopDetails fields --}}
            @if ($coop->details)
                <tr>
                    <th>Municipality ID</th>
                    <td>{{ $coop->details->municipality_id }}</td>
                </tr>
                <tr>
                    <th>Asset Size</th>
                    <td>{{ $coop->details->asset_size }}</td>
                </tr>
                <tr>
                    <th>Coop Type</th>
                    <td>{{ $coop->details->coop_type }}</td>
                </tr>
                <tr>
                    <th>Status/Category</th>
                    <td>{{ $coop->details->status_or_category }}</td>
                </tr>
                <tr>
                    <th>Bond of Membbershipt</th>
                    <td>{{ $coop->details->bond_of_membership }}</td>
                </tr>
                <tr>
                    <th>Area of operations</th>
                    <td>{{ $coop->details->area_of_operation }}</td>
                </tr>
                <tr>
                    <th>Citizenship</th>
                    <td>{{ $coop->details->citizenship }}</td>
                </tr>
                <tr>
                    <th>Members Count</th>
                    <td>{{ $coop->details->members_count }}</td>
                </tr>
                <tr>
                    <th>Total Assets</th>
                    <td>{{ $coop->details->total_asset }}</td>
                </tr>
                <tr>
                    <th>Net Surplus</th>
                    <td>{{ $coop->details->net_surplus }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        No additional details available
                    </td>
                </tr>
            @endif
        </table>

        <a href="{{ route('login.post') }}" class="btn btn-secondary mt-3">⬅ Back to Cooperatives</a>
    </div>
@endsection
