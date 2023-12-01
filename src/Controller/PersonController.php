<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;


#[Route('/person')]
class PersonController extends AbstractController
{
    #[Route('/', name: 'app_person_index', methods: ['GET'])]
    public function index(PersonRepository $personRepository): Response
    {
        return $this->render('person/index.html.twig', [
            'people' => $personRepository->findAll(),
        ]);
    }



    #[Route('/new', name: 'app_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PersonRepository $personRepository): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if (!$password) {
                $form->get('password')->addError(new FormError('Le mot de passe est obligatoire.'));
                return $this->renderForm('person/new.html.twig', [
                    'person' => $person,
                    'form' => $form,
                ])->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
            $person->setPassword($password);
            $personRepository->save($person, true);
    
            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('person/new.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }


    #[Route('/person/{id}', name: 'app_person_show')]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    #[ParamConverter('person', class: 'App\Entity\Person')]
    public function edit(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personRepository->save($person, true);

            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }



    #[Route('/person/delete/{id}', name: 'deletePersonne')]
public function delete(Request $request, int $id, EntityManagerInterface $entityManager, PersonRepository $repo): Response
{
    $personne = $repo->find($id);

    if (!$personne) {
        throw $this->createNotFoundException(
            'Aucune personne ne correspond à cette id ' . $id
        );
    }

    $entityManager->remove($personne);
    $entityManager->flush();

    return $this->redirectToRoute('app_person_index');
}



#[Route('/recherche', name: 'recherchePage')]
public function recherchePage(Request $request, PersonRepository $personRepository): Response
{
    $nomRechercher = $request->request->get('nomRechercher');

    if (!empty($nomRechercher)) {
        $listePersonnes = $personRepository->createQueryBuilder('p')
            ->where('p.nom like :nom')
            ->setParameter('nom', '%' . $nomRechercher . '%')
            ->getQuery()
            ->getResult();
    } else {
        $listePersonnes = [];
    }

    return $this->render('person/recherchePage.html.twig', [
        'listePersonnes' => $listePersonnes
    ]);
}
}
