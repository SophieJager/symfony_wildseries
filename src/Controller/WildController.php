<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;

/**
 * Class WildController
 * @package App\Controller
 * @Route("/wild", name="wild_")
 */
class WildController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index() :Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if(!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }
        return $this->render('wild/index.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     * @Route("/show/{slug}", requirements={"slug" = "[a-z0-9-]+"}, name="show")
     */
    public function show(string $slug = '') :Response
    {
        if (!$slug) {
            throw $this->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = ucwords(str_replace('-',' ', $slug));
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title found in program\'s table.'
            );
        }
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program], ['number' => 'asc']);

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    /**
     * @param string $categoryName
     * @Route("/category/{categoryName}", name="show_category")
     * @return Response
     */
    public function showByCategory(string $categoryName) :Response
    {
        if (!$categoryName) {
            throw $this->createNotFoundException('No category name has been sent to find a program in program\'s table.');
        }
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category], ['id' => 'desc'], 3);

        if (!$programs) {
            throw $this->createNotFoundException(
                'No programs with '.$categoryName.' category found in program\'s table'
            );
        }

        return $this->render('wild/category.html.twig',[
            'programs' => $programs,
            'category' => $category,
        ]);
    }


    /**
     * @param int $seasonId
     * @Route("/season/{seasonId}", requirements={"seasonId": "\d+"}, name="show_season")
     * @return Response
     */
    public function showBySeason(int $seasonId) :Response
    {
        if (!$seasonId) {
            throw $this->createNotFoundException('No season id has been sent to find a season in season\'s table.');
        }
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['id' => $seasonId]);

        if (!$season) {
            throw $this->createNotFoundException(
                'No season with '.$seasonId.' id found in season\'s table'
            );
        }

        $program = $season -> getProgram();
        $episodes = $season -> getEpisodes();

        return $this->render('wild/season.html.twig',[
            'season' => $season,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }

    /**
     *
     * @Route("/episode/{id}", name="show_episode")
     * @param Episode $episode
     * @return Response
     */
    public function showEpisode(Episode $episode) :Response
    {
        $season = $episode->getSeason();
        $program = $season->getProgram();
        $unslugProgram = strtolower(str_replace(' ','-', $program->getTitle()));

        return $this->render('wild/episode.html.twig', [
            'episode'=>$episode,
            'program'=>$program,
            'unslug'=>$unslugProgram,
            'season'=>$season
        ]);
    }
}
