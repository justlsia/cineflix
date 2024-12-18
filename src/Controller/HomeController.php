<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class HomeController extends AbstractController
{

    private HttpClientInterface $httpClient;


    // Constructeur 
    public function __construct(HttpClientInterface $httpClient) 
    {
        $this->httpClient = $httpClient;
    }

    // Récupérer les films tendances de la semaine en francais
    private function fetchTrendingMovies():array 
    {
        $apiKey = 'c3fa1c43c0dc02bc905e415968a469ef'; // Clé api TMDB

        // Construction de la requête 
        $url = "https://api.themoviedb.org/3/trending/movie/week?api_key=" . $apiKey . "&language=fr-FR";

        // Envoyer le requête
        $response = $this->httpClient->request('GET', $url);

        // Gestion des erreurs
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des données depuis TMDB');
        }

        // Retourner les données sous forme de tableau associatif
        return $response->toArray(); 
    }

    // Formater les données pour ne récupérer que l'id les images des films
    private function formatMovieData(array $data):array 
    {
        if(!isset($data['results']) || !is_array($data['results'])) {
            throw new \Exception('Les résultats de l\'API sont invalides ou vides');
        }

        // Retourner uniquement l'id et l'url de l'affiche
        return array_map(function($movie) {
            return [
                'id' => $movie['id'] ?? null, 
                'poster_path' => $movie['poster_path'] 
                    ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}" 
                    : null, 
                'title' => $movie['title'] ?? null,
                'popularity' => $movie['popularity'] ?? null,
            ];
        },$data['results']);
    }

    // Action de la page d'accueil
    #[Route('/home', name: 'app_home')]
    public function index(): Response 
    {
        try {
            // Appeller l'api TMDB
            $apiData = $this->fetchTrendingMovies();
            // Formater les données
            $movies = $this->formatMovieData($apiData);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les films tendances : ' . $e->getMessage());
            $movies = [];
        }

        // Envoyer les données au templates
        return $this->render('home/index.html.twig',[
            'movies' => $movies, 
        ]); 
    }






    // Afficher la fiche détaillée d'un film
    #[Route('/movie/{id}', name: 'movie_detail')]
    public function show(int $id, MovieRepository $movieRepository): Response
    {
        // Récupère le film selon l'ID
        $movie = $movieRepository->find($id);

        // Si le film n'existe pas
        if (!$movie) {
            throw $this->createNotFoundException('Film non trouvé.');
        }

        // Rendu de la vue des détails du film
        return $this->render('movie/detail.html.twig', [
            'movie' => $movie,
        ]);
    }



}
