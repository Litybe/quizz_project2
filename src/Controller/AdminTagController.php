<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagForm;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tags')]
#[IsGranted('ROLE_ADMIN')]
class AdminTagController extends AbstractController
{
    #[Route('/', name: 'admin_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('admin/tag/index.html.twig', [
            'tags' => $tagRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/new', name: 'admin_tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        //if ($form->isSubmitted() && $form->isValid()) {
        if ($request->isMethod('POST')){
            $entityManager->persist($tag);
            $entityManager->flush();

            //$this->addFlash('success', 'Le tag a été créé avec succès!');
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('admin/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_tag_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le tag a été mis à jour avec succès!');
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('admin/tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($tag);
                $entityManager->flush();
                $this->addFlash('success', 'Le tag a été supprimé avec succès!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de supprimer le tag car il est utilisé par un ou plusieurs cours.');
            }
        }

        return $this->redirectToRoute('admin_tag_index');
    }
}
