@extends('layouts.app')

@section('content')
<table class="table table-striped table-dark" id="galleryMons">
    <thead>
        <tr>
            <th>Sprite</th>
            <th>Name</th>
            <th>Rating</th>
            <th>Creator</th>
            <th>Glitch Of</th>
            <th>Primary Type</th>
            <th>Secondary Type</th>
            <th>View</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
        @foreach($glitches as $glitch)
            @php
                $mon2 = $glitch->getJsonData();
                $ogMon = $glitch->getOGMon();
            @endphp
            <tr>
                <td>
                    <img src="/front:{{ $glitch->id }}.png"
                        class="rounded-circle img-fluid" style="width: 64px;">
                </td>
                <td>{{ $glitch->name }}</td>
                <td>{{ $glitch->getRating() }}</td>
                <td>
                    <a href="/u:{{ $glitch->creator->username }}.html">
                        {{ $glitch->creator->username }}
                    </a>
                </td>
                <td>{{ ucwords(str_replace('-', ' ', $ogMon->name)) }}</td>
                <td><img src="/img/types/{{ $mon2->primaryType }}.png"></td>
                <td><img src="/img/types/{{ $mon2->secondaryType }}.png"></td>
                <td>
                    <a href="/g:{{ urlencode(str_replace(' ', '', $glitch->name)) }}:{{ $glitch->id }}.html"
                        class="btn btn-primary btn-sm">View</a>
                </td>
                <td>
                    <a href="/d:{{ $glitch->id }}.html"
                        class="btn btn-secondary btn-sm">Download</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#galleryMons').DataTable({
            paging: true,
            stateSave: false,
            searching: true,
            order: [[2, 'desc']]
        });
    });
</script>
@endsection
