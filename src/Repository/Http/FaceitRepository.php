<?php

namespace App\Repository\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FaceitRepository
{
    private string $Token;

    function __construct()
    {
        $this->Token = "fd42fbca-4c35-4414-95ec-45b41e47c4ed";
    }

    public function GetFaceitPlayerDetailByPseudo(string $pseudo) : string
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://open.faceit.com/data/v4/players?nickname='.$pseudo,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$this->Token,
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}