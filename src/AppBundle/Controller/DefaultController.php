<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\post;
use AppBundle\Entity\author;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        
        //$posts = $this->getDoctrine()->getRepository('AppBundle:post')->findAll();
        $repository = $this->getDoctrine()->getRepository('AppBundle:post');
        $query = $repository->createQueryBuilder('p')->orderBy('p.id','DESC')->getQuery();
        $posts = $query->getResult();

        return $this->render('default/index.html.twig',['posts' => $posts]);
    }

    /**
    * @Route("/create", name="index.create")
    */
    public function create(){

        $authors = $this->getDoctrine()->getRepository('AppBundle:author')->findAll();

        return $this->render('default/create.html.twig',['authors' => $authors]);
    }

    /**
    * @Route("/create/store", name="index.store")
    */
    public function store(Request $request){

        $post = new post();
        $post->setTitle($request->request->get('title') );
        $post->setContent($request->request->get('content') );
        
        $author = $this->getDoctrine()->getRepository('AppBundle:author')->find($request->request->get('author_id') );
        $post->setAuthor($author );

        $em = $this->getDoctrine()->getManager();
        $em->persist($post );
        $em->flush();

        return $this->RedirectToRoute('index');
    }

    /**
    * @Route("/edit/{id}", name="index.edit")
    */

    public function edit($id){

        $post = $this->getDoctrine()->getRepository('AppBundle:post')->find($id);
        $authors = $this->getDoctrine()->getRepository('AppBundle:author')->findAll();

        return $this->render('default/edit.html.twig',['post' => $post , 'authors' => $authors]);
    }

    /**
    * @Route("/edit/{id}/update", name="index.update")
    */

    public function update($id , Request $request){

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:post')->find($id);

        $errors = $this->formValidation($request);
        if( $errors == '' ){
            
            if(!$post) throw ("No existe el post con ID: ".$id);

            $author = $em->getRepository('AppBundle:author')->find( $request->request->get('author_id') );

            $post->setTitle( $request->request->get('title') );
            $post->setContent( $request->request->get('content') );
            $post->setAuthor($author);

            $em->flush();

            $this->get('session')->getFlashBag()->add('alert-msj','Publicación Actualizada');
            $this->get('session')->getFlashBag()->add('alert-type','alert-success');

        }else{

            $this->get('session')->getFlashBag()->add('alert-msj',$errors);
            $this->get('session')->getFlashBag()->add('alert-type','alert-danger');
        }

        return $this->RedirectToRoute('index.edit',['id' => $post->getId()] );

    }

    private function formValidation(Request $request){

        $errors = '';
        if( $request->request->get('title') == '' ) $errors = $errors. "El titulo esta vacio \n";
        if( $request->request->get('content') == '' ) $errors = $errors. "El contenido esta vacio \n";
        
        return $errors;
    }

}
