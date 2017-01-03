<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request) {
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $search = $request->input('search');

        if ($search) {
            $users = User::orderBy('id', 'DESC')->where('id', '=', "$search")->select('id', 'name', 'email', 'img')->paginate($limit);
            $users->appends(array('search' => $search, 'limit' => $limit));
        } else {
            $users = User::orderBy('id', 'DESC')->select('id', 'name', 'email', 'img')->paginate($limit);
        }
        return \Response::json([
                    'status' => true,
                    'data' => $this->transformCollection($users)
                        ], 400);
    }

    public function show($id) {
        $user = User::with(array('User' => function($query) {
                        $query->select('id', 'name');
                    }))->find($id);
        if (!$user) {
            return \Response::json([
                        'message' => 'Users does not exist'
                            ], 404);
        }
        //get previous joke id
        $previous = User::where('id', '<', $user->id)->max('id');
        //get next joke id
        $next = User::where('id', '>', $user->id)->min('id');
        return \Response::json([
                    'previous_joke_id' => $previous,
                    'next_joke_id' => $next,
                    'data' => $this->transform($user)
                        ], 200);
    }

    public function store(Request $request) {
        if (!$request->name || !$request->email ||   !$request->img) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Please provide {name},{email},{category},{img}'
                            ], 422);
        }
        $userId = JWTAuth::parseToken()->toUser()->id;
        $data = $request->all();
        $data['user_id'] = $userId;
        $user = User::create($data);
        return \Response::json([
                    'status' => true,
                    'message' => 'User Created Successfully',
                    'data' => $this->transform($user)
        ]);
    }

    public function update(Request $request, $id) {
        $userId = JWTAuth::parseToken()->toUser()->id;
        $user = User::find($id);

        if ($user != null) {
            $user->fill($request->all());
            $tmpUser = $user->toArray();
            $user->save($tmpUser);
            return \Response::json([
                        'status' => true,
                        'message' => 'User Update Successfully',
            ]);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Users does not exist'
                        ], 422);
    }

    public function destroy($id) {
        if (!User::destroy($id)) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Users does not exist'
                            ], 422);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'User Delete Successfully',
        ]);
    }

    private function transformCollection($users) {
        $usersArray = $users->toArray();

        return [
            'total' => $usersArray['total'],
            'perPage' => intval($usersArray['per_page']),
            'currentPage' => $usersArray['current_page'],
            'lastPage' => $usersArray['last_page'],
            'next_page_url' => $usersArray['next_page_url'],
            'prev_page_url' => $usersArray['prev_page_url'],
            'from' => $usersArray['from'],
            'to' => $usersArray['to'],
            'data' => array_map([$this, 'transform'], $usersArray['data'])
        ];
    }

    private function transform($user) {
        return [
            'userId' => $user['id'],
            'userName' => $user['name'],
            'userEmail' => $user['email'],
            'UserImg' => $user['img']
        ];
    }

}
