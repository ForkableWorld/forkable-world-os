<?php /** @var App\Models\BaseModel $model */ ?>
<div class="py-3 center mx-auto">
    <div class="bg-white px-4 py-5 rounded-lg shadow-lg text-center w-48">
        <div class="mb-4">
            <img class="w-auto mx-auto rounded-full object-cover object-center" src="https://i1.pngguru.com/preview/137/834/449/cartoon-cartoon-character-avatar-drawing-film-ecommerce-facial-expression-png-clipart.jpg" alt="Avatar Upload" />
        </div>
        <label class="cursor-pointer mt-6">
            <span class="mt-2 text-base leading-normal px-4 py-2 bg-blue-500 text-white text-sm rounded-full" >Select Avatar</span>
            <input type='file' class="hidden" :multiple="multiple" :accept="accept" />
        </label>
    </div>
</div>