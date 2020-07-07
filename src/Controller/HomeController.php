<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;

class HomeController extends AbstractController {



        /**
         * @Route("/", name="homepage")
         *
         * @return void
         */
        public function home(){
            return $this->render(
                'home.html.twig'
            );
            
        }

        /**
         * @Route("/tableaux", name="tableau")
         *
         * @return void
         */
        public function tableau(Request $request, PaginatorInterface $paginator){

            $repo = $this->getDoctrine()->getRepository(Upload::class);
            $upload = $repo->findAll();


            $uploads = $paginator->paginate(
                $upload, // Requête contenant les données à paginer (ici nos articles)
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                3 // Nombre de résultats par page
            );
        

            return $this->render(
                'tableau.html.twig', [
                    'upload' => $upload,
                    'uploads' => $uploads
                ]
                
            );
        }

        /**
        * @Route("/ajouter", name="ajouter")
        * 
        * @return void
        */
        public function ajouter(Request $request, EntityManagerInterface $manager){


            $upload = new Upload();
            $form = $this->createForm(UploadType::class, $upload);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){
                $file = $upload->getName();
                $fileName = 'img/tableau/' . md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $upload->setName($fileName);
                $manager->persist($upload);
                $manager->flush();

                $message = 'Le tableau a bien été ajouté';
                $this->addFlash('ajouter', $message);

                return $this->redirectToRoute('tableau');
            }

        
            
            return $this->render(
                'ajouter.html.twig',
                array(
                    'form' => $form->createView()
                )
                
            );
        }

/**
* @Route("/tableaux/delete/{id}", name="tableau_delete")
* @return void
*
* @Security("is_granted('ROLE_USER')")
*/
    public function delete($id, Upload $element)
    {
        $repo = $this->getDoctrine()->getManager();
        $element = $repo->getRepository(Upload::class)->find($id);

    

        if( isset($_POST['confirm'])){

            $repo->remove($element);
            $repo->flush();

            $message = 'Le tableau a bien été supprimé';
            $this->addFlash('delete', $message);

            return $this->redirectToRoute('tableau');
        }

    

        return $this->render('delete.html.twig' ,[
            'image'=> $element
        ]);
    }


}



?>