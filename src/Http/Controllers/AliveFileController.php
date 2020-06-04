<?php

namespace Alimianesa\SmartAuth\Http\Controllers;

use Alive2212\LaravelSmartResponse\ResponseModel;
use Alive2212\LaravelSmartResponse\SmartResponse;
use Alive2212\LaravelSmartRestful\BaseController;
use Alimianesa\SmartAuth\AliveFile;
use Alimianesa\SmartAuth\Jobs\CardFaceRecognitionJob;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AliveFileController extends BaseController
{
    protected $validateFileRequest =  [
        'card_serial'  => 'required',
        'card_image'   => 'required|mimes:jpg,jpeg,png|max:20000',
        'sign_image'   => 'required|mimes:jpg,jpeg,png|max:20000',
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function store(Request $request)
    {
        $response = new ResponseModel();
        $request->validate($this->validateFileRequest);

        // Find User
        $user = auth()->user();

        // Save Card Serial number
        $user->card_serial = $request->card_serial;
        $user->save();

        // Card Image
        $card_image = $request->file('card_image');
        $details = [
            'size' => $card_image->getSize(),
            'title' => 'card',
            'description' => ' card image description',
        ];
        $cardUri = $this->imageUpload($card_image);
        $cardFile = $this->saveFile(
            $card_image,
            $cardUri ,
            $details,
            $user);

        // Attach to Card tag
        $cardFile->tags()->attach(2);

        // Sign Image
        $signImage = $request->file('sign_image');
        $details = [
            'size' => $card_image->getSize(),
            'title' => 'sign',
            'description' => ' sign image description',
        ];
        $signUri = $this->imageUpload($signImage);
        $signFile = $this->saveFile(
            $signImage,
            $signUri,
            $details,
            $user
        );

        // Attach to Sign tag
        $signFile->tags()->attach(3);

        // TODO : face recognition job
        CardFaceRecognitionJob::dispatchNow($user);

        $response->setMessage($this->getTrans(__FUNCTION__,'successful'));
        return SmartResponse::response($response);
    }

    /**
     * @param $image
     * @return string
     */
    public function imageUpload($image)
    {
        $imageName = time() . Str::random(16) . $image->getClientOriginalName();
        $uri = 'images/' . $imageName;
        Storage::disk('local')->putFileAs('images/', $image , $imageName);

        return $uri;
    }

    /**
     * @param $image
     * @param $uri
     * @param $details
     * @param User $user
     * @return AliveFile
     */
    public function saveFile($image , $uri , $details , User $user)
    {
        $file = new AliveFile;
        $file->uri = $uri;
        $file->size = $details['size'];
        $file->mime_type = $image->getMimeType();
        $file->title = $details['title'];
        $file->description = $details['description'];
        $file->thumbnail = 0;
        $file->active = 1;

        $user->files()->save($file);
        return $file;
    }


}
