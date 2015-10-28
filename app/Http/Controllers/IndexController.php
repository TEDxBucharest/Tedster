<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use

Imagine\Image\Point;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    protected $fb;
    protected $storagePath;

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

        $newPictureName = sprintf('profile_%s.jpg', $this->getProfileId());
        $savePath = $this->storagePath . $newPictureName;
        // $newPicture->save($savePath, $options);

        $newPicture->show('jpg', $options);
    }

    protected function getProfilePicture()
    {
        try {
            $response = $this->fb->get('/me/picture?type=large&redirect=false');

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
        try {
            $response = $this->fb->get('/me');

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;

        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        return $response->getGraphObject()->getProperty('id');
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

        // $options = array(
        //     'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
        //     'resolution-x' => 72,
        //     'resolution-y' => 72,
        //     'jpeg_quality' => 100,
        // );

        // return $imagine->open($pictureUrl)->show('jpg', $options);
    }
}
