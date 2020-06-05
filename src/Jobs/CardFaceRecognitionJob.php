<?php

namespace Alimianesa\SmartAuth\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CardFaceRecognitionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $cardFile;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO : refactor job
        // Get Files
        $files = $this->user->files()->with('tags')->get();

        // Find Card Image
        $this->findImage($files, 'card');
        $cardImage = $this->cardFile;

        // Find Registration Office Card Image
        $this->findImage($files, 'registration');
        $registrationImage = $this->cardFile;

        // Validate Card Images With OpenCV
        if (!$this->validateCardImage($cardImage, $registrationImage)) {
            $this->user->card_verified_at = null;
            $this->user->save();
            return;
        }

        // Set card_verified_at
        $this->user->card_verified_at = time();
        $this->user->save();
    }

    /**
     * @param $files
     * @param $key
     *
     */
    public function findImage($files , $key)
    {
        if (!is_null($this->cardFile)) {
            $this->cardFile = null;
        }
        foreach ($files as $file) {
            foreach ($file->tags as $tag) {
                if ($tag->key == $key) {
                    $this->cardFile = $file;
                }
            }
        }
    }

    /**
     * @param $cardImage
     * @param $registrationImage
     * @return bool|string|null
     */
    public function validateCardImage($cardImage , $registrationImage)
    {
        if (!is_null($cardImage) && !is_null($registrationImage)) {
            return shell_exec('cd ../vendor/alimianesa/smartauth/src/python && python3 project-face-recognition.py '.
                "\"../../../../../storage/app/{$cardImage->uri}\"" .' '.
                "\"../../../../../storage/app/{$registrationImage->uri}\"") == "True\n" ? true : false;
        }
        return false;
    }
}
