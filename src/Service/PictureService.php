<?php


namespace App\Service;

use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PictureService
{
    private $params;
 
    // params permet d'aller chercher des informations via le 'parameters' dans services.yaml
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
 
    public function upload(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        // on donne un nouveau nom à l'image
        $file = md5(uniqid(rand(), true)) . '.webp';
 
        // on récupère les infos de l'image (largeur, hauteur, ...)
        $pictureInfos = getimagesize($picture);
 
        if($pictureInfos === false) {
            throw new Exception('Format d\'image incorrect');
        }
 
        // On vérifie le format de l'image
        // On récupère une image dans un variable pour pouvoir la manipuler
        switch($pictureInfos['mime']) {
            case 'image/png':
                $pictureSource = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $pictureSource = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $pictureSource = imagecreatefromwebp($picture);
                break;
            default:
                throw new Exception('Format d\'image incorrect');
        }
 
        // On recadre l'image
        // On récupère les dimensions
        $imageWidth = $pictureInfos[0];
        $imageHeight = $pictureInfos[1];
 
        // On vérifie l'orientation
        // <=> : comparaison donc width < height, width = height et width > height
        switch($imageWidth <=> $imageHeight) {
            case -1: // portrait = width < height
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = ($imageHeight - $squareSize) / 2;
                break;
            case 0: // carré = width = height
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = 0;
                break;
            case 1: // paysage = width > height
                $squareSize = $imageHeight;
                $srcX = ($imageWidth - $squareSize) / 2;
                $srcY = 0;
                break;
        }
 
        // on crée une nouvelle image "vierge"
        $resizedPicture = \imagecreatetruecolor($width, $height);
 
        imagecopyresampled($resizedPicture, $pictureSource, 0, 0, $srcX, $srcY, $width, $height, $squareSize, $squareSize);
 
        $path = $this->params->get('images_directory') . $folder;
 
        // on crée le dossier de destination s'il n'existe pas
        if(!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/', 0755, true);
        }
 
        // On stocke l'image recadrée
        imagewebp($resizedPicture, $path . '/mini/' . $width . 'x' . $height . '-' . $file);
 
        $picture->move($path . '/', $file);
 
        return $file;
    }
 
    public function delete(string $file, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if($file !== 'default.webp') {
            $success = false;
 
            $path = $this->params->get('images_directory') . $folder;
 
            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $file;
 
            if(file_exists($mini)) {
                unlink($mini);
                $success = true;
            }
 
            $original = $path . '/' . $file;
 
            if(file_exists($original)) {
                unlink($mini);
                $success = true;
            }
 
            return $success;
        }
 
        return false;
    }


    // private $images_directory;
    // private $slugger;

    // public function __construct($images_directory, SluggerInterface $slugger)
    // {
    //     $this->images_directory = $images_directory;
    //     $this->slugger = $slugger;
    // }

    // public function upload(UploadedFile $file)
    // {
    //     $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //     $safeFilename = $this->slugger->slug($originalFilename);
    //     $newfilename = round(microtime(true)) . '.' . end($safeFilename);
    //     $fileName = $newfilename . '-' . uniqid() . '.' . $file->guessExtension();

    //     try {
    //         $file->move($this->getImages_directory(), $fileName);
    //     } catch (FileException $e) {
    //         // ... handle exception if something happens during file upload
    //     }

    //     return $fileName;
    // }

    // public function getImages_directory()
    // {
    //     return $this->images_directory;
    // }
}
