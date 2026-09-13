<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Forumify\Core\Service\UploadTransaction;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use LogicException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<string|list<string>|null>
 */
class UploadType extends AbstractType
{
    private const array IMAGE_EXTENSIONS = ['apng', 'avif', 'bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'svg', 'webp'];

    /** @var array<string, FilesystemOperator> */
    private readonly array $storages;

    /**
     * @param iterable<FilesystemOperator> $storages
     */
    public function __construct(
        private readonly Packages $packages,
        private readonly UploadTransaction $transaction,
        private readonly TranslatorInterface $translator,
        #[AutowireIterator('flysystem.storage', 'storage')]
        iterable $storages,
    ) {
        $this->storages = iterator_to_array($storages);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['filesystem', 'asset_package']);
        $resolver->setDefaults([
            'multiple' => false,
            'file_constraints' => [],
            'accept' => null,
            'data_class' => null,
            'compound' => true,
            'error_bubbling' => false,
        ]);

        $resolver->setAllowedTypes('filesystem', 'string');
        $resolver->setAllowedTypes('asset_package', 'string');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('file_constraints', Constraint::class . '[]');
        $resolver->setAllowedTypes('accept', ['string', 'null']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fileOptions = [
            'label' => false,
            'required' => false,
            'multiple' => $options['multiple'],
            'error_bubbling' => true,
            'block_prefix' => 'upload_file',
        ];

        if ($options['file_constraints'] !== []) {
            $fileOptions['constraints'] = $options['multiple']
                ? [new All($options['file_constraints'])]
                : $options['file_constraints'];
        }

        $builder
            ->add('file', FileType::class, $fileOptions)
            ->add('removed', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->setDataMapper(new UploadDataMapper(
                $this->transaction,
                $this->getStorage($options['filesystem']),
                $options['multiple'],
            ));

        if ($options['required']) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();
                if (self::toPaths($form->getData()) === []) {
                    $form->addError(new FormError($this->translator->trans('file_upload.required')));
                }
            });
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $storage = $this->getStorage($options['filesystem']);

        $view->vars['multiple'] = $options['multiple'];
        $view->vars['accept'] = $options['accept'];
        $view->vars['files'] = array_map(
            fn (string $path): array => [
                'path' => $path,
                'name' => $this->displayName($path),
                'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                'url' => $this->packages->getUrl($path, $options['asset_package']),
                'image' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true),
                'size' => $this->formatSize($storage, $path),
            ],
            self::toPaths($form->getData()),
        );
    }

    /**
     * @return list<string>
     */
    public static function toPaths(mixed $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        $value = is_array($value) ? $value : [$value];

        return array_values(array_filter($value, static fn (mixed $v): bool => is_string($v) && $v !== ''));
    }

    private function getStorage(string $name): FilesystemOperator
    {
        return $this->storages[$name] ?? throw new LogicException("Filesystem \"$name\" does not exist.");
    }

    private function formatSize(FilesystemOperator $filesystem, string $path): ?string
    {
        try {
            $bytes = $filesystem->fileSize($path);
        } catch (FilesystemException) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit = $bytes > 0 ? (int)min(floor(log($bytes, 1024)), count($units) - 1) : 0;
        $size = $bytes / 1024 ** $unit;

        return round($size, $size < 10 && $unit > 0 ? 1 : 0) . ' ' . $units[$unit];
    }

    private function displayName(string $path): string
    {
        $name = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $name = preg_replace('/-[0-9a-f]{13}$/', '', $name) ?? $name;

        return $extension === '' ? $name : "$name.$extension";
    }
}
