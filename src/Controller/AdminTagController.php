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
    private const SUCCESS_MESSAGES = [
        'created' => 'Le tag a été créé avec succès !',
        'updated' => 'Le tag a été mis à jour avec succès !',
        'deleted' => 'Le tag a été supprimé avec succès !'
    ];

    private const ERROR_MESSAGES = [
        'delete_failed' => 'Impossible de supprimer le tag car il est utilisé par un ou plusieurs cours.',
        'csrf_invalid' => 'Token de sécurité invalide.'
    ];

    /**
     * Affiche la liste de tous les tags
     */
    #[Route('/', name: 'admin_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('admin/tag/index.html.twig', [
            'tags' => $tagRepository->findAllOrderedByName(),
        ]);
    }

    /**
     * Crée un nouveau tag
     */
    #[Route('/new', name: 'admin_tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TagRepository $tagRepository): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        //if ($form->isSubmitted() && $form->isValid()) {
        if ($request->isMethod('POST')){
            $entityManager->persist($tag);
            $entityManager->flush();
            
            // Invalider le cache des tags
            $tagRepository->invalidateCache();

            $this->addFlash('success', self::SUCCESS_MESSAGES['created']);
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('admin/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie un tag existant
     */
    #[Route('/{id}/edit', name: 'admin_tag_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $entityManager, TagRepository $tagRepository): Response
    {
        $form = $this->createForm(TagForm::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            // Invalider le cache des tags
            $tagRepository->invalidateCache();

            $this->addFlash('success', self::SUCCESS_MESSAGES['updated']);
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('admin/tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un tag
     */
    #[Route('/{id}', name: 'admin_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager, TagRepository $tagRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', self::ERROR_MESSAGES['csrf_invalid']);
            return $this->redirectToRoute('admin_tag_index');
        }

        try {
            $entityManager->remove($tag);
            $entityManager->flush();
            
            // Invalider le cache des tags
            $tagRepository->invalidateCache();
            
            $this->addFlash('success', self::SUCCESS_MESSAGES['deleted']);
        } catch (\Exception $e) {
            $this->addFlash('error', self::ERROR_MESSAGES['delete_failed']);
        }

        return $this->redirectToRoute('admin_tag_index');
    }
}
