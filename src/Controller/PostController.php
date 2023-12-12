<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Post;
use App\Repository\CategorieRepository;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use http\Env\Request;
use Nelmio\ApiDocBundle\Model\Model;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api')]
class PostController extends AbstractController
{

    #[Route('/posts', name: 'api_post_index', methods: ['GET'])]
    #[OA\Tag(name: 'Posts')]
    #[OA\Get(
        path: "/api/posts",
        description: "Permet de récupérer la liste des posts",
        summary: "Lister l'ensemble des posts",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des posts au format JSON",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new \Nelmio\ApiDocBundle\Annotation\Model(type: Post::class,groups: ['list_posts'])
                    )
                )
            )
        ]
    )]
    public function index(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        // Rechercher tous les posts dans le BDD
        $posts = $postRepository->findAll();
//         //Normalisation du tableau de posts
//        $postsArray=$normalizer->normalize($posts);
//         //Encoder en json des posts
//        $postsJson=json_encode($postsArray);
//        // Tout le processus est appelé sérialisation (normalisation + encode)
        //--------------------------------------------------------------------------
        // Sérialiser le tableau de posts en json
        $postsJson = $serializer->serialize($posts, 'json',['groups'=>'list_posts']);
        // Construire la réponse http
//        $reponse = new Response();
//        $reponse->setStatusCode(Response::HTTP_OK);
//        $reponse->headers->set('content-type', 'application/json');
//        $reponse->setContent($postsJson);
//        return $reponse;
        return new Response($postsJson, Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/posts/{id}', name: 'api_post_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Tag(name: 'Posts')]
    #[OA\Get(
        path: "/api/posts/{id}",
        description: "Permet de récupérer un post par son id",
        summary: "Récupérer un post",
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: "ID du post à rechercher",
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détail du post au format JSON",
                content: new OA\JsonContent(
                    ref: new \Nelmio\ApiDocBundle\Annotation\Model(type: Post::class,groups: ['show_post'])
                )
            )
        ]
    )]
    public function show(PostRepository $postRepository, SerializerInterface $serializer, int $id): Response
    {
        // Rechercher tous les posts dans le BDD
        $post = $postRepository->find($id);

        // Sérialiser le tableau de posts en json
        $postJson = $serializer->serialize($post, 'json',['groups'=>'show_post']);

        return new Response($postJson, Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/posts', name: 'api_post_create', methods: ['POST'])]
    #[OA\Tag(name: 'Posts')]
    public function create(\Symfony\Component\HttpFoundation\Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager,CategorieRepository $CatRepository): Response
    {
        // Récupérer le body de la requete http au format json
        $bodyrequest = $request->getContent();
        $post = $serializer->deserialize($bodyrequest, Post::class, 'json');
        /*
        $categorie=$CatRepository->find($request->query->get('categorie'));
        $categorie->addPost($post);
        $post->setCategorie($categorie);*/
        $post->setCreatedAt(new DateTime());
        $entityManager->persist($post);
        $entityManager->flush();
        // Génerer la réponse
        $postJson = $serializer->serialize($post, 'json');
        return new Response($postJson, Response::HTTP_CREATED, ["content-type" => "application/json"]);

    }

    #[Route('/posts/{id}', name: 'api_post_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Tag(name: 'Posts')]
    public function delete(\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->find(Post::class, $id);
        $entityManager->remove($post);
        $entityManager->flush();
        // Génerer la réponse

        return new Response(null, 204, ["content-type" => "application/json"]);

    }

    #[Route('/posts/{id}', name: 'api_post_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Tag(name: 'Posts')]
    public function update(\Symfony\Component\HttpFoundation\Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, int $id): Response
    {
        $bodyRequest = $request->getContent();
        $post = $entityManager->find(Post::class, $id);
        $serializer->deserialize($bodyRequest, Post::class, 'json', ['object_to_populate' => $post]);
        $entityManager->flush();
        // Génerer la réponse
        return new Response(null, Response::HTTP_NO_CONTENT);

    }

    #[Route('/posts/publies-apres', name: 'api_post_showAfterDate', methods: ['GET'])]
    #[OA\Tag(name: 'Posts')]
    public function showAfterDate(\Symfony\Component\HttpFoundation\Request $request, PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        // Récuperer la date dans la requête
        $date = $request->query->get('date');
        // Convertir la date en DateTime
        $date = new DateTime($date);
        $posts = $postRepository->findByDateTallerThan($date);
        $postsJson = $serializer->serialize($posts, 'json',['groups'=>'list_posts']);
        return new Response($postsJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }
}
