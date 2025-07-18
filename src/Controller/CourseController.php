<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\UserCourseStatus;
use App\Repository\CourseRepository;
use App\Repository\TagRepository;
use App\Repository\UserCourseStatusRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    #[Route('/courses', name: 'app_courses_index', methods: ['GET'])]
    public function index(
        CourseRepository $courseRepository,
        PaginatorInterface $paginator,
        Request $request,
        UserCourseStatusRepository $userCourseStatusRepository,
        TagRepository $tagRepository
    ): Response {
        // Récupérer tous les tags
        $tags = $tagRepository->findAllOrderedByName();
        $selectedTag = $request->query->get('tag');

        // Créer la requête de base
        $queryBuilder = $courseRepository->createQueryBuilder('c');

        // Filtrer par tag si un tag est sélectionné
        if ($selectedTag) {
            $queryBuilder->join('c.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $selectedTag);
        }

        $query = $queryBuilder->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Récupérer l'utilisateur actuel si connecté
        /** @var User|null $user */
        $user = $this->getUser();

        $userCourseStatuses = [];
        if ($user) {
            $userCourseStatuses = $userCourseStatusRepository->findBy(["user" => $user]);
            // Convertir les statuts en un tableau associatif pour un accès facile dans Twig
            $userCourseStatuses = array_reduce($userCourseStatuses, function ($carry, $status) {
                $carry[$status->getCourse()->getId()] = $status;
                return $carry;
            }, []);
        }

        return $this->render('course/index.html.twig', [
            'pagination' => $pagination,
            'userCourseStatuses' => $userCourseStatuses,
            'page' => $request->query->getInt('page', 1),
            'tags' => $tags,
            'selectedTag' => $selectedTag,
        ]);
    }

    #[Route('/courses/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }
}
