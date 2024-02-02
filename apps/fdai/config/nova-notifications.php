<?php
use App\Models\User;
return [
    // The Echo namespaced path to the User model
    'user_model' => User::generateBroadcastChannelName(null),
];
