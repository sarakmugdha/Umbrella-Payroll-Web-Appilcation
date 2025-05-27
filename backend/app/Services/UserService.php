<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    public function getUserDetails($request)
    {
        $id = $request->input("id");
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);
        $filter = $request->input('filter');


        $query = user::where("users.is_deleted", 0)
            ->where("organization_id", $id)
            ->select("users.name", "users.email", "users.username", "users.id", "users.status");

        if (!empty($filter)) {
            foreach ($filter as $filterField => $filterValue) {
                if ($filterValue !== null && $filterValue !== '') {
                    $query->where($filterField, 'like', "%$filterValue%");
                }
            }
        }

        $userData = $query->orderBy("id", "desc")
            ->paginate($perPage, ['*'], 'page', $page + 1);

        return [
            'data' => $userData->items(),
            'total' => $userData->total(),
        ];
    }


    public function addUser(Request $request)
    {
        $exists = User::where('email', $request['email'])
            ->exists();
        if ($exists) {
            return ["status" => 409];
        }

        $userData = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'username' => $request['username'],
            'organization_id' => $request['organization_id'],

        ]);
        $userData->sendEmailVerificationNotification();
        return ["status" => 200];

    }

    public function deleteUser($id)
    {



        $userData = user::where('id', $id)
            ->update(['is_deleted' => 1]);
        return $userData;
    }
    public function updateUserStatus(Request $request)
    {
        User::where('id', $request->input('id'))
            ->update(['users.status' => $request->input('status')]);
        return response()->json(['success' => true]);
    }

}
