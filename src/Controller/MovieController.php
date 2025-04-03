<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Movie;
use App\Controller\Reponse;
use App\Form\MovieType;

class MovieController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private $apiKey = 'c3fa1c43c0dc02bc905e415968a469ef';

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
        $apiKey = $this->apiKey; 

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

    // Formater les données des films
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





// Définition de la route qui capte un ID dans l'URL et associe cette route à la méthode 'show'
#[Route('/movies/{id}', name: 'movie_detail')]
public function show(int $id, Request $request, EntityManagerInterface $entityManager ): Response
{
    // Appel de la fonction pour récupérer les données d'un film à partir de l'ID passé dans l'URL
    $movie = $this->getMovieDataFromApi($id);

    // Si le film n'est pas trouvé (ou s'il y a une erreur), on redirige l'utilisateur vers la page d'accueil
    if (!$movie) {
        return $this->redirectToRoute('homepage');
    }

    // Appel de la fonction pour récupérer les crédits (acteurs, réalisateurs, etc.) du film
    $credits = $this->getMovieCreditsFromApi($id);

    // Appel de la fonction pour récupérer les films similaires à celui demandé
    $similarMovies = $this->getSimilarMoviesFromApi($id);

    // Ajout des données en formulaire TEMP
    // Instance de l'entité Movie 
    $movieInfo = new Movie();
    // On ajoute les valeurs du films
    $movieInfo->setTitleMovie($movie['title']);
    $movieInfo->setIdTmdb($movie['id']);

    //dd($movieInfo);
    // Créer un formulaire
    $movieType = $this->createForm(MovieType::class, $movieInfo);
    // 
    $movieType->handleRequest($request);
    // Préparer l'envoie
    $entityManager->persist($movieInfo);
    // Envoyé en BDD
    $entityManager->flush();

    // Générer la vue 
    $movieTypeView= $movieType->createView();


    // Rendu du template 'movie/detail.html.twig' avec les données du film, les crédits et les films similaires
    // Ces données sont passées au template Twig sous forme de variables
    return $this->render('movie/detail.html.twig', [
        'movie' => $movie,  // Les données du film
        'credits' => $credits,  // Les crédits (acteurs, réalisateurs, etc.)
        'similarMovies' => $similarMovies,  // Les films similaires
        'movieForm' => $movieTypeView, // Formulaire d'ajout en base de données
    ]);
}

// Fonction pour récupérer les films similaires depuis l'API
private function getSimilarMoviesFromApi(int $id)
{
    $apiKey = $this->apiKey;
    
    // Construction de l'URL pour récupérer les films similaires, incluant l'ID du film
    $url = "https://api.themoviedb.org/3/movie/{$id}/similar?api_key={$apiKey}&language=fr-FR";

    try {
        // Envoi de la requête GET à l'API pour obtenir les films similaires
        $response = $this->httpClient->request('GET', $url);
        
        // Conversion de la réponse en tableau associatif
        $similarMovies = $response->toArray();

        // Vérification si des films similaires existent dans la réponse
        if (empty($similarMovies['results'])) {
            // Si aucun film similaire n'est trouvé, lancer une exception
            throw new \Exception('Films similaires non trouvés.');
        }

        // Retourner la liste des films similaires récupérés
        return $similarMovies['results'];

    } catch (\Exception $e) {
        // En cas d'erreur (par exemple, problème avec l'API), afficher un message d'erreur
        $this->addFlash('error', 'Erreur lors de la récupération des films similaires : ' . $e->getMessage());
        
        // Retourner un tableau vide si une erreur se produit
        return [];  // Retourne un tableau vide en cas d'erreur
    }
}

// Récupérer les données du film depuis l'API en utilisant l'ID
private function getMovieDataFromApi(int $id)
{
    $apiKey = $this->apiKey;
    
    // Construction de l'URL pour récupérer les informations du film par son ID
    $url = "https://api.themoviedb.org/3/movie/{$id}?api_key={$apiKey}&language=fr-FR";

    try {
        // Envoi de la requête GET à l'API pour obtenir les données du film
        $response = $this->httpClient->request('GET', $url);
        
        // Conversion de la réponse en tableau associatif
        $movie = $response->toArray();

        // Vérification si les données du film sont présentes
        if (empty($movie)) {
            // Si aucune donnée de film n'est trouvée, lancer une exception
            throw new \Exception('Film non trouvé.');
        }

        // Retourner les données du film récupérées
        return $movie;

    } catch (\Exception $e) {
        // En cas d'erreur (par exemple, problème avec l'API), afficher un message d'erreur
        $this->addFlash('error', 'Erreur lors de la récupération du film : ' . $e->getMessage());
        
        // Retourner null si une erreur se produit
        return null;
    }
}

// Récupérer les crédits (acteurs, réalisateurs, etc.) du film depuis l'API en utilisant l'ID
private function getMovieCreditsFromApi(int $id)
{
    $apiKey = $this->apiKey;
    
    // Construction de l'URL pour récupérer les crédits du film par son ID
    $url = "https://api.themoviedb.org/3/movie/{$id}/credits?api_key={$apiKey}&language=fr-FR";

    try {
        // Envoi de la requête GET à l'API pour obtenir les crédits du film
        $response = $this->httpClient->request('GET', $url);
        
        // Conversion de la réponse en tableau associatif
        $credits = $response->toArray();

        // Vérification si des crédits sont présents dans la réponse
        if (empty($credits)) {
            // Si aucun crédit n'est trouvé, lancer une exception
            throw new \Exception('Crédits non trouvés.');
        }

        // Retourner les crédits récupérés
        return $credits;

    } catch (\Exception $e) {
        // En cas d'erreur (par exemple, problème avec l'API), afficher un message d'erreur
        $this->addFlash('error', 'Erreur lors de la récupération des crédits : ' . $e->getMessage());
        
        // Retourner null si une erreur se produit
        return null;
    }
}

// EN COURS
#[Route('/movie/search', name: 'movie_search')]
public function searchMovie(Request $request): Response
{
    $apiKey = $this->apiKey;

    // Récupérer le titre depuis la requête GET (champ "search" du formulaire)
    $title = $request->query->get('search');
    //dump($title); 
    // Vérifier si le champ "search" est vide
    if (!$title) {
        $this->addFlash('error', 'Veuillez entrer un titre pour effectuer une recherche.');
        return $this->redirectToRoute('app_home'); // Redirection vers une autre page si nécessaire
    }

    // Construire l'URL pour récupérer un film selon son titre
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query={$title}&language=fr-FR";

    try {
        // Envoi de la requête GET à l'API pour obtenir les films
        $response = $this->httpClient->request('GET', $url);

        // Conversion de la réponse en tableau associatif
        $movies = $response->toArray()['results'] ?? [];

        // Si aucun film n'est trouvé
        if (empty($movies)) {
            $this->addFlash('warning', 'Aucun film trouvé pour votre recherche.');
            return $this->redirectToRoute('movie_search');
        }

        // Passer les résultats à une vue
        return $this->render('movie/search.html.twig', [
            'movies' => $movies,
            'search' => $title,
        ]);

    } catch (\Exception $e) {
        // En cas d'erreur, ajouter un flash message
        $this->addFlash('error', 'Erreur lors de la récupération des films : ' . $e->getMessage());

        return $this->redirectToRoute('app_home');
    }
}



#[Route('/movie/add', name: 'movie_add')]
public function addMovie(EntityManagerInterface $entityManager, int $id): Response
{

    // Instance de l'entité Movie 
    //$movie = new Movie();
    // On ajoute les valeurs du films
    //$movie->setTitle("Running Man");
    // Préparer l'envoie
    //$entityManager->persist($movie);
    // Envoyé en BDD
    //$entityManager->flush();

    // Vérifier la présence de l'id du film
    if (!$id) {
        return new Response("Aucun ID fourni.", Response::HTTP_BAD_REQUEST);
    }

    // Appel de la fonction pour récupérer les données d'un film à partir de l'ID passé dans l'URL
    $movieFile = $this->getMovieDataFromApi($id);
    
    // Instance de l'entité Movie 
    $movie = new Movie();

    // On ajoute les valeurs du films
    $movie->setTitle($movieFile['title']);
    $movie->setIdTmdb($movieFile['id']);

    // Préparer l'envoie
    $entityManager->persist($movie);

    // Envoyé en BDD
    $entityManager->flush();

    return new Response("Film ajouté dans la base.");
}



#[Route('/movie/addForm/{id}', name: 'movie_addForm')]
public function addMovieForm(int $id): Response
{

    // Instance de l'entité Movie 
    $movie = new Movie();
    // Appel de la fonction pour récupérer les données d'un film à partir de l'ID passé dans l'URL
    $movieInfo = $this->getMovieDataFromApi($id);

    if (!$movieInfo || !isset($movieInfo['title'], $movieInfo['id'])) {
        throw $this->createNotFoundException('Film introuvable.');
    }

    // On ajoute les valeurs du films
    $movie->setTitle($movieInfo['title']);
    $movie->setIdTmdb($movieInfo['id']);

    // Créer un formulaire
    $movieType = $this->createForm(MovieType::class, $movie);

    // Générer la vue 
    $movieTypeView= $movieType->createView();

    // Rendu du template 

    return $this->render('movie/addForm.twig', [
        'movieForm' => $movieTypeView
    ]);
}




}
