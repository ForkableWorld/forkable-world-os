<?php
/**
* Language file for group error/success messages
*
*/

return [

    'group_exists'        => 'Group already exists!',
    'group_not_found'     => 'Group [:id] does not exist.',
    'group_name_required' => 'The name field is required',
    'users_exists'        => 'Group contains users, group can not be deleted',

    'success' => [
        'create' => 'Group was successfully created.',
        'update' => 'Group was successfully updated.',
        'delete' => 'Group was successfully deleted.',
    ],

    'delete' => [
        'create' => 'There was an issue creating the group. Please try again.',
        'update' => 'There was an issue updating the group. Please try again.',
        'delete' => 'There was an issue deleting the group. Please try again.',
    ],

    'error' => [
        'group_exists' => 'A group already exists with that name, names must be unique for groups.',
    ],

];
