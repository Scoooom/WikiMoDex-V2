@extends('layouts.app')

@section('content')
<section>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->getAvatarURL() }}" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3">{{ $user->username }}</h5>

                    @auth
                    <div class="d-flex justify-content-center mb-2">
                        @if(Auth::user()->likesUser($user->id))
                            <form action="/uRLike:{{ $user->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-success">Remove Like</button>
                            </form>
                        @else
                            <form action="/uLike:{{ $user->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-success">Like</button>
                            </form>
                        @endif
                    </div>

                    @if($isOwner)
                    <div class="d-flex justify-content-center mb-2">
                        <form action="/u:{{ $user->username }}.html" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="action" value="uploadNew">
                            <input type="file" name="saveFile" class="form-control mb-2">
                            <button type="submit" class="btn btn-primary">
                                {{ ($user->b64_prsv && $user->raw_prsv) ? 'Update Save' : 'Upload Save File' }}
                            </button>
                        </form>
                    </div>

                    @if($user->b64_prsv && $user->raw_prsv)
                    <div class="d-flex justify-content-center mb-2">
                        <form action="/u:{{ $user->username }}.html" method="post" class="mr-2">
                            @csrf
                            <input type="hidden" name="action" value="dlSave">
                            <button type="submit" class="btn btn-info">Download Save</button>
                        </form>
                        <form action="/u:{{ $user->username }}.html" method="post">
                            @csrf
                            <input type="hidden" name="action" value="delSave">
                            <button type="submit" class="btn btn-danger">Delete Save</button>
                        </form>
                    </div>
                    @endif
                    @endif
                    @endauth

                    @if($user->b64_prsv && $user->raw_prsv)
                    <div class="d-flex justify-content-center mb-2">
                        <a href="/trainercard:{{ $user->username }}.html" class="btn btn-success">Trainer Card</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Discord User</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $user->username }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Likes</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $likes }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Uploaded Glitches</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $user->getUploadCount() ?: 'None' }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Join Date</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ date('F j, Y', $user->join_date) }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Last Login</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ date('F j, Y, g:i a', $user->last_login) }}</p></div>
                    </div>
                </div>
            </div>

            @if($glitches->count() > 0)
            <table class="table table-striped table-dark" id="userGlitches">
                <thead>
                    <tr>
                        <th>Sprite</th>
                        <th>Name</th>
                        <th>Rating</th>
                        <th>Base Pokemon</th>
                        <th>Primary Type</th>
                        <th>Secondary Type</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($glitches as $glitch)
                        @php
                            $mon2 = $glitch->getJsonData();
                            $ogMon = $glitch->getOGMon();
                        @endphp
                        <tr>
                            <td><img src="/front:{{ $glitch->id }}.png" class="rounded-circle img-fluid" style="width: 64px;"></td>
                            <td>{{ $glitch->name }}</td>
                            <td>{{ $glitch->getRating() }}</td>
                            <td>{{ ucwords(str_replace('-', ' ', $ogMon->name)) }}</td>
                            <td><img src="/img/types/{{ $mon2->primaryType }}.png"></td>
                            <td><img src="/img/types/{{ $mon2->secondaryType }}.png"></td>
                            <td>
                                <a href="/g:{{ urlencode(str_replace(' ', '', $glitch->name)) }}:{{ $glitch->id }}.html"
                                    class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <script>
                $(document).ready(function() {
                    $('#userGlitches').DataTable({
                        paging: true,
                        searching: true,
                        order: [[2, 'desc']]
                    });
                });
            </script>
            @endif
        </div>
    </div>
</section>
@endsection
