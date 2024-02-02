<?php /** @var \App\Traits\HasCorrelationCoefficient $c */ ?>
<div class="shadow bg-white pt-4 pb-4 mt-4">
    <div style="text-align: center;">
        <h3 style="text-align: center;" class="study-title text-2xl">
            {!! $c->generateStudyTitle(true, true) !!}
        </h3>
    </div>
    '
    <div style="text-align: center; max-width: 95%; margin: auto;">
        <div class="gauge-and-images" style="justify-content:space-around;">
                <span style="display: inline-block; max-width: 10%;">
                    <img
                            style="max-width: 100%; max-height: 150px;"
                            src="{{ $c->getCauseVariableImage() }}"
                            alt="cause image"
                    >
                </span>
            <span style="display: inline-block; max-width: 65%;">
                    <img
                            style="max-width: 100%; max-height: 200px;"
                            src="{{ $c->getGaugeImage() }}"
                            alt="gauge image"
                    >
                </span>
            <span style="display: inline-block; max-width: 10%;">
                    <img
                            style="max-width: 100%; max-height: 150px;"
                            src="{{ $c->getEffectVariableImage() }}"
                            alt="effect image"
                    >
                </span>
        </div>
        <div>
            <div
                    class="text-xl"
                    style="padding: 20px; text-align: center;"
            >
                {{ $c->getTagLine() }}
            </div>
        </div>
    </div>
    <div style="text-align: center; margin: auto;">
        {!! $c->getRoundOutlineWithIcon() !!}
    </div>
</div>
