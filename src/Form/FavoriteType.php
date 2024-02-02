<?php

    namespace App\Form;

    use App\Entity\Event;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class FavoriteType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('event', EntityType::class, [
                    'class' => Event::class,
                    'choice_label' => 'description', // ou un autre champ que vous souhaitez afficher
                    'label' => 'Choose an event to add to favorites',
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Add to Favorites',
                ]);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                // Configure your form options here
            ]);
        }
    }
