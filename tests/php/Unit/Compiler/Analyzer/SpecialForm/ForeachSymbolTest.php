<?php

declare(strict_types=1);

namespace PhelTest\Unit\Compiler\Analyzer\SpecialForm;

use Phel\Compiler\Analyzer\Analyzer;
use Phel\Compiler\Analyzer\Ast\DoNode;
use Phel\Compiler\Analyzer\Ast\ForeachNode;
use Phel\Compiler\Analyzer\Ast\LetNode;
use Phel\Compiler\Analyzer\Ast\LocalVarNode;
use Phel\Compiler\Analyzer\Ast\TableNode;
use Phel\Compiler\Analyzer\Ast\VectorNode;
use Phel\Compiler\Analyzer\Environment\GlobalEnvironment;
use Phel\Compiler\Analyzer\Environment\NodeEnvironment;
use Phel\Compiler\Analyzer\TypeAnalyzer\SpecialForm\ForeachSymbol;
use Phel\Compiler\Exceptions\AbstractLocatedException;
use Phel\Lang\Collections\LinkedList\PersistentListInterface;
use Phel\Lang\Symbol;
use Phel\Lang\Table;
use Phel\Lang\TypeFactory;
use PHPUnit\Framework\TestCase;

final class ForeachSymbolTest extends TestCase
{
    public function testRequiresAtLeastTwoArg(): void
    {
        $this->expectException(AbstractLocatedException::class);
        $this->expectExceptionMessage("At least two arguments are required for 'foreach");

        // (foreach)
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
        ]);

        $this->analyze($list);
    }

    public function testFirstArgMustBeAVector(): void
    {
        $this->expectException(AbstractLocatedException::class);
        $this->expectExceptionMessage("First argument of 'foreach must be a vector.");

        // (foreach x)
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            Symbol::create('x'),
        ]);

        $this->analyze($list);
    }

    public function testArgForVectorCanNotBe1(): void
    {
        $this->expectException(AbstractLocatedException::class);
        $this->expectExceptionMessage("Vector of 'foreach must have exactly two or three elements.");

        // (foreach [x])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                Symbol::create('x'),
            ]),
        ]);

        $this->analyze($list);
    }

    public function testValueSymbolFromVectorWith2Args(): void
    {
        // (foreach [x []])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                Symbol::create('x'),
                TypeFactory::getInstance()->persistentVectorFromArray([]),
            ]),
            Symbol::create('x'),
        ]);

        $env = NodeEnvironment::empty();

        self::assertEquals(
            new ForeachNode(
                $env,
                new DoNode(
                    $env->withLocals([Symbol::create('x')]),
                    [],
                    new LocalVarNode($env->withLocals([Symbol::create('x')]), Symbol::create('x'))
                ),
                new VectorNode($env->withContext(NodeEnvironment::CONTEXT_EXPRESSION), []),
                Symbol::create('x')
            ),
            $this->analyze($list)
        );
    }

    public function testDeconstrutionWithTwoArgs(): void
    {
        // (foreach [[x] []])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                TypeFactory::getInstance()->persistentVectorFromArray([Symbol::create('x')]),
                TypeFactory::getInstance()->persistentVectorFromArray([]),
            ]),
            Symbol::create('x'),
        ]);

        $node = $this->analyze($list);
        self::assertInstanceOf(LetNode::class, $node->getBodyExpr());
    }

    public function testValueSymbolVectorWith3Args(): void
    {
        // (foreach [key value @{}])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                Symbol::create('key'),
                Symbol::create('value'),
                Table::empty(),
            ]),
            Symbol::create('key'),
        ]);

        $env = NodeEnvironment::empty();

        self::assertEquals(
            new ForeachNode(
                $env,
                new DoNode(
                    $env->withLocals([Symbol::create('value'), Symbol::create('key')]),
                    [],
                    new LocalVarNode($env->withLocals([Symbol::create('value'), Symbol::create('key')]), Symbol::create('key'))
                ),
                new TableNode($env->withContext(NodeEnvironment::CONTEXT_EXPRESSION), []),
                Symbol::create('value'),
                Symbol::create('key')
            ),
            $this->analyze($list)
        );
    }

    public function testDeconstrutionWithThreeArgs(): void
    {
        // (foreach [[key] [value] []])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                TypeFactory::getInstance()->persistentVectorFromArray([Symbol::create('key')]),
                TypeFactory::getInstance()->persistentVectorFromArray([Symbol::create('value')]),
                TypeFactory::getInstance()->persistentVectorFromArray([]),
            ]),
            Symbol::create('key'),
        ]);

        $node = $this->analyze($list);
        self::assertInstanceOf(LetNode::class, $node->getBodyExpr());
    }

    public function testArgForVectorCanNotBe4(): void
    {
        $this->expectException(AbstractLocatedException::class);
        $this->expectExceptionMessage("Vector of 'foreach must have exactly two or three elements.");

        // (foreach [x y z @{}])
        $list = TypeFactory::getInstance()->persistentListFromArray([
            Symbol::create(Symbol::NAME_FOREACH),
            TypeFactory::getInstance()->persistentVectorFromArray([
                Symbol::create('x'),
                Symbol::create('y'),
                Symbol::create('z'),
                Table::empty(),
            ]),
        ]);

        $this->analyze($list);
    }

    private function analyze(PersistentListInterface $list): ForeachNode
    {
        $env = new GlobalEnvironment();
        $env->addDefinition('phel\\core', Symbol::create('first'), TypeFactory::getInstance()->emptyPersistentMap());
        $env->addDefinition('phel\\core', Symbol::create('next'), TypeFactory::getInstance()->emptyPersistentMap());
        $analyzer = new Analyzer($env);

        return (new ForeachSymbol($analyzer))->analyze($list, NodeEnvironment::empty());
    }
}
