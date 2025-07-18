<?php
namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;

class ImageUtils
{
    /**
     * Vérifie si une chaîne n'est ni null ni vide.
     *
     * @param string|null $str La chaîne à vérifier.
     * @return bool Renvoie true si la chaîne est null ou vide, sinon false.
     */
    /*public static function isStringEmpty(Request $request, int $index): string
    {
        $imageFile = $request->files->get("questions")[$index]["image"];
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
        }

        return $imageFile;
    }*/
}