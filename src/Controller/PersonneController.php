<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Form\PersonneType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Persisters\Collection\OneToManyPersister;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PersonneController extends AbstractController
{
    #[Route('/personne/select', name: 'app_personne')]
    public function index(EntityManagerInterface $doctrine): Response
    {
        $personne = $doctrine->getRepository(Personne::class);
        $tab = $personne->findAll();
        return $this->render('personne/index.html.twig', [
            'personne' => $tab,'ispaginated'=>false,
        ]);
    }

    #[Route('/personne/delete/{id?0}', name: 'delete_personne')]
    public function delete(Personne $id=null, EntityManagerInterface $manager): Response
    {
        // recuperer personne
        // si la personne existe => la supprimer et retourner un flash message de succes
        if($id){
 $manager->remove($id);
 $manager->flush();
 $this->addFlash('alert','la personne a ete supprimé avec succés');
       }
           // sinon la personne n'existe pas => retourner un flash message d'erreur
        else {
            $this->addFlash('erreur','la personne a supprimer n existe pas');
        }
        return $this->redirectToRoute('app_personne');

    }
    #[Route('/personne/update/{id}/{nom}/{firstname}/{age}', name: 'update_personne')]
    public function update(Personne $id=null,$nom,$firstname,$age , EntityManagerInterface $manager): Response
    {
        if(!$id){
            $this->addFlash('erreur','id inexistant');
        }
        else {
            $id->setNom($nom);
            $id->setFirstname($firstname);
            $id->setAge($age);
            $manager->persist($id);
            $manager->flush();
            $this->addFlash('alert','updated');

        }
        return $this->redirectToRoute('app_personne');
    }


        #[Route('/personne/findby/{page?1}/{nbre?12}', name: 'findpersonneby')]
    public function findby(EntityManagerInterface $doctrine,$page,$nbre): Response
    {
        $personne = $doctrine->getRepository(Personne::class);
        $nbrepersonne = $doctrine->getRepository(Personne::class)->count([]);
        $nbrepages =  ceil($nbrepersonne / $nbre) ;
        $tab = $personne->findBy([] ,[],$nbre,($page-1)*$nbre);
        return $this->render('personne/index.html.twig', [
            'personne' => $tab,'ispaginated'=>true ,
            'nbrePage'=> $nbrepages ,
            'page' => $page,
            'nbreparpage'=>$nbre ,
        ]);
    }


    #[Route('/personne/select/{id<\d+>}', name: 'app_personne2')]
    public function find( $id,EntityManagerInterface $doctrine): Response
    {
        $personne = $doctrine->getRepository(Personne::class);

        if(!$personne->find($id)){
            $this->addFlash('erreur',"la personne d'id $id n'existe pas " );
            return $this->redirectToRoute('app_personne');
        }
        else {
            $tab = $personne->find($id);
            return $this->render('personne/detail.html.twig', [
                'personne' => $tab,
            ]);
        }
    }
    #[Route('/personne/select2/{id<\d+>}', name: 'app_personne3')]
    public function find2(Personne $id =null): Response
    {
        if(!$id){
            $this->addFlash('erreur',"la personne d'id $id n'existe pas " );
            return $this->redirectToRoute('app_personne');
        }
        else {
            return $this->render('personne/detail.html.twig', [
                'personne' => $id,
            ]);
        }
    }
//    #[Route("/personne/add/{firstname?nada}/{name?mankai}/{age?20}", name: 'add_personne')]
//    public function add($firstname,$name,$age,EntityManagerInterface $manager): Response
//    {   $pers = new Personne();
//        $pers->setNom($name);
//        $pers->setFirstname($firstname);
//        $pers->setAge($age);
//        $manager->persist($pers);
//        $manager->flush();
//        return $this->render('personne/detail.html.twig',['personne'=>$pers]);
//    }
//    #[Route("/personne/remove/{id}", name: 'remove_personne')]
//    public function remove(Personne $id,EntityManagerInterface $manager): Response
//    {
//        $manager->remove($id);
//        $manager->flush();
//        return $this->render('personne/index.html.twig');
//    }



    #[Route("/personne/edit/{id?0}", name: 'edit_personne')]
    public function add(Personne $personne=null,EntityManagerInterface $manager,Request $req,SluggerInterface $slugger): Response
    {$new =false ;
        if(!$personne) {
            $new = true ;
        $personne = new Personne();
    }
        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('updatedAt');
        $form->remove('createdAt');
//dump($req);
        //mon formulaire va aller traiter la requete
        $form->handleRequest(($req));
//est ce que le formulaire a été soumis
        if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile $brochureFile */
                $photo = $form->get('photo')->getData();

                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $photo->move(
                            $this->getParameter('personne_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $personne->setImage($newFilename);
                }

            //si oui ,on va ajouter l'objet personne dans la base de donnée
//            dd($form->getData());
            $manager->persist($personne);
            $manager->flush();
            //rederiger vers la liste des personnes
            // afficher un message de succés
           if($new=true) {
               $this->addFlash('alert', "a ete ajoute avec succes");
           }else{ $this->addFlash('alert', "a ete mis a jour avec succes");}
            return $this->redirectToRoute('app_personne');
            //sinon on affiche notre formulaire

        } else {
            return $this->render('personne/personne.html.twig', ['form' => $form->createView()]);
        }

    }

}
