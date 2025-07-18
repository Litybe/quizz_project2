<?php

namespace App\Utils;

class StringUtils
{
    /**
     * Vérifie si une chaîne n'est ni null ni vide.
     *
     * @param string|null $str La chaîne à vérifier.
     * @return bool Renvoie true si la chaîne est null ou vide, sinon false.
     */
    public static function isStringEmpty(?string $str): bool
    {
        // Vérifie si la chaîne est null
        if ($str === null) {
            return true;
        }

        // Vérifie si la chaîne n'est pas vide et ne contient pas uniquement des espaces blancs
        return !(strlen(trim($str)) > 0);
    }
}