<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Models\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{


    public function createGroupWithUsers(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'users' => 'required|array'
        ]);

        // Create the group
        $group = new Group;
        $group->name = $validatedData['name'];
        $group->save();

        // Add the current user to the group
        $groupUser = new GroupUser;
        $groupUser->group_id = $group->id;
        $groupUser->user_id = $user->id;
        $groupUser->is_admin = true;
        $groupUser->save();

        // Add other users to the group
        foreach ($validatedData['users'] as $userId) {
            $groupUser = new GroupUser;
            $groupUser->group_id = $group->id;
            $groupUser->user_id = $userId;
            $groupUser->save();
        }

        return $this->success($group, "{$group->name} created successfully");

    }
    public function addUserToGroup(Request $request): JsonResponse
    {
        $user = Auth::user();

        $groupId = $request->group_id;
        $userId = $request->user_id;

        // Validate request data
        $validatedData = $request->validate([
            'group_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);


        // Check if user is the owner of the group

        $groupUser = GroupUser::where('group_id', $groupId)->with('user')->first();

        if (!$groupUser || !$groupUser->is_admin || $groupUser->user_id !== $user->id) {

            return $this->error('You do not have permission to add members to this group', 403);
        }


        // Check if user to be added exists
        $userexists = User::where('id', $userId)->exists();

        if (!$userexists) {
            return $this->error('User not in the system', 403);
        }

        // Check if user is already a member of the group
        $userAlreadyInGroup = GroupUser::where('user_id', $userId)
        ->where('group_id', $groupId)
        ->exists();

        if ($userAlreadyInGroup) {
            return $this->error('user is already a member of the group', 403);
        }else {
            // Create group user
            $newuser = new GroupUser;
            $newuser->group_id = $groupId;
            $newuser->user_id = $userId;
            $newuser->is_admin = $request->is_admin??false;
            $newuser->save();

            // broadcast new user added to the group

            return $this->success($groupUser, "{$groupUser->user->name} added to {$groupUser->group->name} successfully");
        }

        return $this->success($groupUser, " successfully");

    }
    public function createMessage(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate request data
        $validatedData = $request->validate([
            'group_id' => 'required|integer',
            'message' => 'required|string|max:1000'
        ]);


        // Check if user is a member of the group
        $group = Group::find($validatedData['group_id']);
        if (!$group || !$group->users()->where('users.id', $user->id)->exists()) {
            return $this->error('You are not a member of this group', 403);
        }

        // Create the message
        $message = new Message;
        $message->group_id = $validatedData['group_id'];
        $message->user_id = $user->id;
        $message->content = $validatedData['message'];
        $message->save();

        ///TODO: broadcast new message to group members.


        // Increment unread_count for all other members of the group
        $group->users()
        ->where('users.id', '<>', $user->id) // exclude sender
        ->each(function ($user) {
            $user->pivot->increment('unread_count');
        });

        return $this->success($message);


    }
    public function getUserGroups(Request $request): JsonResponse
    {

        // $currentPage = $data['page'];
        // $pageSize = $data['page_size'] ?? 15;

        // Get the authenticated user's ID
        $userId = Auth::id();

        //  Get the authenticated user's groups
        // $groups = Group::whereHas('users', function ($query) use ($userId) {
        //     $query->where('user_id', $userId);
        // })
        // ->with('messages', function($query) {
        //     $query->latest('created_at')->with('user')->first();
        // })
        // ->get();
        $groups = GroupUser::where('user_id', $userId)
        ->with([
            'group' => function ($query) {
                $query->with([
                    'messages' => function ($query) {
                        $query->latest()->first();
                    }
                ]);
            }
        ])
        ->get();


        return $this->success($groups);
    }

    public function groupMessages(Request $request): JsonResponse
    {
        $userId = Auth::id();
        // Get the group ID from the request
        $groupId = $request->group_id;

        $messages = Group::where('id', $request->group_id)
        ->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['messages' => function ($query) {
            $query->with('user')->orderBy('created_at', 'DESC');
        }])
        ->first();

        // Check if the authenticated user belongs to the group
        $userInGroup = GroupUser::where('group_id', $groupId)->where('user_id', $userId)->exists();
        // Get the authenticated user's membership in the group

        if ($userInGroup) {
            // Get the group and its users
            $groupUsers = GroupUser::where('group_id', $groupId)->with('user')->get();

            // Return the users in the group
            // return $group->users;

            $data = [
                "messages" => $messages,
                "users" => $groupUsers,
            ];

            return $this->success($data);
        } else {
            // Return an error response
            return $this->error('Unauthorized', 401);
        }

    }
}
