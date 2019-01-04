<?php

namespace Aa\AkeneoImport\ImportCommand;

use Aa\AkeneoImport\ImportCommand\Media\CreateProductMediaFile;

abstract class BaseUpdateProductCommand extends BaseCommand implements CommandsAwareInterface
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $associations = [];

    /**
     * @var CommandInterface[]
     */
    private $extraCommands = [];

    public function toArray(): array
    {
        if (count($this->values) > 0) {
            $this->data = array_merge($this->data, ['values' => $this->values]);
        }

        if (count($this->associations) > 0) {
            $this->data = array_merge($this->data, ['associations' => $this->associations]);
        }

        return $this->data;
    }

    public function addExtraCommand(CommandInterface $command)
    {
        $this->extraCommands[] = $command;
    }

    /**
     * @inheritdoc
     */
    public function getExtraCommands(): array
    {
        return $this->extraCommands;
    }

    public function addValue(string $attributeCode, $data, ?string $locale = null, ?string $scope = null): self
    {
        $this->values[$attributeCode][] = [
            'data' => $data,
            'locale' => $locale,
            'scope' => $scope,
        ];

        return $this;
    }

    public function addImageValue(string $attributeCode, string $fileName, ?string $locale = null, ?string $scope = null): self
    {
        return $this->addMediaValue($attributeCode, $fileName, $locale, $scope);
    }

    public function addFileValue(string $attributeCode, string $fileName, ?string $locale = null, ?string $scope = null): self
    {
        return $this->addMediaValue($attributeCode, $fileName, $locale, $scope);
    }

    private function addMediaValue(string $attributeCode, string $fileName, ?string $locale = null, ?string $scope = null): self
    {
        $mediaCommand = new CreateProductMediaFile($fileName, $this->data['identifier'], $attributeCode, $scope, $locale);
        $this->addExtraCommand($mediaCommand);

        return $this;

    }

    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @example:
     *
     * [
     *      'PACK' => [
     *          'groups' => ['a', 'b', 'c'],
     *          'products' => ['g'],
     *          'product_models' => ['e', 'f', 'k'],
     *      ],
     * ]
     */
    public function setAssociations(array $associations): self
    {
        $this->associations = $associations;

        return $this;
    }

    public function addAssociatedGroups(string $associationTypeCode, array $groupCodes): self
    {
        return $this->addAssociations('groups', $associationTypeCode, $groupCodes);
    }

    public function addAssociatedProducts(string $associationTypeCode, array $productIdentifiers): self
    {
        return $this->addAssociations('products', $associationTypeCode, $productIdentifiers);
    }

    public function addAssociatedProductModels(string $associationTypeCode, array $productModelCodes): self
    {
        return $this->addAssociations('product_models', $associationTypeCode, $productModelCodes);
    }

    private function addAssociations(string $associationEntity, string $associationTypeCode, array $identifiers): self
    {
        $existingIdentifiers = $this->associations[$associationTypeCode][$associationEntity] ?? [];

        $this->associations[$associationTypeCode][$associationEntity] = array_merge($existingIdentifiers, $identifiers);

        return $this;
    }
}