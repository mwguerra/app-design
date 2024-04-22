<?php

namespace MWGuerra\AppDesign\Utilities;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class PhpClassEditor extends FileHandler
{
    private Parser $parser;
    private PrettyPrinter\Standard $prettyPrinter;

    public function __construct($filePath, $dryRun = false) {
        parent::__construct($filePath, $dryRun);
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->prettyPrinter = new PrettyPrinter\Standard;
    }

    public function deleteMethod($methodName): void
    {
        if ($this->dryRun) {
            echo "Dry run: Would delete method {$methodName}.\n";
        } else {
            try {
                $stmts = $this->parser->parse($this->readFile());
                $traverser = new NodeTraverser();
                $traverser->addVisitor(new class($methodName) extends NodeVisitorAbstract {
                    private $methodName;

                    public function __construct($methodName)
                    {
                        $this->methodName = $methodName;
                    }

                    public function leaveNode(Node $node)
                    {
                        if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === $this->methodName) {
                            return NodeTraverser::REMOVE_NODE;
                        }
                    }
                });

                $modifiedStmts = $traverser->traverse($stmts);
                $this->writeFile($this->prettyPrinter->prettyPrintFile($modifiedStmts));
            } catch (Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }
        }
    }

    public function addMethod($methodString): void
    {
        if ($this->dryRun) {
            echo "Dry run: Would add a new method.\n";
        } else {
            try {
                $stmts = $this->parser->parse($this->readFile());
                $additionalStmts = $this->parser->parse('<?php ' . $methodString);
                $stmts[count($stmts) - 1]->stmts = array_merge($stmts[count($stmts) - 1]->stmts, $additionalStmts);
                $this->writeFile($this->prettyPrinter->prettyPrintFile($stmts));
            } catch (Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }
        }
    }

    public function replaceMethod($methodName, $newMethodString): void
    {
        if ($this->dryRun) {
            echo "Dry run: Would replace method {$methodName} with new content.\n";
        } else {
            $this->deleteMethod($methodName);
            $this->addMethod($newMethodString);
        }
    }
}

// Example Usage:
// $filePath = 'path/to/YourClass.php';
// $editor = new PhpClassEditor($filePath);
// $newMethod = "public function newMethod() { /* method body */ }";
// $editor->addMethod($newMethod);
// $editor->deleteMethod('oldMethod');
// $editor->replaceMethod('anotherMethod', $newMethod);
