@extends('layouts.app')

@section('content')
<table class="table table-striped table-dark" id="galleryMons">
    <thead>
        <tr>
            <th>Sprite</th>
            <th>Name</th>
            <th>Primary Type</th>
            <th>Secondary Type</th>
            <th>BST</th>
            <th>View</th>
        </tr>
    </thead>
    <tbody>
        @foreach($glitches as $glitch)
            <tr>
                <td>
                    <img src="/cFront:{{ $glitch->name }}.png"
                        class="rounded-circle img-fluid" style="width: 64px;">
                </td>
                <td>{{ ucwords($glitch->name) }}</td>
                <td><img src="/img/types/{{ $glitch->type1 }}.png"></td>
                <td>
                    @if(!empty($glitch->type2))
                        <img src="/img/types/{{ $glitch->type2 }}.png">
                    @else
                        None
                    @endif
                </td>
                <td>{{ $glitch->bst }}</td>
                <td>
                    <a href="/smitty:{{ $glitch->name }}.html"
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
