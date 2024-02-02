<?php /** @var \Facade\IgnitionContracts\Solution $solution */ ?>
<div id="solution-container" class="solution-content ml-0" style="max-width: 600px; margin: auto;">
    <h4 class="solution-title">{{ $solution->getSolutionTitle() }}</h4>
    <div>
        <p>
            {!! $solution->getSolutionDescription()  !!}
        </p>
    </div> <!---->
    <div class="mt-8 grid justify-start">
        <div class="border-t-2 border-gray-700 opacity-25 "></div>
        <div class="pt-2 grid cols-auto-1fr gapx-4 gapy-2 text-sm">
            <ul>
                @foreach( $solution->getDocumentationLinks() as $text => $url)
                    <li>
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            class="link-solution"
                        >{{ $text }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
