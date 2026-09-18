<?php declare(strict_types=1);

namespace PhpParser;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;

class NameContext {
    /** @var null|Name Current namespace */
    protected ?Name $namespace;

    /** @var Name[][] Map of format [aliasType => [aliasName => originalName]] */
    protected array $aliases = [];

    /** @var Name[][] Same as $aliases but preserving original case */
    protected array $origAliases = [];

    /** @var ErrorHandler Error handler */
    protected ErrorHandler $errorHandler;

    /**
     * Create a name context.
     *
     * @param ErrorHandler $errorHandler Error handling used to report errors
     */
    public function __construct(ErrorHandler $errorHandler) {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Start a new namespace.
     *
     * This also resets the alias table.
     *
     * @param Name|null $namespace Null is the global namespace
     */
    public function startNamespace(?Name $namespace = null): void {
        $this->namespace = $namespace;
        $this->origAliases = $this->aliases = [
            Stmt\Use_::TYPE_NORMAL   => [],
            Stmt\Use_::TYPE_FUNCTION => [],
            Stmt\Use_::TYPE_CONSTANT => [],
        ];
    }

    /**
     * Add an alias / import.
     *
     * @param Name $name Original name
     * @param string $aliasName Aliased name
     * @param Stmt\Use_::TYPE_* $type One of Stmt\Use_::TYPE_*
     * @param array<string, mixed> $errorAttrs Attributes to use to report an error
     */
    public function addAlias(Name $name, string $aliasName, int $type, array $errorAttrs = []): void {
        if ($type === Stmt\Use_::TYPE_CONSTANT) {
            $aliasLookupName = $aliasName;
        } else {
            $aliasLookupName = strtolower($aliasName);
        }

        if (isset($this->aliases[$type][$aliasLookupName])) {
            $typeStringMap = [
                Stmt\Use_::TYPE_NORMAL   => '',
                Stmt\Use_::TYPE_FUNCTION => 'function ',
                Stmt\Use_::TYPE_CONSTANT => 'const ',
            ];

            $this->errorHandler->handleError(new Error(
                sprintf(
                    'Cannot use %s%s as %s because the name is already in use',
                    $typeStringMap[$type], $name, $aliasName
                ),
                $errorAttrs
            ));
            return;
        }

        $this->aliases[$type][$aliasLookupName] = $name;
        $this->origAliases[$type][$aliasName] = $name;
    }

    /**
     * Get current namespace.
     *
     * @return null|Name Namespace (or null if global namespace)
     */
    public function getNamespace(): ?Name {
        return $this->namespace;
    }

    /**
     * Get resolved name.
     *
     * @param Name $name Name to resolve
     * @param Stmt\Use_::TYPE_* $type One of Stmt\Use_::TYPE_{FUNCTION|CONSTANT}
     *
     * @return null|Name Resolved name, or null if static resolution is not possible
     */
    public function getResolvedName(Name $name, int $type): ?Name {
        if ($type === Stmt\Use_::TYPE_NORMAL && $name->isSpecialClassName()) {
            if (!$name->isUnqualified()) {
                $this->errorHandler->handleError(new Error(
                    sprintf("'\\%s' is an invalid class name", $name->toString()),
                    $name->getAttributes()
                ));
            }
            return $name;
        }

        if ($name->isFullyQualified()) {
            return $name;
        }

        if (null !== $resolvedName = $this->resolveAlias($name, $type)) {
            return $resolvedName;
        }

        if ($type !== Stmt\Use_::TYPE_NORMAL && $name->isUnqualified()) {
            if (null === $this->namespace) {
                return new FullyQualified($name, $name->getAttributes());
            }


            return null;
        }


        return FullyQualified::concat($this->namespace, $name, $name->getAttributes());
    }

    /**
     * Get resolved class name.
     *
     * @param Name $name Class ame to resolve
     *
     * @return Name Resolved name
     */
    public function getResolvedClassName(Name $name): Name {
        return $this->getResolvedName($name, Stmt\Use_::TYPE_NORMAL);
    }

    /**
     * Get possible ways of writing a fully qualified name (e.g., by making use of aliases).
     *
     * @param string $name Fully-qualified name (without leading namespace separator)
     * @param Stmt\Use_::TYPE_* $type One of Stmt\Use_::TYPE_*
     *
     * @return Name[] Possible representations of the name
     */
    public function getPossibleNames(string $name, int $type): array {
        $lcName = strtolower($name);

        if ($type === Stmt\Use_::TYPE_NORMAL) {
            if ($lcName === "self" || $lcName === "parent" || $lcName === "static") {
                return [new Name($name)];
            }
        }

        $possibleNames = [new FullyQualified($name)];

        if (null !== $nsRelativeName = $this->getNamespaceRelativeName($name, $lcName, $type)) {
            if (null === $this->resolveAlias($nsRelativeName, $type)) {
                $possibleNames[] = $nsRelativeName;
            }
        }

        foreach ($this->origAliases[Stmt\Use_::TYPE_NORMAL] as $alias => $orig) {
            $lcOrig = $orig->toLowerString();
            if (0 === strpos($lcName, $lcOrig . '\\')) {
                $possibleNames[] = new Name($alias . substr($name, strlen($lcOrig)));
            }
        }

        foreach ($this->origAliases[$type] as $alias => $orig) {
            if ($type === Stmt\Use_::TYPE_CONSTANT) {
                $normalizedOrig = $this->normalizeConstName($orig->toString());
                if ($normalizedOrig === $this->normalizeConstName($name)) {
                    $possibleNames[] = new Name($alias);
                }
            } else {
                if ($orig->toLowerString() === $lcName) {
                    $possibleNames[] = new Name($alias);
                }
            }
        }

        return $possibleNames;
    }

    /**
     * Get shortest representation of this fully-qualified name.
     *
     * @param string $name Fully-qualified name (without leading namespace separator)
     * @param Stmt\Use_::TYPE_* $type One of Stmt\Use_::TYPE_*
     *
     * @return Name Shortest representation
     */
    public function getShortName(string $name, int $type): Name {
        $possibleNames = $this->getPossibleNames($name, $type);


        $shortestName = null;
        $shortestLength = \INF;
        foreach ($possibleNames as $possibleName) {
            $length = strlen($possibleName->toCodeString());
            if ($length < $shortestLength) {
                $shortestName = $possibleName;
                $shortestLength = $length;
            }
        }

        return $shortestName;
    }

    private function resolveAlias(Name $name, int $type): ?FullyQualified {
        $firstPart = $name->getFirst();

        if ($name->isQualified()) {
            $checkName = strtolower($firstPart);
            if (isset($this->aliases[Stmt\Use_::TYPE_NORMAL][$checkName])) {
                $alias = $this->aliases[Stmt\Use_::TYPE_NORMAL][$checkName];
                return FullyQualified::concat($alias, $name->slice(1), $name->getAttributes());
            }
        } elseif ($name->isUnqualified()) {
            $checkName = $type === Stmt\Use_::TYPE_CONSTANT ? $firstPart : strtolower($firstPart);
            if (isset($this->aliases[$type][$checkName])) {
                return new FullyQualified($this->aliases[$type][$checkName], $name->getAttributes());
            }
        }

        return null;
    }

    private function getNamespaceRelativeName(string $name, string $lcName, int $type): ?Name {
        if (null === $this->namespace) {
            return new Name($name);
        }

        if ($type === Stmt\Use_::TYPE_CONSTANT) {
            if ($lcName === "true" || $lcName === "false" || $lcName === "null") {
                return new Name($name);
            }
        }

        $namespacePrefix = strtolower($this->namespace . '\\');
        if (0 === strpos($lcName, $namespacePrefix)) {
            return new Name(substr($name, strlen($namespacePrefix)));
        }

        return null;
    }

    private function normalizeConstName(string $name): string {
        $nsSep = strrpos($name, '\\');
        if (false === $nsSep) {
            return $name;
        }

        $ns = substr($name, 0, $nsSep);
        $shortName = substr($name, $nsSep + 1);
        return strtolower($ns) . '\\' . $shortName;
    }
}
