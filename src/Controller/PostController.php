<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class PostController extends AbstractController
{
    #[Route('/posts', name: 'api_post_index', methods: ['GET'])]
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
    #[Route('/posts/{id}',name: 'api_post_show',requirements: ['id'=>'\d+'],methods: ['GET'])]
    public function show(PostRepository $postRepository, SerializerInterface $serializer,int $id): Response
    {
        // Rechercher tous les posts dans le BDD
        $post = $postRepository->find($id);

        // Sérialiser le tableau de posts en json
        $postJson = $serializer->serialize($post, 'json');

        return new Response($postJson,Response::HTTP_OK,
            ['content-type'=> 'application/json']
        );
    }
    #[Route('/posts',name: 'api_post_create',methods: ['POST'])]
    public function create(\Symfony\Component\HttpFoundation\Request $request,SerializerInterface $serializer,EntityManagerInterface $entityManager): Response
    {
        // Récupérer le body de la requete http au format json
        $bodyrequest=$request->getContent();
        $post=$serializer->deserialize($bodyrequest,Post::class,'json');
        $post->setCreatedAt(new \DateTime());
        $entityManager->persist($post);
        $entityManager->flush();
        // Génerer la réponse
        $postJson=$serializer->serialize($post,'json');
        return new Response($postJson,Response::HTTP_CREATED,["content-type"=>"application/json"]);

    }
    #[Route('/posts/{id}',name: 'api_post_delete',requirements: ['id'=>'\d+'],methods: ['DELETE'])]
    public function delete(\Symfony\Component\HttpFoundation\Request $request,SerializerInterface $serializer,EntityManagerInterface $entityManager,int $id): Response
    {
        $post=$entityManager->find(Post::class,$id);
        $entityManager->remove($post);
        $entityManager->flush();
        // Génerer la réponse

        return new Response(null,204,["content-type"=>"application/json"]);

    }
    #[Route('/posts/{id}',name: 'api_post_update',requirements: ['id'=>'\d+'],methods: ['PUT'])]
    public function update(\Symfony\Component\HttpFoundation\Request $request,SerializerInterface $serializer,EntityManagerInterface $entityManager,int $id): Response
    {
        $bodyRequest=$request->getContent();
        $post=$entityManager->find(Post::class,$id);
        $serializer->deserialize($bodyRequest,Post::class,'json',['Object_to_populate'=>$post]);
        $entityManager->flush();
        // Génerer la réponse
        return new Response(null,Response::HTTP_NO_CONTENT);

    }
}
