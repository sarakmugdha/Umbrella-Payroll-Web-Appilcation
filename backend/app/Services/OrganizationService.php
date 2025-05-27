<?php
namespace App\Services;
use App\Models\Organizations;
use Auth;
use Exception;



use DB;

class OrganizationService
{

    public function store($request)
    {

        try {

            DB::beginTransaction();
            $image = $request->input('organization_logo');
            $base64Image = base64_encode(file_get_contents($image));
            $exists = Organizations::where('email', $request['email'])
                ->exists();
            if ($exists) {
                return ["status" => 409];
            }
            $organization = Organizations::insert([
                'organization_name' => $request['organization_name'],
                'email' => $request['email'],
                'address' => $request['address'],
                'state' => $request['state'],
                'pincode' => $request['pincode'],
                'status' => $request['status'],
                'organization_logo' => $base64Image,

            ]);
            DB::commit();
            return ['status' =>'200'];
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        try {
            DB::beginTransaction();
            $image = $request->input('organization_logo');
            $base64Image = base64_encode(file_get_contents($image));
            $organizationData = Organizations::where('organization_id', $id)
                ->update([
                    'organization_name' => $request['organization_name'],
                    'email' => $request['email'],
                    'address' => $request['address'],
                    'state' => $request['state'],
                    'pincode' => $request['pincode'],
                    'status' => $request['status'],
                    'organization_logo' => $base64Image,

                ]);
            DB::commit();
            return $organizationData;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function delete($request, $id)
    {
        try {
            DB::beginTransaction();
            $id = $request->input('organization_id');
            $organizationData = Organizations::where("organization_id", $id)
                ->update([
                    "is_deleted" => '1',
                ]);
            DB::commit();
            return $organizationData;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function getOrganizationDetails($request)
    {
        try {
            DB::beginTransaction();

            $perPage = $request->input('pageSize');
            $page = $request->input('page');
            $filter = $request->input('filter');





            $query = Organizations::
                where("organizations.is_deleted", '0')
                ->select("*");

            if (!empty($filter)) {
                foreach ($filter as $filterField => $filterValue) {
                    if ($filterValue !== null && $filterValue !== '') {
                        $query->where($filterField, 'like', "%$filterValue%");
                    }
                }
            }
            $organizationData = $query->orderBy("organization_id", "desc")
                ->paginate($perPage, ['*'], 'page', $page + 1);
            $organizationData->getCollection()->transform(function ($item) {
                $item->organization_logo = 'data:image/png;base64,' . $item->organization_logo;
                return $item;
            });




            DB::commit();
            return $organizationData;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    public function countOrganization()
    {
        try {
            DB::beginTransaction();
            $organizationData = Organizations::
                where("organizations.is_deleted", '0')
                ->select("*")
                ->orderBy("organization_id", "desc")
                ->count();
            DB::commit();
            return $organizationData;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with("error", $e->getMessage());
        }
    }
    public function countActiveOrganization()
    {
        try {
            DB::beginTransaction();
            $activeCount = Organizations::
                where("status", 1)
                ->where('is_deleted', 0)
                ->count();
            DB::commit();
            return $activeCount;
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getOrgDetails($id)
    {
        try {
            DB::beginTransaction();

            $orgData = Organizations::where('organization_id', $id)
                ->select('*')
                ->get()
                ->map(function ($orgData) {
                    $orgData->organization_logo = 'data:image/png;base64,' . $orgData->organization_logo;
                    return $orgData;
                });

            DB::commit();
            return $orgData;

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getOrgLogo()
    {
        try {
            $id = Auth::user()->organization_id;
            DB::beginTransaction();

            $orgData = Organizations::where('organization_id', $id)
                ->select('organization_logo')
                ->get()
                ->map(function ($orgData) {
                    $orgData->organization_logo = 'data:image/png;base64,' . $orgData->organization_logo;
                    return $orgData;
                });

            DB::commit();
            return $orgData;

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

}
