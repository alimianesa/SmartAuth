<?php

namespace Alimianesa\SmartAuth\Jobs;

use Alimianesa\SmartAuth\AliveFile;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VideoFaceRecognitionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $video;
    protected $registerFile;
    protected $percent;
    protected $speechPercent;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $video
     */
    public function __construct(User $user , $video)
    {
        $this->video = $video;
        $this->user = $user;
        $this->percent = config('face-recognition.percent');
        $this->speechPercent = config('speech-recognition.percent');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO : refactor job
        // Find Files
        $files = $this->user->files()->with('tags')->get();

        // Find Registration Office Card Image
        $this->findImage($files, 'registration');

        // Validate Card Image and Video
        $faceRecognitionResponse = $this->validateCardImage($this->video , $this->registerFile);

        // Speech Recognition
        $speechValidation = $this->speechValidation($this->video);
        if (!$speechValidation) {
            // Send SMS
            $this->user->video_verified_at = null;
            $this->user->save();
            return;
        }

        // Video Recognition
        $validate = $this->faceRecognitionPercent(preg_split ("/,/", $faceRecognitionResponse));
        if (!$validate){
            // Send SMS
            $this->user->video_verified_at = null;
            $this->user->save();
            return;
        }

        // Complete Validation
        $this->user->video_verified_at = time();
        $this->user->save();
    }

    /**
     * @param AliveFile $video
     * @param AliveFile $registrationImage
     * @return bool|string|null
     */
    public function validateCardImage(AliveFile $video ,AliveFile $registrationImage)
    {
        if (!is_null($video) && !is_null($registrationImage)) {
            return shell_exec('cd python && venv/bin/python3 project-video-face-recognition.py '.
                "\"../storage/app/{$video->uri}\"" .' '.
                "\"../storage/app/{$registrationImage->uri}\"");
        }
        return false;
    }

    /**
     * @param $files
     * @param $key
     *
     */
    public function findImage($files , $key)
    {
        foreach ($files as $file) {
            foreach ($file->tags as $tag) {
                if ($tag->key == $key) {
                    $this->registerFile = $file;
                }
            }
        }
    }

    /**
     * @param $faceRecognitionResponse
     * @return bool
     */
    public function faceRecognitionPercent($faceRecognitionResponse):bool
    {
        if (!isset($faceRecognitionResponse[0])
            or !isset($faceRecognitionResponse[1])
            or $faceRecognitionResponse[1]<10
        ) {
            return false;
        }

        // Validate OpenCV Response with predefined percent
        $responsePercent = ((int) $faceRecognitionResponse[1])*100/((int) $faceRecognitionResponse[0]);
        if ($responsePercent >= $this->percent) {
            return true;
        }
        return false;
    }

    /**
     * @param AliveFile $video
     * @return bool
     */
    public function speechValidation(AliveFile $video) : bool
    {
        if (!is_null($video)) {
            // mp4 To wav
            $format = str_replace(".mp4", '.wav', $video->uri);
            $wavUri = str_replace('videos', 'voices', $format);
            shell_exec("ffmpeg -i storage/app/{$video->uri} -vn storage/app/{$wavUri}");

            // Convert Speech To Text
            $voiceToText = shell_exec('cd python && venv/bin/python3 project-video-voice-recognition.py '.
                "\"../storage/app/{$wavUri}\"");

            // Get assigned Text
            $text = $video->speechTexts()->first()->speech_text;
            similar_text($text, $voiceToText, $percent);

            // Validate
            if ($percent >= $this->speechPercent) {
                return true;
            }
            return false;
        }
        return false;
    }
}
