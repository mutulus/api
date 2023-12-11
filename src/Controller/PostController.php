<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    #[Route('/api/posts', name: 'api_post_index', methods: ['GET'])]
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
        $postsJson = $serializer->serialize($posts, 'json');
        // Construire la réponse http
//        $reponse = new Response();
//        $reponse->setStatusCode(Response::HTTP_OK);
//        $reponse->headers->set('content-type', 'application/json');
//        $reponse->setContent($postsJson);
//        return $reponse;
        return new Response($postsJson,Response::HTTP_OK,
            ['content-type'=> 'application/json']
        );
    }
    #[Route('/api/posts/{id}',name: 'api_post_show',methods: ['GET'])]
    public function show(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        // Rechercher tous les posts dans le BDD
        $post = $postRepository->find('id');

        // Sérialiser le tableau de posts en json
        $postJson = $serializer->serialize($post, 'json');

        return new Response($postJson,Response::HTTP_OK,
            ['content-type'=> 'application/json']
        );
    }
}
