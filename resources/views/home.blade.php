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

                            <button type="submit" class="btn btn-primary">Download</button>
                        </form>
                        <?php
                        if(!empty($file_name_destination)) {
                            echo '<a href="'.$file_name_destination.'" download>Download Mp3</a>';
                        }
                        ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
