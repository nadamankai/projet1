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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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

    #[Route('/personne/delete/{id}', name: 'delete_personne')]
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



    #[Route("/personne/add", name: 'add_personne')]
    public function add(EntityManagerInterface $manager): Response
    {
        $personne=new Personne();
$form = $this->createForm(PersonneType::class,$personne);
$form->remove('updatedAt');
$form->remove('createdAt');
        return $this->render('personne/personne.html.twig',[ 'form' => $form->createView() ]);
    }


}
