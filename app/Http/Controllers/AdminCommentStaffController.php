<?php

namespace App\Http\Controllers;

use App\Comment;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;

class AdminCommentStaffController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $jwt)
    {
        //
        $this->jwt = $jwt;
    }

    //

    public function index()
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $comments = Comment::all();
        if (!$comments)
            return response()->json('comments not found', 404);

        return response()->json('comments', 200);
    }


    public function show(int $id, int $offset = 0)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $comment_collection = Comment::where('staff_id', $id)->where('company_id', $company->id);
        $comment_count = $comment_collection->count();
        $comments = $comment_collection->skip($offset)->take(15)->get();
        if (!$comments)
            return response()->json('comments not found', 404);

        return response()->json(compact('comments', 'comment_count'), 200);
    }

    public function create(Request $request)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        //

        $this->validate($request, [
            'staff_id' => 'integer|required|exists:staffs,id',
            'comment' => 'string|required',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;
        $credentials['commentable_type'] = 'App\User';
        $credentials['commentable_id'] = $user->id;

        $comment = Comment::create($credentials);

        if (!$comment)
            return response()->json('unable to create comment', 500);

        return response()->json(compact('comment'));


    }

    public function delete(Request $request)
    {
        //

        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $comment = Comment::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$comment)
            return response()->json('comment not found', 404);

        $result = $comment->delete();

        if (!$result)
            return response()->json('unable to delete comment', 500);

        return response()->json("comment successfully deleted", 200);
    }


}
