<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseForm;
use App\Repository\CourseRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/course')]
#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODO")'))]
class AdminCourseController extends AbstractController
{
    #[Route('/', name: 'admin_course_index', methods: ['GET'])]
    public function index(
        CourseRepository $courseRepository,
        PaginatorInterface $paginator,
        Request $request,
        TagRepository $tagRepository
    ): Response {
        $tags = $tagRepository->findAllOrderedByName();
        $selectedTag = $request->query->get('tag');

        $query = $courseRepository->createQueryBuilder('c')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/course/index.html.twig', [
            'pagination' => $pagination,
            'tags' => $tags,
            'selectedTag' => $selectedTag,
        ]);
    }

    #[Route('/new', name: 'admin_course_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $course = new Course();
        $form = $this->createForm(CourseForm::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('admin_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_course_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Course $course,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CourseForm::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_course_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Course $course,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
