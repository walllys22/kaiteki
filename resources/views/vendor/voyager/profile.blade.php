@extends('voyager::master')

@section('css')
    <style>
        .user-email {
            font-size: .85rem;
            margin-bottom: 1.5em;
        }
    </style>
@stop

@section('content')
    <div
        style="background-size:cover; background-image: url({{ Voyager::image(Voyager::setting('admin.bg_image'), asset('/images/bg_image.png')) }}); background-position: center center;position:absolute; top:0; left:0; width:100%; height:300px;">
    </div>
    <div style="height:160px; display:block; width:100%"></div>
    <div style="position:relative; z-index:9; text-align:center;">

        @php
            $user = App\Models\User::where('id', Auth::user()->id)->first();
            $user_avatar = null;
            if ($user->person) {
                // if ($user->person->image) {
                //     $user_avatar = asset('storage/' . str_replace('.', '-cropped.', $user->person->image));
                // }
                if ($user->person->image) {
                    $pathInfo = pathinfo($user->person->image);
                    $extension = strtolower($pathInfo['extension'] ?? '');

                    if (str_contains($extension, 'avif')) {
                        $image = str_replace('.avif', '', $user->person->image);
                    }
                    $user_avatar = asset('storage/' . $image . '-cropped.webp');
                }
            }

        @endphp
        {{-- <img src="@if (!filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL)){{ Voyager::image( Auth::user()->avatar ) }}@else{{ Auth::user()->avatar }}@endif" --}}
        <img src="{{ $user_avatar ? $user_avatar : Voyager::image(Auth::user()->avatar) }}" class="avatar"
            style="border-radius:50%; width:150px; height:150px; border:5px solid #fff;"
            alt="{{ Auth::user()->name }} avatar">
        <h4>{{ ucwords(Auth::user()->name) }}</h4>
        <div class="user-email text-muted">{{ ucwords(Auth::user()->email) }}</div>
        <p>{{ Auth::user()->bio }}</p>
        @if ($route != '')
            <a href="{{ $route }}" class="btn btn-primary">{{ __('voyager::profile.edit') }}</a>
        @endif
    </div>
@stop
