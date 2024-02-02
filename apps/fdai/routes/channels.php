<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
use App\Models\User;
Broadcast::channel(User::generateBroadcastChannelName('{id}'), function ($user, $id) {
    /** @var User $user */
    return (int) $user->ID === (int) $id;
});

Broadcast::channel('post.{postId}', function ($user, $orderId) {
    /** @var User $user */
    return $user->ID === \App\Models\WpPost::findOrNew($orderId)->post_author;
});

Broadcast::channel('posts', function ($user) {
    /** @var User $user */
    return $user->isAdmin();
});

Broadcast::channel('chat', function ($user) {
    return Auth::check();
});