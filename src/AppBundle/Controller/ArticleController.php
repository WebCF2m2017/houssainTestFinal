<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Article controller.
 *
 * @Route("admin/article")
 */
class ArticleController extends Controller
{
    /**
     * Lists all article entities.
     *
     * @Route("/", name="admin_article_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')->findAll();

        return $this->render('article/index.html.twig', array(
            'articles' => $articles,
        ));
    }

    /**
     * Creates a new article entity.
     *
     * @Route("/new", name="admin_article_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $article = new Article();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            $form = $this->createForm('AppBundle\Form\ArticleType', $article);
        else
            $form = $this->createForm('AppBundle\Form\ArticleUserType', $article);


        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            {
                $user = $em->getRepository('AppBundle:FosUser')->find($this->getUser()->getId());
                $article->setFosUser($user);
            }
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('article/new.html.twig', array(
            'article' => $article,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a article entity.
     *
     * @Route("/{id}", name="admin_article_show")
     * @Method("GET")
     */
    public function showAction(Article $article)
    {
        $deleteForm = $this->createDeleteForm($article);

        return $this->render('article/show.html.twig', array(
            'article' => $article,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing article entity.
     *
     * @Route("/{id}/edit", name="admin_article_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Article $article)
    {
        $idUser = $this->getUser()->getId();
        $idArticleOwner = $article->getFosUser()->getId();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            if ($idArticleOwner != $idUser)
            {
                $this->addFlash("error", "Impossible de modifier un article qui ne vous appartient pas!");
                return $this->redirectToRoute('admin_article_index');
            }

        }

        $deleteForm = $this->createDeleteForm($article);
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            $editForm = $this->createForm('AppBundle\Form\ArticleType', $article);
        else
            $editForm = $this->createForm('AppBundle\Form\ArticleUserType', $article);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('article/edit.html.twig', array(
            'article' => $article,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a article entity.
     *
     * @Route("/{id}", name="admin_article_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Article $article, $id)
    {


        $idUser = $this->getUser()->getId();
        $idArticleOwner = $article->getFosUser()->getId();


        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
        {
            if ($idArticleOwner != $idUser) {
                $this->addFlash("error", "Impossible de supprimer un article qui ne vous appartient pas!");
                return $this->redirectToRoute('admin_article_index');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($article, $id);
        $em->flush();


        return $this->redirectToRoute('admin_article_index');
    }

    /**
     * Creates a form to delete a article entity.
     *
     * @param Article $article The article entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Article $article)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_article_delete', array('id' => $article->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
