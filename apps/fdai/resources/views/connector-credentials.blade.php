<!-- Start resources/views/chip-search.blade.php -->
<?php /** @var App\Models\Connector $connector */ ?>
@extends('layouts.so-simple-layout-no-header')
{{--@extends('layouts.report-layout')--}}
@section('content')
    <div class="tailplate-live-sample py-4">
        <div>
            <div class="shadow-lg sm:flex">
                <div class="sm:w-2/5 w-full bg-gray-400 bg-cover bg-center text-white"
                     style="background-image: url('{{ $connector->image }}');">
                    <!--                    <div class="p-8">
                        <h1>{{ $connector->display_name }}</h1>
                        <p class="leading-tight mt-2 text-sm text-white">
                            Create an account to join our awesome community
                        </p>
                    </div>-->
                </div>
                <div class="sm:w-3/5 w-full bg-white">
                    <div class="p-8">
                        <h1>{{ $connector->display_name }}</h1>
                        {{--                        <p class="leading-tight mt-2 text-sm">  {{ $connector->short_description }} </p>--}}
                        <p>{{ $connector->long_description }}</p>
                        <form _lpchecked="1" action="{{$connector->getConnectUrlWithParams()}}" method="GET"
                              class="connect-form">

                            @foreach($connector->getConnectInstructions() ->getParameters() as $p)
                                <label class="text-lg text-gray-500" for="{{$p->key}}">
                                    {{$p->displayName}}
                                </label>
                                <br>
                                <input id="{{$p->key}}"
                                       class="bg-transparent border-b m-auto block border-gray-500 w-full mb-6 text-gray-700 pb-1"
                                       type="{{$p->type}}"
                                       name="{{$p->key}}"
                                       value="{{$p->defaultValue}}"
                                       placeholder="{{$p->placeholder}}"
                                       autocomplete="false"
                                       style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAYAAABSO15qAAAAAXNSR0IArs4c6QAAAPhJREFUOBHlU70KgzAQPlMhEvoQTg6OPoOjT+JWOnRqkUKHgqWP4OQbOPokTk6OTkVULNSLVc62oJmbIdzd95NcuGjX2/3YVI/Ts+t0WLE2ut5xsQ0O+90F6UxFjAI8qNcEGONia08e6MNONYwCS7EQAizLmtGUDEzTBNd1fxsYhjEBnHPQNG3KKTYV34F8ec/zwHEciOMYyrIE3/ehKAqIoggo9inGXKmFXwbyBkmSQJqmUNe15IRhCG3byphitm1/eUzDM4qR0TTNjEixGdAnSi3keS5vSk2UDKqqgizLqB4YzvassiKhGtZ/jDMtLOnHz7TE+yf8BaDZXA509yeBAAAAAElFTkSuQmCC&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
                                <br>
                            @endforeach
                            <script>
                                $('.connect-form').on('submit', function (e) {
                                    e.preventDefault();
                                    showLoader();
                                    var formData = $(this).serialize();
                                    var fullUrl = window.location.href;
                                    var finalUrl = fullUrl + "&" + formData;
                                    window.location.href = finalUrl;
                                })
                            </script>
                            <input class="shadow-lg pt-3 pb-3 w-full text-white bg-indigo-500 hover:bg-indigo-400 rounded-full cursor-pointer "
                                   onclick="showLoader()"
                                   type="submit"
                                   value="Save">
                        </form>
                        <div class="text-center mt-4">
                            <p class="text-sm text-grey-dark">Don't have an account?
                                <a class="no-underline text-indigo-500 font-bold hover:text-indigo-300"
                                   href="{{ $connector->get_it_url }}" target="_blank">
                                    Get it here
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
