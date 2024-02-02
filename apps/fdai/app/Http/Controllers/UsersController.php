<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Http\Resources\UserResource;
use App\Models\OAClient;
use App\Models\User;
use App\Notifications\UserFollowed;
use App\Properties\User\UserProviderIdProperty;
use Illuminate\Http\Request;
use InfyOm\Generator\Utils\ResponseUtil;

class UsersController extends Controller
{
    public function index()
    {
        $auth = auth()->user();
        $users = $auth->patient_physicians_where_physician_user()->get();
        //$users = User::where(User::FIELD_ID, '!=', auth()->user()->id)->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request){
        /** @var User $u */
        $u = User::findOrCreateByProviderId($request->all());
        $msg = 'User found';
        if($u->wasRecentlyCreated){
            $msg = 'User created';
        }
        $resource = new UserResource($u);
        return \Response::json(ResponseUtil::makeResponse($msg, $resource->toArray($request)), 201);
    }

    public function show(User $user)
    {
        return User::findByRequest()->getShowPageHtml();
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {
        //
    }

    public function destroy(User $user)
    {
        //
    }
    //...
    public function follow(User $user){
        $follower = auth()->user();
        if($follower->id == $user->id){
            return back()->withError("You can't follow yourself");
        }
        if(!$follower->isFollowing($user->id)){
            $follower->follow($user->id);
            // sending a notification
            $user->notify(new UserFollowed($follower));
            return back()->withSuccess("You are now friends with {$user->name}");
        }
        return back()->withError("You are already following {$user->name}");
    }
    public function unfollow(User $user){
        $follower = auth()->user();
        if($follower->isFollowing($user->id)){
            $follower->unfollow($user->id);
            return back()->withSuccess("You are no longer friends with {$user->name}");
        }
        return back()->withError("You are not following {$user->name}");
    }
}
