<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Position;
use App\Models\Role;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @authenticated
 * @group Admin\Account
 * Class AccountController
 * @package App\Http\Controllers\Api\V1\Admin
 */

class LoginController extends Controller
{

    /**
     * Get my profile
     * @apiResource  \App\Http\Resources\AccountResource
     * @apiResourceModel  \App\Models\Account
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        return view('login');
    }

    /**
     * Update my profile
     * @apiResource  \App\Http\Resources\AccountResource
     * @apiResourceModel  \App\Models\Account
     *
     * @param Request $request
     * @return mixed
     */
    public function updateProfile(Request $request)
    {
        //TODO
    }

    /**
     * Update my admin profile
     * @apiResource  \App\Http\Resources\AccountResource
     * @apiResourceModel  \App\Models\Account
     *
     * @param Request $request
     * @return mixed
     */
    public function updateAdminProfile(Request $request)
    {
        $account = $request->user();

        if (!$account->admin){
            return $this->error('update_admin_failed', __('user.account_not_admin'));
        }

        //Profile information
        $adminData = $request->validate([
            'contact_email'    => 'sometimes|required|email|max:191|unique:admins,contact_email,' . $account->admin->id,
            'firstname'  => 'sometimes|required|max:191',
            'lastname'  => 'sometimes|required|max:191',
            'avatar' => 'sometimes|required',
            'phone' => 'sometimes|required|max:20|unique:admins,phone,' . $account->admin->id,
        ]);

        try {
            $resp = DB::transaction(function ($resp) use ($account, $adminData, &$error) {
                $account->admin()->update($adminData);

                return $account->refresh();
            });

        } catch (\Illuminate\Database\QueryException $e) {
            logger($e->getMessage());
            return $this->error('admin_update_failed', __('user.update_admin_failed'));
        }

        return response($resp,201);
    }
}
