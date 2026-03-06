<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Objet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom de l'objet",
            ])
            ->add('descriptionHistorique', TextareaType::class, [
                'label' => 'Description historique',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('epoque', TextType::class, [
                'label' => 'Époque',
                'required' => false,
            ])
            ->add('origine', TextType::class, [
                'label' => 'Origine',
                'required' => false,
            ])
            ->add('materiaux', TextType::class, [
                'label' => 'Matériaux',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nomCategorie',
                'label' => 'Catégorie',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objet::class,
        ]);
    }
}
