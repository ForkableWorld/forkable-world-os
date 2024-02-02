<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators;
use App\Exceptions\QMFileNotFoundException;
use App\Files\PHP\PhpClassFile;
use App\Logging\QMLog;
use App\Types\QMArr;
use Closure;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
class ClassTypePrinter extends Printer
{

    protected $indentation = "\t";
    protected $linesBetweenProperties = 0;
    protected $linesBetweenMethods = 1; // Has to be greater than 0 so we'll just remove empty lines later
    protected $returnTypeColon = ': ';
	/**
	 * @var \App\Files\PHP\PhpClassFile
	 */
	private PhpClassFile $existingFile;
	public function __construct(PhpClassFile $file){
		$this->existingFile = $file;
        parent::__construct();
	}
	public function printMethod(Method $method, PhpNamespace $namespace = null): string{
		$f = $this->existingFile;
        try {
            if ($f->methodInheritedFromTrait($method->getName())) {
                return "";
            }
        } catch (QMFileNotFoundException $e) {
            QMLog::error("Could not find trait: " . $e->getMessage());
        }
        $method->validate();
		$line = ($method->isAbstract() ? 'abstract ' : '')
		        . ($method->isFinal() ? 'final ' : '')
		        . ($method->getVisibility() ? $method->getVisibility() . ' ' : '')
		        . ($method->isStatic() ? 'static ' : '')
		        . 'function '
		        . ($method->getReturnReference() ? '&' : '')
		        . $method->getName();
		$returnType = $this->printReturnType($method, $namespace);

		$str = Helpers::formatDocComment($method->getComment() . "\n")
		       . self::printAttributes($method->getAttributes(), $namespace)
		       . $line
		       . ($params = $this->printParameters($method, strlen($line) + strlen($returnType) + strlen($this->indentation) + 2)) // 2 = parentheses
		       . $returnType
		       . ($method->isAbstract() || $method->getBody() === null
				? ";\n"
				: ''
				  . "{\n"
				  . $this->indent(ltrim(rtrim($method->getBody()) . "\n"))
				  . "}\n");
		return $str;
    }

	/**
	 * @param Closure|GlobalFunction|Method  $function
	 */
	private function printReturnType($function, ?PhpNamespace $namespace): string
	{
		return ($tmp = $this->printType($function->getReturnType(), $function->isReturnNullable(), $namespace))
			? $this->returnTypeColon . $tmp
			: '';
	}


	private function printAttributes(array $attrs, ?PhpNamespace $namespace, bool $inline = false): string
	{
		if (!$attrs) {
			return '';
		}
		$items = [];
		foreach ($attrs as $attr) {
			$args = (new Dumper)->format('...?:', $attr->getArguments());
			$items[] = $this->printType($attr->getName(), false, $namespace) . ($args ? "($args)" : '');
		}
		return $inline
			? '#[' . implode(', ', $items) . '] '
			: '#[' . implode("]\n#[", $items) . "]\n";
	}


	private function joinProperties(array $props)
	{
		return $this->linesBetweenProperties
			? implode(str_repeat("\n", $this->linesBetweenProperties), $props)
			: preg_replace('#^(\w.*\n)\n(?=\w.*;)#m', '$1', implode("\n", $props));
	}

    protected function printUses(PhpNamespace $namespace, string $of = PhpNamespace::NameNormal): string
    {
        $nsName = $namespace->getName();
        $uses = [];
        $force = false;
        foreach ($namespace->getUses() as $alias => $original) {
            $nsPlusAlias = $nsName ? $nsName . '\\' . $alias : $alias;
            /** @var ClassType $class */
            $class = QMArr::first($namespace->getClasses());
            if ($nsName.'\\'.$class->getName() !== $original) {
                $uses[] = $alias === $original || substr($original, -(strlen($alias) + 1)) === '\\' . $alias
                    ? "use $original;"
                    : "use $original as $alias;";
            }
        }
        return implode("\n", $uses);
    }

}
