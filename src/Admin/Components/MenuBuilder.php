<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Doctrine\ORM\QueryBuilder;
use Forumify\Admin\Form\MenuItemType;
use Forumify\Core\Entity\MenuItem;
use Forumify\Core\Repository\MenuItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('MenuBuilder', '@Forumify/admin/components/menu_builder/menu_builder.html.twig')]
#[IsGranted('forumify.admin.settings.menu_builder.manage')]
class MenuBuilder extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public bool $isCreating = false;

    #[LiveProp]
    public ?MenuItem $parent = null;

    #[LiveProp]
    public ?MenuItem $selectedItem = null;

    public function __construct(
        private readonly MenuItemRepository $menuItemRepository
    ) {
    }

    /**
     * @return array<MenuItem>
     */
    public function getRoots(): array
    {
        return $this->menuItemRepository->getRoots();
    }

    public function isFormVisible(): bool
    {
        return $this->isCreating || $this->selectedItem !== null;
    }

    #[LiveAction]
    public function selectItem(#[LiveArg] ?int $itemId): void
    {
        $this->selectedItem = $itemId === null ? null : $this->menuItemRepository->find($itemId);
        $this->isCreating = false;
        $this->parent = null;

        $this->resetForm();
    }

    #[LiveAction]
    public function setCreating(#[LiveArg] ?int $parentId): void
    {
        $this->selectedItem = null;
        $this->isCreating = true;
        $this->parent = $parentId !== null
            ? $this->menuItemRepository->find($parentId)
            : null;

        $this->resetForm();
    }

    /**
     * @return FormInterface<array<string, mixed>|MenuItem|null>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(MenuItemType::class, $this->selectedItem, [
            'parent' => $this->parent,
        ]);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        /** @var MenuItem $menuItem */
        $menuItem = $this->getForm()->getData();
        $this->menuItemRepository->save($menuItem);

        $this->selectItem($this->isCreating ? $menuItem->getId() : null);
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $itemId): void
    {
        $item = $this->menuItemRepository->find($itemId);
        if ($item !== null) {
            $this->menuItemRepository->remove($item);
        }
    }

    #[LiveAction]
    public function reorderItem(#[LiveArg] int $itemId, #[LiveArg] string $direction): void
    {
        $item = $this->menuItemRepository->find($itemId);
        if ($item === null) {
            return;
        }

        $this->menuItemRepository->reorder($item, $direction, static function (QueryBuilder $qb) use ($item) {
            if ($item->getParent() !== null) {
                $qb->andWhere('e.parent = :parent')->setParameter('parent', $item->getParent());
            } else {
                $qb->andWhere('e.parent IS NULL');
            }
        });
    }

    #[LiveAction]
    public function manageACL(#[LiveArg] int $itemId): RedirectResponse
    {
        /** @var MenuItem|null $menuItem */
        $menuItem = $this->menuItemRepository->find($itemId);
        if ($menuItem === null) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('forumify_admin_acl', (array)$menuItem->getACLParameters());
    }
}
