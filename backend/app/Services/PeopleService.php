<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\Peoples;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PeopleService
{
    public function duplicateEntry($email, $ni_no, $excludePeopleId = null)
    {
        $emailExists = Peoples::where('email', $email)
            ->where('people_id', '!=', $excludePeopleId)
            ->exists();
        if ($emailExists) {
            return ["status" => 409, "message" => "Email Already Exists", "emailExists" => true];
        }
        $ninoExists = Peoples::where('is_deleted', 0)
            ->where('ni_no', $ni_no)
            ->where('people_id', '!=', $excludePeopleId)
            ->exists();
        if ($ninoExists) {
            return ["status" => 409, "message" => "NINO is not unique!", "emailExists" => false];
        }
        return null;
    }
    public function insertPeople($request)
    {
        try {
            $user = Auth::user();
            $organization_id = $user->organization_id;
            Log::info('User organization ID', ['organization_id' => $organization_id]);
            Log::info("user");
            Log::info($user);

            DB::beginTransaction();

            $exists = $this->duplicateEntry($request['email'], $request['ni_no']);
            if ($exists) {
                return $exists;
            }

            $date_of_birth = Carbon::parse($request['date_of_birth'])->format('Y-m-d');

            Peoples::insert([
                'name' => $request['name'],
                'email' => $request['email'],
                'job_type' => $request['job_type'],
                'gender' => $request['gender'],
                'date_of_birth' => $date_of_birth,
                'address' => $request['address'],
                'city' => $request['city'],
                'state' => $request['state'],
                'country' => $request['country'],
                'pincode' => $request['pincode'],
                'company_id' => $request['company_id'],
                'ni_no' => $request['ni_no'],
                'organization_id' => $organization_id,
            ]);
            DB::commit();
            return ["status" => 200, "message" => "People Inserted Successfully"];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            Log::error("PeopleServices :: insertPeople");
            return ["status" => 422, "message" => "Exception occured"];
        }

    }

    public function updatePeople($request, $id)
    {
        try {
            DB::beginTransaction();
            $date_of_birth = Carbon::parse($request['date_of_birth'])->format('Y-m-d');
            $user = Auth::user();

            $exists = $this->duplicateEntry($request['email'], $request['ni_no'], $id);
            if ($exists) {
                return $exists;
            }

            Peoples::where('people_id', $id)
                ->update([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'job_type' => $request['job_type'],
                    'gender' => $request['gender'],
                    'date_of_birth' => $date_of_birth,
                    'address' => $request['address'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'pincode' => $request['pincode'],
                    'company_id' => $request['company_id'],
                    'ni_no' => $request['ni_no'],
                    'organization_id' => $user->organization_id,
                ]);
            DB::commit();
            return ["status" => 200, "message" => "People Updated Successfully"];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            Log::error("PeopleServices :: updatePeople");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function deletePeople($id)
    {
        try {
            DB::beginTransaction();
            Peoples::where("people_id", $id)
                ->update([
                    "is_deleted" => 1,
                ]);
            Assignments::where("people_id", $id)
                ->update([
                    "is_deleted" => 1,
                ]);

            DB::commit();
            return ["status" => 200, "message" => "People Deleted Successfully"];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            Log::error("PeopleServices :: deletePeople");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function getPeopleDetails($page, $pageSize, $filter)
    {

        try {
            $currentPage = $page + 1;
            $user = Auth::user();

            $query = Peoples::join("companies", "companies.company_id", "=", "peoples.company_id")
                ->where('peoples.organization_id', $user->organization_id)
                ->where("peoples.is_deleted", 0)
                ->select(
                    'peoples.people_id',
                    'peoples.name',
                    'peoples.email',
                    'peoples.gender',
                    'date_of_birth',
                    'peoples.job_type',
                    'peoples.address',
                    'peoples.city',
                    'peoples.state',
                    'peoples.country',
                    'peoples.pincode',
                    'peoples.company_id',
                    'company_name',
                    'peoples.ni_no',
                );

            if (!empty($filter)) {
                foreach ($filter as $filterField => $filterValue) {
                    if ($filterField === 'email') {
                        $query->where('peoples.email', 'like', "%$filterValue%");
                        continue;
                    }
                    if ($filterValue !== null && $filterValue !== '') {
                        $query->where($filterField, 'like', "%$filterValue%");
                    }
                }
            }

            $peopleData = $query->orderBy('people_id', 'desc')
                ->paginate($pageSize, ['*'], 'page', $currentPage);

            return ["status" => 200, "peopleData" => $peopleData];
        } catch (Exception $e) {
            Log::error("PeopleServices :: getPeopleDetails");
            return ["status" => 422, "peopleData" => ""];
        }
    }
}