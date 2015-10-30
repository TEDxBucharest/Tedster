<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    protected $fb;
    protected $storagePath;
    protected $userId;

    public function __construct()
    {
        $this->fb = new Facebook([
            'app_id'                => env('FACEBOOK_APP_ID'),
            'app_secret'            => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);
        $this->fb->setDefaultAccessToken($_SESSION['facebook_access_token']);

        $this->storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
    }

    public function welcome()
    {
        $pictureUrl = $this->getProfilePicture();
        $newPicture = $this->addOverlay($pictureUrl);

        $options = [
            'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => 72,
            'resolution-y' => 72,
            'jpeg_quality' => 100,
        ];

        $newPicture->save($this->getPicturePath($this->getProfileId()), $options);

        return view('welcome', ['userId' => $this->getProfileId()]);
    }

    public function upload(Request $request)
    {
        if (!file_exists($this->getPicturePath())) {
            return;
        }

        $response = $this->uploadPicture($this->getPicturePath(), $request->get('description'));
        $photoId = $response->getGraphNode()->getProperty('id');

        return redirect(sprintf('https://www.facebook.com/photo.php?fbid=%s&makeprofile=1', $photoId));
    }

    protected function getProfilePicture()
    {
        try {
            $response = $this->fb->get('/me/picture?type=large&redirect=false&width=400');

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;

        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        return $response->getGraphObject()->getProperty('url');
    }

    protected function getProfileId()
    {
        if (empty($this->userId)) {
            try {
                $response = $this->fb->get('/me');

            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;

            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $this->userId = $response->getGraphObject()->getProperty('id');
        }

        return $this->userId;
    }

    protected function addOverlay($pictureUrl)
    {
        $imagine = new Imagine();

        $picture = $imagine->open($pictureUrl);
        $overlay = $imagine->open($this->storagePath . env('FACEBOOK_OVERLAY'));

        $x = $picture->getSize()->getWidth() - $overlay->getSize()->getWidth();
        $y = $picture->getSize()->getHeight() - $overlay->getSize()->getHeight();

        $picture->paste($overlay, new Point($x, $y));

        return $picture;
    }

    protected function uploadPicture($path, $message = '')
    {
        $data = [
            'source' => $this->fb->fileToUpload($path),
            'message' => $message,
        ];

        return $this->fb->post('/me/photos', $data);
    }

    protected function getPicturePath()
    {
        return $this->storagePath . sprintf('profile_%s.jpg', $this->getProfileId());
    }
}
