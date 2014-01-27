<?php

namespace EB\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/facebook")
 */
class FacebookController extends Controller
{
    public function getThumbnail($photos, $width = 180) {
        foreach($photos as $photo) {
            if ($photo['width'] === $width ) return $photo['source'];
        }
    }
    
    /**
     * @Route("/photos", name="eb_facebook_photos")
     */
    public function photoAction() {
        $fb = $this->get('fos_facebook.api');
        $fbPhotos = $fb->api('/me/photos?limit=50&type=uploaded&fields=images,id,source');

        $photos = array();
        foreach($fbPhotos['data'] as $photo) {
            $photos[] = array(
                'source'    => $photo['source'],
                'thumb'     => $this->getThumbnail($photo['images'])
            );
        }

        $response = new Response(json_encode($photos));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/albums", name="eb_facebook_albums")
     */
    public function albumsAction() {
        $fb = $this->get('fos_facebook.api');
        $fbAlbums = $fb->api('/me/albums?fields=photos.limit(1).fields(images),id,name&limit=100');

        $albums = array();
        foreach($fbAlbums['data'] as $album) {
            if (!isset($album['photos'])) continue;
            
            $albums[] = array(
                'id'    => $album['id'],
                'name'  => $album['name'],
                'thumb' => $this->getThumbnail($album['photos']['data'][0]['images']),
                'route' => $this->generateUrl('my_facebook_album_photos', array('albumId' => $album['id']))
            );
        }

        $response = new Response(json_encode($albums));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/albums/{albumId}", name="eb_facebook_album_photos")
     */
    public function albumPhotosAction($albumId) {
        $fb = $this->get('fos_facebook.api');
        $fbPhotos = $fb->api('/'.$albumId.'?fields=photos.limit(50).fields(images,source)');
        
        $photos = array();
        foreach($fbPhotos['photos']['data'] as $photo) {
            $photos[] = array(
                'source'    => $photo['source'],
                'thumb'     => $this->getThumbnail($photo['images'])
            );
        }
        
        $response = new Response(json_encode($photos));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
