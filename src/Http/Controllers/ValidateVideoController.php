<?php

namespace Alimianesa\SmartAuth\Http\Controllers;

use Alive2212\LaravelSmartResponse\ResponseModel;
use Alive2212\LaravelSmartResponse\SmartResponse;
use Alive2212\LaravelSmartRestful\BaseController;
use Alimianesa\SmartAuth\AliveFile;
use Alimianesa\SmartAuth\Jobs\VideoFaceRecognitionJob;
use Alimianesa\SmartAuth\SpeechText;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ValidateVideoController extends BaseController
{
    protected $validateVideoRequest =  [
        'video'   => 'required|mimes:mp4|max:40000',
    ];

    /**
     * @inheritDoc
     */
    public function initController()
    {
        $this->model = new AliveFile();
        $this->middleware(['auth:api']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getText()
    {
        $response = new ResponseModel();

        // Find User
        $user = auth()->user();

        // Check User Phone number & Card Verification
        $canSendVideo = $this->checkCardVerification($user);
        if (!$canSendVideo) {
            $response->setStatusCode(400);
            $response->setMessage($this->getTrans(__FUNCTION__,'user_not_verified'));
            return SmartResponse::response($response);
        }

        // Get Random Text
        $randomText = SpeechText::all()->random(1)->first();

        // Cache User & Text
        Cache::put($user->id , [
            'user' => $user,
            'text' => $randomText
        ], now()->addMinutes(180));

        // Add Weight
        $randomText->weight += 1 ;
        $randomText->save();

        // Response
        $response->setData(collect($randomText));
        $response->setMessage($this->getTrans(__FUNCTION__,'successful'));
        return SmartResponse::response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoValidation(Request $request)
    {
        $response = new ResponseModel();
        $request->validate($this->validateVideoRequest);

        // Find User
        $user = auth()->user();

        // Check User Phone number & Card Verification
        $canSendVideo = $this->checkCardVerification($user);
        if (!$canSendVideo) {
            $response->setStatusCode(400);
            $response->setMessage($this->getTrans(__FUNCTION__,'user_not_verified'));
            return SmartResponse::response($response);
        }

        // Check User Text
        $receivedText = $this->receivedText($user);
        if (!$receivedText) {
            $response->setStatusCode(400);
            $response->setMessage($this->getTrans(__FUNCTION__,'user_has_not_received_text'));
            return SmartResponse::response($response);
        }

        // Save Video
        $video = $request->file('video');
        $details = [
            'size' => $video->getSize(),
            'title' => 'video',
            'description' => 'video',
        ];
        $videoUri = $this->videoUpload($video);
        $videoFile = $this->saveVideo(
            $video,
            $videoUri ,
            $details,
            $user);

        // Attach to Video tag
        $videoFile->tags()->attach(4);

        // TODO : video face recognition job
        VideoFaceRecognitionJob::dispatch($user , $videoFile);

        // Response
        $response->setMessage($this->getTrans(__FUNCTION__,'successful'));
        return SmartResponse::response($response);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function checkCardVerification(User $user)
    {
        if (is_null($user->phone_number_confirmed_at) or
            is_null($user->card_verified_at)
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param $video
     * @return string
     */
    public function videoUpload($video)
    {
        $videoName = time() . Str::random(16) . $video->getClientOriginalName();
        $uri = 'videos/' . $videoName;
        Storage::disk('local')->putFileAs('videos/', $video , $videoName);

        return $uri;
    }

    /**
     * @param $image
     * @param $uri
     * @param $details
     * @param User $user
     * @return AliveFile
     */
    public function saveVideo($image , $uri , $details , User $user)
    {
        $file = new AliveFile;

        $file->size = $details['size'];
        $file->uri = $uri;
        $file->mime_type = $image->getMimeType();
        $file->title = $details['title'];
        $file->description = $details['description'];
        $file->thumbnail = 0;
        $file->active = 1;

        $user->files()->save($file);

        if (Cache::get($user->id)['user']->id == $user->id &&
            isset(Cache::get($user->id)['text'])
        ) {
            $file->speechTexts()->attach(Cache::get($user->id)['text']->id);
        }

        return $file;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function receivedText(User $user)
    {
        if (isset(Cache::get($user->id)['text'])) {
            return true;
        }
        return false;
    }
}
