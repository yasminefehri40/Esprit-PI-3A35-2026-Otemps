<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\Objet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeMedia', ChoiceType::class, [
                'label' => 'Type de média',
                'choices' => [
                    'Vidéo' => 'video',
                    'Audio' => 'audio',
                    'Image' => 'image',
                ],
            ])
            ->add('lienFichier', TextType::class, [
                'label' => 'Lien du fichier (URL ou chemin)',
            ])
            ->add('objet', EntityType::class, [
                'class' => Objet::class,
                'choice_label' => 'nom',
                'label' => 'Objet associé',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
