<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'projects')]
    public function index(): Response
    {
        return $this->render('project/index.html.twig');
    }

    #[Route('/admin/projects', name: 'projectsAdmin')]
    public function adminIndex(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/adminList.html.twig', [
            'projects' => $projectRepository->findAll()
        ]);
    }

    #[Route('/admin/projects/create', name: 'projectsCreate')]
    public function adminCreate(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $infoImg = $form['img']->getData();
            if (!empty($infoImg)) {
                $extensionImg = $infoImg->guessExtension();
                $nomImg = time() . '.' . $extensionImg;
                $project->setImg($nomImg);
                $infoImg->move($this->getParameter('project_img_dir'), $nomImg);
            }
            $manager = $managerRegistry->getManager();
            $manager->persist($project);
            $manager->flush();
            return $this->redirectToRoute('projectsAdmin');
        }
        return $this->render(
            'project/create.html.twig',
            [
                'projectForm' => $form->createView()
            ]
        );
    }
}
