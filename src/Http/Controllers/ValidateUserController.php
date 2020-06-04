<?php

namespace Alimianesa\SmartAuth\Controllers;

use Alive2212\LaravelMobilePassport\Http\Controllers\MobilePassportAuthController;
use Alive2212\LaravelSmartResponse\ResponseModel;
use Alive2212\LaravelSmartResponse\SmartResponse;
use Alive2212\LaravelSmartRestful\SmartCrudController;
use Alimianesa\SmartAuth\Jobs\CheckPhoneNumberJob;
use App\User;
use Illuminate\Http\Request;


class ValidateUserController extends SmartCrudController
{
    protected $validateRegisterRequest=[
        'phone_number' => 'required',
        'country_code' => 'required',
        'card_number' => 'required'
    ];

    protected $validatePasswordRequest=[
        'password' => 'required'
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function phoneCard(Request $request)
    {
        $response = new ResponseModel();
        $request->validate($this->validateRegisterRequest);
        $data = [
            "country_code" => $request->country_code ,
            "phone_number" => $request->phone_number,
            "card_number"  => $request->card_number
        ];
        $user = User::firstOrCreate($data);

        if (CheckPhoneNumberJob::dispatchNow($user)) {
            $user -> phone_number_confirmed_at = time();
            $user->save();

            $request->request->add([
                'scope' => '["customer"]',
                'imei' => 12345678,
                'app_name' => 'test_app',
                'app_version' => 'v1',
                'platform' => 'android',
                'os' => 'KitKat',
                'push_token' => 1234
            ]);

            $mobilePassport = new MobilePassportAuthController;
            return $mobilePassport->store($request);
        }
        $response->setStatusCode(400);
        $response->setMessage($this->getTrans(__FUNCTION__,'card_number_not_matched'));
        return SmartResponse::response($response);
    }

    /**
     * @return bool
     */
    public function cardPhoneValidation()
    {
        return true;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setPassword(Request $request)
    {
        $response = new ResponseModel();
        $request->validate($this->validatePasswordRequest);
        $user = auth()->user();
        if (!isset($user->phone_number_confirmed_at)) {
            $response->setStatusCode(404);
            $response->setMessage($this->getTrans(__FUNCTION__,'user_not_found'));
            return SmartResponse::response($response);
        }
        $user->password = md5($request->password);
        $user->save();
        $response->setMessage($this->getTrans(__FUNCTION__,'successful'));
        return SmartResponse::response($response);

    }

    /**
     * @inheritDoc
     */
    public function initController()
    {
        $this->model = new User();
    }
}
