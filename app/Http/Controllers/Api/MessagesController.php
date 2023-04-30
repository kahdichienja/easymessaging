<?php

namespace App\Http\Controllers\Api;

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

        // Validate request data
        $validatedData = $request->validate([
            'group_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);


        // Check if user is the owner of the group
        // $group = Group::find($validatedData['group_id']);

        // if (!$group || $group->group_id !== $user->id) {
        //     return response()->json([
        //         'message' => 'You do not have permission to add members to this group'
        //     ], 403);
        // }


        // Check if user to be added exists
        // Check if user is already a member of the group

        // Create group user
        $groupUser = new GroupUser;
        $groupUser->group_id = $validatedData['group_id'];
        $groupUser->user_id = $validatedData['user_id'];
        $groupUser->is_admin = $request->is_admin??false;
        $groupUser->save();

        return $this->success($groupUser, "{$groupUser->user->name} added to {$groupUser->group->name} successfully");

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
            return response()->json([
                'message' => 'You are not a member of this group'
            ], 403);
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
        $groups = Group::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with('messages', function($query) {
            $query->latest('created_at')->with('user')->first();
        })
        ->get();


        return $this->success($groups);
    }

    public function groupMessages(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $messages = Group::with([
            'messages' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'users' => function ($query) use ($userId) {
                $query->where('user_id', '<>', $userId);
            }
        ])
        ->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->findOrFail($request->group_id);
        return $this->success($messages);
    }
}
