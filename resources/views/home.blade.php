@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <main class="px-4 py-5 my-5 text-center">
                        <h1>Y2Mp3</h1>
                        <form method="post">
                            @csrf
                            <label for="url">Youtube URL</label>
                            <input type="url" name="url" id="url" placeholder="https://www.youtube.com/watch?v=XXXXX"
                                   required="required" class="form-control">

                            <button type="submit" class="btn btn-primary mt-2">
                                <i class="bi bi-cloud-arrow-down-fill"></i> {{ __('Download') }}
                            </button>
                        </form>
                        @error('url')
                            <div class="alert alert-danger mt-3" role="alert">{{ $message }}</div>
                        @enderror
                        @if(!empty($error))
                            <div class="alert alert-danger mt-3" role="alert">
                                {{ __('The audio could not be downloaded. Please check the URL and try again.') }}
                            </div>
                        @endif
                        @if(!empty($file_name_destination))
                            <a href="{{ asset($file_name_destination) }}" class="d-inline-block mt-3" download>Download Mp3</a>
                        @endif
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
