<?php /** @var \App\Models\VariableCategory $model */ ?>
@include('search-filter-input', ['searchId' => $model->getTableName(), 'placeholder' => $model->getPlaceholder()])
@isset($heading)
    <h2 style="text-align: center;" class="text-3xl mb-2 font-semibold leading-normal">
        {{ $model->getTitleAttribute() }}
    </h2>
@endisset
@include('chips', ['searchId' => $model->getTableName(), 'buttons' => $model->getVariablesOrButtons()])
@include('not-found-box', ['notFoundButtons' => $model->getNotFoundButtons()])
