<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieController extends AbstractController
{
    private HttpClientInterface $httpClient;

    // Constructeur
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Récupérer les films par genre
    #[Route('/movies', name: 'app_movies')]
    public function index(): Response
    {
        // Liste des genres et leurs ID TMDB
        $genres = [
            'Action' => 28,
            'Adventure' => 12,
            'Animation' => 16,
            'Comedy' => 35,
            'Horror' => 27,
            'Documentary' => 99,
            'Drama' => 18
        ];

        // Clé API TMDB 
        $apiKey = 'c3fa1c43c0dc02bc905e415968a469ef'; // Clé api TMDB

        // Variables pour stocker les films par genre
        $actionMovies = [];
        $adventureMovies = [];
        $animationMovies = [];
        $comedyMovies = [];
        $horrorMovies = [];
        $documentaryMovies = [];
        $dramaMovies = [];

        // Récupérer les films par genre
        foreach ($genres as $genreName => $genreId) {
            $url = "https://api.themoviedb.org/3/discover/movie?api_key={$apiKey}&with_genres={$genreId}&language=fr-FR";

            try {
                $response = $this->httpClient->request('GET', $url);
                $data = $response->toArray();
                $formattedMovies = $this->formatMovies($data['results']);
                
                // Stocker les films formatés dans la variable correspondante
                switch ($genreName) {
                    case 'Action':
                        $actionMovies = $formattedMovies;
                        break;
                    case 'Adventure':
                        $adventureMovies = $formattedMovies;
                        break;
                    case 'Animation':
                        $animationMovies = $formattedMovies;
                        break;
                    case 'Comedy':
                        $comedyMovies = $formattedMovies;
                        break;
                    case 'Horror':
                        $horrorMovies = $formattedMovies;
                        break;
                    case 'Documentary':
                        $documentaryMovies = $formattedMovies;
                        break;
                    case 'Drama':
                        $dramaMovies = $formattedMovies;
                        break;
                }
            } catch (\Exception $e) {
                // En cas d'erreur, afficher un message d'erreur pour chaque genre
                $this->addFlash('error', "Erreur lors de la récupération des films pour le genre : {$genreName}");
            }
        }

        // Passer les films par genre au template
        return $this->render('movie/index.html.twig', [
            'actionMovies' => $actionMovies,
            'adventureMovies' => $adventureMovies,
            'animationMovies' => $animationMovies,
            'comedyMovies' => $comedyMovies,
            'horrorMovies' => $horrorMovies,
            'documentaryMovies' => $documentaryMovies,
            'dramaMovies' => $dramaMovies,
        ]);
    }

    // Méthode pour formater les données des films
    private function formatMovies(array $movies): array
    {
        return array_map(function ($movie) {
            return [
                'id' => $movie['id'] ?? null,
                'title' => $movie['title'] ?? 'Titre inconnu',
                'poster_path' => $movie['poster_path']
                    ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}"
                    : null,
                'popularity' => $movie['popularity'] ?? null,
            ];
        }, $movies);
    }
}
