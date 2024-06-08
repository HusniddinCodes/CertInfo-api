<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Api\IdentifiersExtractorInterface;
use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Closure;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class SearchMultiFieldsFilter extends AbstractFilter implements SearchFilterInterface
{
    use SearchFilterTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        IriConverterInterface $iriConverter,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?IdentifiersExtractorInterface $identifiersExtractor = null,
        ?NameConverterInterface $nameConverter = null,
        public string $searchParameterName = 'search',
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);

        $this->iriConverter = $iriConverter;
        $this->identifiersExtractor = $identifiersExtractor;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    protected function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    /**
     * {@inheritDoc}
     */
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            null === $value
            || $property !== $this->searchParameterName
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $ors = [];
        $count = 0;

        foreach (($this->getProperties() ?? []) as $prop => $caseSensitive) {
            $filter = $this->generatePropertyOrWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $prop,
                $value,
                $resourceClass,
                $count,
                $caseSensitive ?? false,
            );

            if (null === $filter) {
                continue;
            }

            [$expr, $exprParams] = $filter;
            $ors[] = $expr;

            $queryBuilder->setParameter($exprParams[1], $exprParams[0]);

            ++$count;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$ors));
    }

    protected function generatePropertyOrWhere(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $property,
        string $value,
        string $resourceClass,
        int $key,
        bool $caseSensitive = false,
    ): ?array {
        if (
            !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass, true)
        ) {
            return null;
        }

        $field = $property;
        $associations = [];

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field, $associations] = $this->addJoinsForNestedProperty(
                $property,
                $alias,
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                Join::INNER_JOIN,
            );
        }

        $metadata = $this->getNestedMetadata($resourceClass, $associations);

        if (
            'id' === $field
            || !$metadata->hasField($field)
        ) {
            return null;
        }

        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = ':' . $queryNameGenerator->generateParameterName($field);
        $aliasedField = sprintf('%s.%s', $alias, $field);
        $keyValueParameter = sprintf('%s_%s', $valueParameter, $key);

        return [
            $queryBuilder->expr()->like(
                $wrapCase($aliasedField),
                $wrapCase((string) $queryBuilder->expr()->concat("'%'", $keyValueParameter, "'%'")),
            ),
            [$caseSensitive ? $value : strtolower($value), $keyValueParameter],
        ];
    }

    protected function createWrapCase(bool $caseSensitive): Closure
    {
        return static function (string $expr) use ($caseSensitive): string {
            if ($caseSensitive) {
                return $expr;
            }

            return sprintf('LOWER(%s)', $expr);
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(string $doctrineType): string
    {
        return 'string';
    }

    public function getDescription(string $resourceClass): array
    {
        $props = $this->getProperties();

        if (null === $props) {
            throw new InvalidArgumentException('Properties must be specified');
        }

        return [
            $this->searchParameterName => [
                'property' => implode(', ', array_keys($props)),
                'type' => 'string',
                'required' => false,
                'description' => 'search by different parameters.',
            ],
        ];
    }
}