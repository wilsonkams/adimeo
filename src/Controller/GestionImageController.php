<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionImageController extends AbstractController
{
    /**
     * @Route("/listImages", name="affichImage")
     */
    public function affichImage(): Response
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->findAll();

        return $this->render('gestion_image/index.html.twig', [
            'controller_name' => 'GestionImageController',
            'post' => $post
        ]);
    }

    /**
     * @Route("/ajout_image", name="ajoutImage")
     */
    public function ajoutImage(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setDate(new \DateTime());

            if ($post->getImage() !== null) {
                $file = $form->get('image')->getData();
                $fileName =  uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans lequel le fichier va être charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $post->setImage($fileName);
            }



            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($post); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return $this->redirectToRoute('affichImage');
        }
        return $this->render('gestion_image/ajout.html.twig', [
            'controller_name' => 'GestionImageController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modification_image/{id}", name="editImage")
     */
    public function editImage(Post $post, Request $request): Response
    {
        $oldImage = $post->getImage();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedDate(new \DateTime());

            if ($post->getImage() !== null && $post->getImage() !== $oldImage) {
                $file = $form->get('image')->getData();
                $fileName =  uniqid(). '.' .$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans lequel le fichier va être charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $post->setImage($fileName);
            }else {
                $post->setImage($oldPicture);
            }


            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($post); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return $this->redirectToRoute('affichImage');
        }
        return $this->render('gestion_image/modifier.html.twig', [
            'controller_name' => 'GestionImageController',
            'post' => $post,
            'form' => $form->createView()
        ]);
    }
}