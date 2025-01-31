<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Recherche;
use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $repo1 = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repo1->findAll();
        $articles = $paginator->paginate(
            $repo->findAll(),
            $request->query->getInt('page', 1), /*page number*/
             15 /*limit per page*/
        );

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles'=> $articles,
            'categories'=> $categories
        ]);
    }

    /**
     * @Route("/blog1/{id}", name="blog_show")
     */
    public function show($id, Request $request, EntityManagerInterface $manager)
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $repo1 = $this->getDoctrine()->getRepository(Article::class);
        $article = $repo->find($id);
        $articles = $repo1->findAll();
        
        $commentaire = new Commentaire();
        $form = $this->createFormBuilder($commentaire)
                     ->add('author')
                     ->add('content')
                     ->getForm();

                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()) {
                    if(!$commentaire->getId()) {
                        $commentaire->setCreatedAt(new \DateTime());
                    }
                    $commentaire->setArticle($article);
                    $manager->persist($commentaire);
                    $manager->flush();
            
                        return $this->redirectToRoute('blog_show',  ['id' => $article->getId()
                        ]);
                }
               
        return $this->render('blog/show.html.twig', [
            'article'=> $article,
            'articles'=> $articles,
            'commentaire'=> $commentaire,
            'formCommentaire' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cat", name="cat")
     */
    public function cat()
    {
        $repo = $this->getDoctrine()->getRepository(Category::class);
        $repo1 = $this->getDoctrine()->getRepository(Article::class);

        $categories = $repo->findAll();
        $articles = $repo1->findAll();

        return $this->render('blog/cat.html.twig', [
            'controller_name' => 'BlogController',
            'categories'=> $categories,
            'articles'=> $articles
        ]);
    }

    /**
     * @Route("/comment", name="comment")
     */
    public function comment()
    {
        $repo = $this->getDoctrine()->getRepository(Commentaire::class);

        $commentaires = $repo->findAll();

        return $this->render('blog/comment.html.twig', [
            'controller_name' => 'BlogController',
            'commentaires'=> $commentaires
        ]);
    }

    /**
     * @Route("/uti", name="uti")
     */
    public function uti()
    {
        $repo = $this->getDoctrine()->getRepository(Utilisateur::class);

        $utilisateur = $repo->findAll();

        return $this->render('blog/uti.html.twig', [
            'controller_name' => 'BlogController',
            'utilisateurs'=> $utilisateur
        ]);
    }

    /**
     * @route("/cat/{id}", name="blog_catArt")
     */
    public function CatArt($id) {

        $repo = $this->getDoctrine()->getRepository(Category::class);
        $repo1 = $this->getDoctrine()->getRepository(article::class);
        $repo2 = $this->getDoctrine()->getRepository(article::class);
        $category = $repo->find($id);
        $article = $repo1->find($id);
        $articles = $repo2->findAll();
           
        return $this->render('blog/catArt.html.twig', [
            'controller_name' => 'BlogController',
            'category'=> $category,
            'article'=> $article,
            'articles'=> $articles
        ]);

    }

    /**
     * @route("/cat/{id}/art", name="blog_art")
     */
    public function art($id) {

        $repo = $this->getDoctrine()->getRepository(Category::class);
        $repo1 = $this->getDoctrine()->getRepository(Article::class);
        $repo2 = $this->getDoctrine()->getRepository(Article::class);
    
        $category = $repo->find($id);
        $article = $repo1->find($id);
        $articles = $repo2->findAll();
           
        return $this->render('blog/art.html.twig', [
            'controller_name' => 'BlogController',
            'article'=> $article,
            'articles'=> $articles,
            'category'=> $category
        ]);

    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }

     /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Category $category = null, Request $request, EntityManagerInterface $manager)
    {
        if(!$article) {
            $article = new Article();
            $category = new Category();
        }
       
        $form = $this->createFormBuilder($article)
                     ->add('title')
                     ->add('content')
                     ->add('image')
                     ->add('category', EntityType::class, [
                        'class' => Category::class,
                        "choice_label" => 'title'
                    ])
                     ->getForm();
        
            $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid()) {
            if(!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }
            $manager->persist($article);
            //$manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()
            ]);
        }

        return $this->render("blog/create.html.twig", [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("recherche", name="recherche")
     */
    public function rechercher(Request $request)
    {
           
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll();
        $repo1 = $this->getDoctrine()->getRepository(Category::class);
        $category = $repo1->findAll();

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
       
        $recherches = new Recherche();
        $form = $this->createFormBuilder($recherches)
                     ->add('categoryArticle')
                     ->add('titreArticle')
                     ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                //dd($request);
                $id = $form['categoryArticle']->getData();
                $key = $form['titreArticle']->getData();
                $recherches = $this->getDoctrine()->getRepository(Article::class)->findOneByRechercher($id, $key);  
               
                //$this->addFlash('danger', 'Catégorie non trouvée');
                //dd($recherche->getCategoryArticle());
                
            }

        return $this->render('blog/recherche.html.twig', [
            'controller_name' => 'BlogController',
            'formRecherche' => $form->createView(),
            'recherches' =>  $recherches,
            'articles' => $articles,
            'categories' => $categories,
            'category' => $category
        ]);
    }

    /**
     * @Route("/catSport", name="catSport")
     * @Route("/catReligion", name="catReligion")
     * @Route("/catPolitique", name="catPolitique")
     */
    public function catFilter(Request $request)
    {
        $currentRoute = $request->attributes->get('_route');
       
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll();
        
        $title="";
        if($currentRoute == "catSport")
            $title = 'sport';
        else if($currentRoute == "catReligion")
            $title = 'religion';
        else if($currentRoute == "catPolitique")
            $title = "politique";
        $categories = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findByCatSport($title);
        return $this->render('blog/catFilter.html.twig', [
            'controller_name' => 'BlogController',
            'categories'=> $categories,
            'articles'=> $articles,
            'title' => $title
        ]);
    }

}
