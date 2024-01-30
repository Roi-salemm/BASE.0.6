<?php

namespace App\Security\Voter;

use App\Entity\Products;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

//^^ Gestion des permissions plus fine que security.yaml

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $product): bool
    {
        //* Verrifications de si l'attribut est dans delete ou edit
        if(!in_array($attribute, [self::EDIT, self::DELETE])){
            return false;
        }
        if(!$product instanceof Products){
            return false;
        }
        return true;

        // return in_array($attribute, [self::EDIT, self::DELETE]) && $product instanceof Products;
    }

    protected function voteOnAttribute($attribute, $product, TokenInterface $token): bool
    {
        //* Récuperation de l'utilisateur a partir du token 
        $user = $token->getUser();

        if(!$user instanceof UserInterface) return false;


        //* Verif de si l'utilisateur est ADMIN 
        if($this->security->isGranted('ROLE_ADMIN')) return true;



        //* si il n'est pas ADMIN a t'il les permissions ?
        switch($attribute){
            case self::EDIT:
                // On vérifie si l'utilisateur peut éditer
                return $this->canEdit();
                break;
            case self::DELETE:
                // On vérifie si l'utilisateur peut supprimer
                return $this->canDelete();
                break;
        }
    }


    //* fonction de verrificaiton des role utilisatuer 
    private function canEdit(){
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }
    private function canDelete(){
        return $this->security->isGranted('ROLE_ADMIN');
    }
}