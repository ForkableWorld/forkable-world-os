<div style='
    text-align: center;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.23), 0 3px 12px rgba(0, 0, 0, 0.16);
    transition: 0.3s;
    padding: 1px 10px 15px 10px;
    border-radius: 9px;
    max-width: 600px;
    margin: 20px auto auto;
    border: 0 solid #2b3138;
    overflow: hidden;
'>
    <h1 style='text-align: center;'>
        @isset($title)
            {{ $title }}
        @endisset
        @empty($title)
                Title not set
        @endempty
    </h1>
    @isset($body)
        {{ $body }}
    @endisset
    @empty($body)
        body not set
    @endempty
    <p>
        @isset($footer)
            {{ $footer }}
        @endisset
        @empty($footer)
            footer not set
        @endempty
    </p>
</div>