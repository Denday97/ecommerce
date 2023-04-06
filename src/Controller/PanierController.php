<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier', name:'panier_')]
class PanierController extends AbstractController
{ 
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository, $stripeSK): Response
    {
        // on recupère la session
        $panier = $session->get('panier', []);

        // on fabrique les données
        $dataPanier =[];
        $total = 0;

        foreach($panier as $id => $quantite){
            $product = $productsRepository->find($id);
            $dataPanier[] = [
                'produit'=>$product,
                'quantite'=> $quantite
            ];
            $total += $product->getPrice() * $quantite;

        }
        return $this->render('panier/index.html.twig', compact('dataPanier', 'total'));
    }

    #[Route('/ajouter/{id}', name:'add')]
    public function add(Products $product, SessionInterface $session)
    {
        // on récupère le panier actuel
        $panier = $session->get('panier', []);
        $id = $product->getId();

        if(!empty($panier[$id])){
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        //on sauvegarde dans la session
        $session->set('panier', $panier);

        return $this->redirectToRoute('panier_index');

    }
    #[Route('/retirer{id}', name:'remove')]
    public function remove(Products $product, SessionInterface $session)
    {
        // on récupère le panier actuel
        $panier = $session->get('panier', []);
        $id = $product->getId();

        if(!empty($panier[$id])){
            $panier[$id]--;
        } else {
            unset($panier[$id]);
        }

        //on sauvegarde dans la session
        $session->set('panier', $panier);

        return $this->redirectToRoute('panier_index');

    }
    #[Route('/supprimer/{id}', name:'delete')]
    public function delete(Products $product, SessionInterface $session)
    {
        // on récupère le panier actuel
        $panier = $session->get('panier', []);
        $id = $product->getId();

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        //on sauvegarde dans la session
        $session->set('panier', $panier);

        return $this->redirectToRoute('panier_index');

    }
    #[Route('/supprimer', name:'delete_all')]
    public function deletAll(SessionInterface $session)
    {
        $session->remove('panier');
        
        return $this->redirectToRoute('panier_index');
    }
}
