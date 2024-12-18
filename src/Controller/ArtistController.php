<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistController extends AbstractController
{

    private HttpClientInterface $httpClient;

    // Constructeur 
    public function __construct(HttpClientInterface $httpClient) 
    {
        $this->httpClient = $httpClient;
    }

    // Récupérer les artistes tendances de la semaine en francais
    private function fetchTrendingArtists():array 
    {
        $apiKey = 'c3fa1c43c0dc02bc905e415968a469ef'; // Clé api TMDB

        // Construction de la requête 
        $url = "https://api.themoviedb.org/3/trending/person/week?api_key={$apiKey}&language=fr-FR";

        // Envoyer le requête
        $response = $this->httpClient->request('GET', $url);

        // Gestion des erreurs
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des données depuis TMDB');
        }

        // Retourner les données sous forme de tableau associatif
        return $response->toArray(); 
    }


    // Formater les données pour ne récupérer que l'id les images des acteurs
    private function formatArtistData(array $data):array 
    {
        if(!isset($data['results']) || !is_array($data['results'])) {
            throw new \Exception('Les résultats de l\'API sont invalides ou vides');
        }

        // Retourner les informations premières des atristes
        return array_map(function($actor) {
            return [
                'id' => $actor['id'] ?? null, 
                'profile_path' => $actor['profile_path'] 
                    ? "https://image.tmdb.org/t/p/w500{$actor['profile_path']}" 
                    : null, 
                'name' => $actor['name'] ?? null,
                'popularity' => $actor['popularity'] ?? null,
            ];
        },$data['results']);
    }

    // Action de la page des artistes
    #[Route('/artists', name: 'app_artist')]
    public function index(): Response 
    {
        try {
            // Appeller l'api TMDB
            $apiData = $this->fetchTrendingArtists();
            // Formater les données
            $artists = $this->formatArtistData($apiData);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les artistes tendances : ' . $e->getMessage());
            $artists = [];
        }

        // Envoyer les données au templates
        return $this->render('artist/index.html.twig',[
            'artists' => $artists, 
        ]); 
    }





















    /*
    #[Route('/artist', name: 'app_artist')]
    public function index(): Response
    {
        return $this->render('artist/index.html.twig', [
            'controller_name' => 'ArtistController',
        ]);
    }
    */


}
