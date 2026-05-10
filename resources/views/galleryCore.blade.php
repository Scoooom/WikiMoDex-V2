@extends('layouts.app')

@section('content')
<table class="table table-striped table-dark" id="galleryMons">
    <thead>
        <tr>
            <th>Sprite</th>
            <th>Name</th>
            <th>Glitch Of</th>
            <th>Primary Type</th>
            <th>Secondary Type</th>
            <th>View</th>
        </tr>
    </thead>
    <tbody>
        @foreach($glitches as $glitch)
            @php
                $og = explode(',', $glitch->og_mon ?? '');
                $mons = '';
                foreach ($og as $ogMonId) {
                    if (empty(trim($ogMonId))) continue;
                    try {
                        $ogMon = \App\Services\PokemonService::getMon(trim($ogMonId));
                        $mons .= ucwords(str_replace('-', ' ', $ogMon->name)) . ', ';
                    } catch (\Exception $e) {
                        $mons .= 'Unknown, ';
                    }
                }
                $mons = rtrim(trim($mons), ',');
            @endphp
            <tr>
                <td>
                    <img src="/cFront:{{ $glitch->name }}.png"
                        class="rounded-circle img-fluid" style="width: 64px;">
                </td>
                <td>{{ ucwords($glitch->name) }}</td>
                <td>{{ $mons }}</td>
                <td><img src="/img/types/{{ $glitch->type1 }}.png"></td>
                <td>
                    @if(!empty($glitch->type2))
                        <img src="/img/types/{{ $glitch->type2 }}.png">
                    @else
                        None
                    @endif
                </td>
                <td>
                    <a href="/core:{{ $glitch->name }}.html"
                        class="btn btn-primary btn-sm">View</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#galleryMons').DataTable({
            paging: true,
            searching: true,
            order: [[1, 'asc']]
        });
    });
</script>
@endsection
